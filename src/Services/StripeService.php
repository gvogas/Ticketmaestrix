<?php

declare(strict_types=1);

namespace App\Services;

use Stripe\Checkout\Session;
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
     * @param array  $rows        Cart::hydrate() output — each row has: ticket_id, name, price, quantity, total
     * @param int    $pointsToUse Validated points to spend; 1 point = 1 cent ($0.01)
     * @param int    $pendingId   stripepending.id passed back via Stripe metadata so the webhook can find the row
     * @param string $successUrl  Full public URL Stripe redirects to after payment
     * @param string $cancelUrl   Full public URL Stripe redirects to when user clicks Back
     * @return array{url: string, id: string}
     */
    public function createCheckoutSession(
        array  $rows,
        int    $pointsToUse,
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

        // Negative line item for points discount — Stripe supports negative unit_amount in price_data
        if ($pointsToUse > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount'  => -$pointsToUse, // points are already in cents (100 points = -$1.00)
                    'product_data' => ['name' => 'Points discount'],
                ],
                'quantity' => 1,
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $successUrl,
            'cancel_url'           => $cancelUrl,
            // pending_id lets the webhook load the right stripepending row
            'metadata'             => ['pending_id' => $pendingId],
        ]);

        // Return only what the controller needs — keeps Stripe SDK types out of caller code
        return ['url' => $session->url, 'id' => $session->id];
    }
}
