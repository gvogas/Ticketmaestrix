<?php

declare(strict_types=1);

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\Coupon;
use Stripe\Stripe;

/**
 * Thin wrapper around the Stripe SDK.
 * Keeps all Stripe API calls out of the controller so the controller
 * only handles HTTP concerns.
 */
class StripeService
{
    public function __construct(private string $secretKey)
    {
        Stripe::setApiKey($secretKey);
    }

    /**
     * Build a Stripe Checkout Session from a hydrated cart.
     *
     * @param array  $rows           Cart::hydrate() output — each row has: ticket_id, name, price, quantity, total
     * @param int    $pointsToUse    Validated points to spend; 1 point = 1 cent ($0.01)
     * @param int    $serviceFeeCents 15% service fee (already computed against the post-discount subtotal), in cents
     * @param int    $pendingId      stripepending.id passed back via Stripe metadata so the webhook can find the row
     * @param string $successUrl     Full public URL Stripe redirects to after payment
     * @param string $cancelUrl      Full public URL Stripe redirects to when user clicks Back
     * @return array{url: string, id: string}
     */
    public function createCheckoutSession(
        array  $rows,
        int    $pointsToUse,
        int    $serviceFeeCents,
        int    $pendingId,
        string $successUrl,
        string $cancelUrl,
    ): array {
        $lineItems = [];

        foreach ($rows as $row) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount'  => (int) round($row['price'] * 100), // dollars → cents
                    'product_data' => ['name' => $row['name']],
                ],
                'quantity' => (int) $row['quantity'],
            ];
        }

        // Service fee shown as its own positive line item so the user sees the
        // tax broken out on the Stripe Checkout page. The caller computed this
        // against the *post-discount* subtotal already — so a points discount
        // also reduces the tax. The coupon below then removes the discount
        // from the line-item total, and the math reconciles to the controller's
        // computed `total` (subtotal − discount) × 1.15.
        if ($serviceFeeCents > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount'  => $serviceFeeCents,
                    'product_data' => ['name' => 'Service fee (15%)'],
                ],
                'quantity' => 1,
            ];
        }

        $params = [
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $successUrl,
            'cancel_url'           => $cancelUrl,
            // pending_id lets the webhook load the right stripepending row
            'metadata'             => ['pending_id' => $pendingId],
        ];

        // Apply the points discount via a one-shot Stripe Coupon. Negative
        // unit_amount in price_data is rejected by the Stripe API (must be a
        // non-negative integer), which is why prior "negative line item"
        // attempts failed the whole Session::create call. The amount-off
        // coupon is the supported Checkout pattern for a flat discount.
        // Sub-$0.50 totals never reach here — CartController routes those to
        // createOrderDirectly() because Stripe rejects sub-$0.50 charges.
        if ($pointsToUse > 0) {
            $coupon = Coupon::create([
                'amount_off' => $pointsToUse, // already in cents (1 point = 1 cent)
                'currency'   => 'usd',
                'duration'   => 'once',
                'name'       => "Points discount ({$pointsToUse} pts)",
                'metadata'   => ['pending_id' => $pendingId],
            ]);
            $params['discounts'] = [['coupon' => $coupon->id]];
        }

        $session = Session::create($params);

        // Return only what the controller needs — keeps Stripe SDK types out of caller code
        return ['url' => $session->url, 'id' => $session->id];
    }
}
