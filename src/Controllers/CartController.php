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

class CartController
{
    private const ORDER_STATUS_PAID = 1;
    private const SERVICE_FEE_RATE = 0.15;

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
        if (Auth::isAdmin()) {
            return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
        }

        $queryParams = $request->getQueryParams();
        $pointsToUse = (int) ($queryParams['points_to_use'] ?? 0);

        $rows = Cart::hydrate($this->ticketModel, $this->eventModel, $this->venueModel);

        if (count($rows) === 0) {
            return $response->withHeader('Location', $this->basePath . '/cart')->withStatus(302);
        }

        $subtotal        = Cart::subtotal($rows);
        $user            = Auth::user();
        $availablePoints = (int) ($user->points ?? 0);
        $pointsToUse     = $this->clampPoints($pointsToUse, $availablePoints, (int) floor($subtotal * 100));
        $discount        = $pointsToUse * 0.01;
        $taxable         = max(0, $subtotal - $discount);
        $serviceFee      = round($taxable * self::SERVICE_FEE_RATE, 2);
        $total           = round($taxable + $serviceFee, 2);

        $html = $this->twig->render('home/checkout.html.twig', [
            'base_path'     => $this->basePath,
            'points_to_use' => $pointsToUse,
            'subtotal'      => $subtotal,
            'discount'      => $discount,
            'service_fee'   => $serviceFee,
            'total'         => $total,
            'error'         => null,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    /** POST /cart/add — add a ticket id to the session cart. */
    public function add(Request $request, Response $response): Response
    {
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

    /** POST /cart/expire — client countdown fires this when timer hits zero. */
    public function expire(Request $request, Response $response): Response
    {
        $token = $request->getHeaderLine('X-CSRF-Token');
        if ($token === '' || $token !== ($_SESSION['csrf_token'] ?? '')) {
            return $response->withStatus(403);
        }

        Cart::clear();

        return $response
            ->withHeader('Location', $this->basePath . '/events')
            ->withStatus(302);
    }

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
        $body            = $request->getParsedBody() ?? [];
        $pointsToUse     = (int) ($body['points_to_use'] ?? 0);
        $user            = Auth::user();
        $availablePoints = (int) ($user->points ?? 0);
        $pointsToUse     = $this->clampPoints($pointsToUse, $availablePoints, (int) floor($subtotal * 100));
        $discount        = $pointsToUse * 0.01;
        $taxable         = max(0, $subtotal - $discount);
        $serviceFee      = round($taxable * self::SERVICE_FEE_RATE, 2);
        $total           = round($taxable + $serviceFee, 2);

        // stripe rejects anything under $0.50 - go direct for free/near-free orders
        if ($total < 0.50) {
            try {
                return $this->createOrderDirectly(
                    $rows, $userId, $total, $subtotal, $pointsToUse, $response
                );
            } catch (\Throwable $e) {
                error_log('Direct order creation failed: ' . $e->getMessage());
                $html = $this->twig->render('home/checkout.html.twig', [
                    'base_path'     => $this->basePath,
                    'points_to_use' => $pointsToUse,
                    'subtotal'      => $subtotal,
                    'discount'      => $discount,
                    'service_fee'   => $serviceFee,
                    'total'         => $total,
                    'error'         => 'Something went wrong placing your order. Please try again.',
                ]);
                $response->getBody()->write($html);
                return $response->withStatus(200);
            }
        }

        // save expiry before we extend it - restored on cancel so the timer doesnt jump
        $_SESSION['cart_expires_at_pre_stripe'] = $_SESSION['cart_expires_at'] ?? null;
        Cart::extendExpiry(1800);

        $pending                    = R::dispense('stripepending');
        $pending->user_id           = $userId;
        $pending->cart_json         = json_encode($rows);
        $pending->points_to_use     = $pointsToUse;
        $pending->total             = $total;
        $pending->created_at        = date('Y-m-d H:i:s');
        $pending->stripe_session_id = '';
        $pendingId = (int) R::store($pending);

        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

        try {
            $session = $this->stripeService->createCheckoutSession(
                $rows,
                $pointsToUse,
                (int) round($serviceFee * 100),
                $pendingId,
                $appUrl . $this->basePath . '/checkout/success',
                $appUrl . $this->basePath . '/checkout/cancel',
            );
        } catch (\Throwable $e) {
            R::trash($pending);
            error_log('Stripe session creation failed: ' . $e->getMessage());

            $html = $this->twig->render('home/checkout.html.twig', [
                'base_path'     => $this->basePath,
                'points_to_use' => $pointsToUse,
                'subtotal'      => $subtotal,
                'discount'      => $discount,
                'service_fee'   => $serviceFee,
                'total'         => $total,
                'error'         => 'Payment service temporarily unavailable. Please try again.',
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(200);
        }

        $pending->stripe_session_id = $session['id'];
        R::store($pending);

        return $response->withHeader('Location', $session['url'])->withStatus(302);
    }

    public function checkoutSuccess(Request $request, Response $response): Response
    {
        Cart::clear();
        unset($_SESSION['cart_expires_at_pre_stripe']);

        $html = $this->twig->render('home/checkout_success.html.twig', [
            'base_path' => $this->basePath,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function checkoutCancel(Request $request, Response $response): Response
    {
        return $response
            ->withHeader('Location', $this->basePath . '/checkout')
            ->withStatus(302);
    }

    private function createOrderDirectly(
        array    $rows,
        int      $userId,
        float    $total,
        float    $subtotal,
        int      $pointsToUse,
        Response $response,
    ): Response {
        $pointsEarned = (int) floor($subtotal * 0.20);

        R::begin();
        try {
            $order                    = R::dispense('orders');
            $order->total_price       = $total;
            $order->status            = self::ORDER_STATUS_PAID;
            $order->order_time        = date('Y-m-d H:i:s');
            $order->user_id           = $userId;
            $order->points_earned     = $pointsEarned;
            $order->points_spent      = $pointsToUse;
            $order->stripe_session_id = '';
            $orderId = (int) R::store($order);

            foreach ($rows as $row) {
              
                $this->orderItemModel->create(
                    (int) $row['quantity'],
                    $orderId,
                    (int) $row['ticket_id']
                );
                $this->ticketModel->markSold((int) $row['ticket_id']);
            }

            $user = R::load('users', $userId);
            if ($pointsToUse > 0) {
                // load fresh from DB - session-cached value could be stale if points changed mid-request
                $user->points = max(0, (int) ($user->points ?? 0) - $pointsToUse);
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

    private function clampPoints(int $points, int $available, int $maxDiscount): int
    {
        return min(max(0, $points), $available, $maxDiscount);
    }
}
