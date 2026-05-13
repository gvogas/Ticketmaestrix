<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Cart;
use App\Models\EventModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\PointsHistoryModel;
use App\Models\TicketModel;
use App\Models\VenueModel;
use App\Services\StripeService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RedBeanPHP\R;
use Twig\Environment;

/**
 * Mutates the session-backed cart and initiates Stripe Checkout on checkout.
 * Order creation happens in StripeWebhookController after payment succeeds.
 * Read-only cart rendering lives in HomeController::showCart.
 */
class CartController
{
    private const ORDER_STATUS_PAID = 1;

    public function __construct(
        private Environment        $twig,
        private TicketModel        $ticketModel,
        private EventModel         $eventModel,
        private VenueModel         $venueModel,
        private OrderModel         $orderModel,
        private OrderItemModel     $orderItemModel,
        private PointsHistoryModel $pointsHistoryModel,
        private StripeService      $stripeService,
        private string             $basePath,
    ) {}

    /** GET /checkout — display the order summary and "Pay with Stripe" button. */
    public function showCheckout(Request $request, Response $response): Response
    {
        // Admins should manage the system, not use the checkout flow
        if (Auth::isAdmin()) {
            return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
        }

        $queryParams = $request->getQueryParams();
        $pointsToUse = (int) ($queryParams['points_to_use'] ?? 0);

        $rows = Cart::hydrate($this->ticketModel, $this->eventModel, $this->venueModel);

        // Navigating to /checkout with an empty cart would show a $0.00 form
        if (count($rows) === 0) {
            return $response->withHeader('Location', $this->basePath . '/cart')->withStatus(302);
        }

        $subtotal        = Cart::subtotal($rows);
        $user            = Auth::user();
        $availablePoints = (int) ($user->points ?? 0);
        $pointsToUse     = $this->clampPoints($pointsToUse, $availablePoints, (int) floor($subtotal * 100));
        $discount        = $pointsToUse * 0.01;
        $total           = max(0, $subtotal - $discount);

        $html = $this->twig->render('home/checkout.html.twig', [
            'base_path'     => $this->basePath,
            'points_to_use' => $pointsToUse,
            'subtotal'      => $subtotal,
            'discount'      => $discount,
            'total'         => $total,
            'error'         => null,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    /** POST /cart/add — add a ticket id to the session cart. */
    public function add(Request $request, Response $response): Response
    {
        // If an admin clicks 'Buy', redirect them to the management inventory instead
        if (Auth::isAdmin()) {
            return $response->withHeader('Location', $this->basePath . '/tickets')->withStatus(302);
        }

        $data     = $request->getParsedBody();
        $ticketId = (int) ($data['ticket_id'] ?? 0);
        $qty      = (int) ($data['quantity']  ?? 1);

        $ticket = $this->ticketModel->getById($ticketId);
        if ($ticket === null || !empty($ticket->sold)) {
            return $response->withHeader('Location', $this->basePath . '/cart')->withStatus(302);
        }

        Cart::add($ticketId, max(1, $qty));

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.cart_added'];
        return $response->withHeader('Location', $this->basePath . '/cart')->withStatus(302);
    }

    /** POST /cart/remove/{ticket_id} — remove a single line. */
    public function remove(Request $request, Response $response, array $args): Response
    {
        Cart::remove((int) ($args['ticket_id'] ?? 0));

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.cart_removed'];
        return $response
            ->withHeader('Location', $this->basePath . '/cart')
            ->withStatus(302);
    }

    /** POST /cart/clear — empty the cart entirely. */
    public function clear(Request $request, Response $response): Response
    {
        Cart::clear();

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.cart_cleared'];
        return $response
            ->withHeader('Location', $this->basePath . '/cart')
            ->withStatus(302);
    }

    /**
     * POST /cart/expire — called by the client-side countdown when the timer
     * hits zero. Clears the cart and sends the user back to browse events.
     */
    public function expire(Request $request, Response $response): Response
    {
        Cart::clear();

        return $response
            ->withHeader('Location', $this->basePath . '/events')
            ->withStatus(302);
    }

    /**
     * POST /checkout — validate the cart, save a snapshot in stripepending,
     * create a Stripe Checkout Session, and redirect the user to Stripe.
     *
     * Order creation happens in StripeWebhookController after Stripe confirms
     * payment via the checkout.session.completed webhook event.
     *
     * Exception: if total < $0.50 (Stripe minimum), the order is created
     * directly here without going through Stripe.
     */
    public function checkout(Request $request, Response $response): Response
    {
        if (Auth::isAdmin()) {
            return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
        }

        Cart::checkExpiry();

        $rows = Cart::hydrate($this->ticketModel, $this->eventModel, $this->venueModel);
        if (count($rows) === 0) {
            return $response
                ->withHeader('Location', $this->basePath . '/cart')
                ->withStatus(302);
        }

        $userId          = (int) Auth::userId();
        $subtotal        = Cart::subtotal($rows);
        $pointsToUse     = (int) ($request->getParsedBody()['points_to_use'] ?? 0);
        $user            = Auth::user(); // request-cached; avoids a redundant R::load
        $availablePoints = (int) ($user->points ?? 0);
        $pointsToUse     = $this->clampPoints($pointsToUse, $availablePoints, (int) floor($subtotal * 100));
        $discount        = $pointsToUse * 0.01;
        $total           = round($subtotal - $discount, 2);

        // Stripe requires a minimum charge of $0.50 — bypass for fully-discounted orders
        if ($total < 0.50) {
            try {
                return $this->createOrderDirectly(
                    $rows, $userId, $total, $subtotal, $pointsToUse, $availablePoints, $response
                );
            } catch (\Throwable $e) {
                error_log('Direct order creation failed: ' . $e->getMessage());
                $html = $this->twig->render('home/checkout.html.twig', [
                    'base_path'     => $this->basePath,
                    'points_to_use' => $pointsToUse,
                    'subtotal'      => $subtotal,
                    'discount'      => $discount,
                    'total'         => $total,
                    'error'         => 'Something went wrong placing your order. Please try again.',
                ]);
                $response->getBody()->write($html);
                return $response->withStatus(200);
            }
        }

        // Extend cart expiry so it survives the Stripe redirect
        // (the normal 5-minute window is too short for an offsite payment flow)
        Cart::extendExpiry(1800);

        // Save a snapshot of everything the webhook will need to create the order.
        // The webhook runs server-side with no PHP session, so it cannot read the cart.
        $pending                    = R::dispense('stripepending');
        $pending->user_id           = $userId;
        $pending->cart_json         = json_encode($rows);
        $pending->points_to_use     = $pointsToUse;
        $pending->total             = $total;
        $pending->created_at        = date('Y-m-d H:i:s');
        $pending->stripe_session_id = ''; // filled in after the Stripe session is created below
        $pendingId = (int) R::store($pending);

        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

        try {
            $session = $this->stripeService->createCheckoutSession(
                $rows,
                $pointsToUse,
                $pendingId,
                $appUrl . $this->basePath . '/checkout/success',
                $appUrl . $this->basePath . '/checkout/cancel',
            );
        } catch (\Throwable $e) {
            // Clean up the orphaned pending row and show the checkout page with an error
            R::trash($pending);
            error_log('Stripe session creation failed: ' . $e->getMessage());

            $html = $this->twig->render('home/checkout.html.twig', [
                'base_path'     => $this->basePath,
                'points_to_use' => $pointsToUse,
                'subtotal'      => $subtotal,
                'discount'      => $discount,
                'total'         => $total,
                'error'         => 'Payment service temporarily unavailable. Please try again.',
            ]);
            $response->getBody()->write($html);
            // 200 because the checkout page itself rendered correctly — the payment error is handled
            return $response->withStatus(200);
        }

        // Save the Stripe session ID so the webhook can find this pending row by it
        $pending->stripe_session_id = $session['id'];
        R::store($pending);

        return $response->withHeader('Location', $session['url'])->withStatus(302);
    }

    /**
     * GET /checkout/success — Stripe redirects here after successful payment.
     *
     * The order itself is created by StripeWebhookController — this page only
     * clears the session cart and confirms to the user that payment was received.
     */
    public function checkoutSuccess(Request $request, Response $response): Response
    {
        // Clear the session cart — the webhook already (or soon will) create the order
        Cart::clear();

        $html = $this->twig->render('home/checkout_success.html.twig', [
            'base_path' => $this->basePath,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * GET /checkout/cancel — Stripe redirects here when the user clicks Back.
     * Cart is left intact so the user can try again.
     */
    public function checkoutCancel(Request $request, Response $response): Response
    {
        return $response
            ->withHeader('Location', $this->basePath . '/checkout')
            ->withStatus(302);
    }

    /**
     * Creates an order directly (without Stripe) when the total is below $0.50.
     * This handles the edge case where points cover the full order amount.
     */
    private function createOrderDirectly(
        array    $rows,
        int      $userId,
        float    $total,
        float    $subtotal,
        int      $pointsToUse,
        int      $availablePoints,
        Response $response,
    ): Response {
        $pointsEarned = (int) floor($subtotal * 0.10);

        R::begin();
        try {
            $order                    = R::dispense('orders');
            $order->total_price       = $total;
            $order->status            = self::ORDER_STATUS_PAID;
            $order->order_time        = date('Y-m-d H:i:s');
            $order->user_id           = $userId;
            $order->points_earned     = $pointsEarned;
            $order->points_spent      = $pointsToUse;
            $order->stripe_session_id = ''; // no Stripe session for free orders
            $orderId = (int) R::store($order);

            foreach ($rows as $row) {
                $item            = R::dispense('orderitem');
                $item->quantity  = (int) $row['quantity'];
                $item->order_id  = $orderId;
                $item->ticket_id = (int) $row['ticket_id'];
                R::store($item);
                $this->ticketModel->markSold((int) $row['ticket_id']);
            }

            $user = R::load('users', $userId);
            if ($pointsToUse > 0) {
                $user->points = max(0, $availablePoints - $pointsToUse);
                $this->pointsHistoryModel->addPoints(
                    $userId,
                    -$pointsToUse,
                    "Spent {$pointsToUse} points on order #{$orderId}",
                    $orderId
                );
            }

            $user->points = (int) ($user->points ?? 0) + $pointsEarned;
            R::store($user);

            $this->pointsHistoryModel->addPoints(
                $userId,
                $pointsEarned,
                "Earned {$pointsEarned} points (10%) from order #{$orderId}",
                $orderId
            );

            R::commit();
        } catch (\Throwable $e) {
            R::rollback();
            throw $e;
        }

        Cart::clear();
        return $response
            ->withHeader('Location', $this->basePath . '/profile')
            ->withStatus(302);
    }

    /**
     * Clamps a points value so it cannot exceed the user's balance or the
     * maximum discount allowed (subtotal in cents). Ensures non-negative.
     */
    private function clampPoints(int $points, int $available, int $maxDiscount): int
    {
        return min(max(0, $points), $available, $maxDiscount);
    }
}
