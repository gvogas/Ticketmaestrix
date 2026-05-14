<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\BeanHelper;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\PointsHistoryModel;
use App\Models\TicketModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RedBeanPHP\R;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

/**
 * Receives and processes Stripe webhook events.
 *
 * Called by Stripe's servers — no PHP session is available here.
 * All order data comes from the stripepending table, which was populated
 * by CartController::checkout() before the Stripe redirect.
 */
class StripeWebhookController
{
    private const ORDER_STATUS_PAID = 1;

    public function __construct(
        private OrderModel         $orderModel,
        private OrderItemModel     $orderItemModel,
        private PointsHistoryModel $pointsHistoryModel,
        private TicketModel        $ticketModel,
        private string             $webhookSecret,
    ) {}

    /**
     * POST /stripe/webhook
     *
     * Verifies the Stripe-Signature header, then on checkout.session.completed:
     * loads stripepending, creates the order + order items, handles points,
     * deletes the pending row. Returns 200 for all handled events so Stripe
     * stops retrying. Returns 500 on transaction failure so Stripe retries.
     */
    public function handle(Request $request, Response $response): Response
    {
        // Read the raw body before any framework parsing — Stripe's signature
        // is computed against the exact raw bytes Stripe sent
        $payload   = (string) $request->getBody();
        $sigHeader = $request->getHeaderLine('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
        } catch (SignatureVerificationException $e) {
            // Invalid or missing signature — could be a spoofed request
            error_log('Stripe webhook: invalid signature — ' . $e->getMessage());
            $response->getBody()->write('Invalid signature');
            return $response->withStatus(400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session   = $event->data->object;
            $sessionId = $session->id;
            $pendingId = (int) ($session->metadata->pending_id ?? 0);

            $pending = R::load('stripepending', $pendingId);
            if (!BeanHelper::isValidBean($pending)) {
                // Row gone — already processed or manually deleted; acknowledge so Stripe stops retrying
                error_log("Stripe webhook: stripepending #{$pendingId} not found for session {$sessionId}");
                $response->getBody()->write('ok');
                return $response->withStatus(200);
            }

            // Idempotency guard — Stripe may deliver the same event more than once
            if (R::findOne('orders', 'stripe_session_id = ?', [$sessionId])) {
                $response->getBody()->write('ok');
                return $response->withStatus(200);
            }

            $userId       = (int) $pending->user_id;
            $pointsToUse  = (int) $pending->points_to_use;
            $total        = (float) $pending->total;
            $rows         = json_decode($pending->cart_json, true);
            $subtotal     = (float) array_sum(array_column($rows, 'total'));
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
                $order->stripe_session_id = $sessionId; // stored for idempotency on duplicate delivery
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

                // Pending row is no longer needed — the order is real now
                R::trash($pending);

                R::commit();
            } catch (\Throwable $e) {
                R::rollback();
                // Return 500 so Stripe retries the webhook delivery
                error_log('Stripe webhook: order creation failed — ' . $e->getMessage());
                return $response->withStatus(500);
            }
        }

        $response->getBody()->write('ok');
        return $response->withStatus(200);
    }
}
