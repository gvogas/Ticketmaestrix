<?php

declare(strict_types=1);

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\Coupon;
use Stripe\Stripe;

class StripeService
{
    public function __construct(private string $secretKey)
    {
        Stripe::setApiKey($secretKey);
    }

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
                    'currency'     => 'cad',
                    'unit_amount'  => (int) round($row['price'] * 100),
                    'product_data' => ['name' => $row['name']],
                ],
                'quantity' => (int) $row['quantity'],
            ];
        }

        if ($serviceFeeCents > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'cad',
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
            'metadata'             => ['pending_id' => $pendingId],
        ];

        // Stripe will not accept a negative price, so the points discount is sent as a coupon.
        $couponId = null;
        if ($pointsToUse > 0) {
            $coupon = Coupon::create([
                // 1 point = 1 cent off.
                'amount_off' => $pointsToUse,
                'currency'   => 'cad',
                'duration'   => 'once',
                'name'       => "Points discount ({$pointsToUse} pts)",
                'metadata'   => ['pending_id' => $pendingId],
            ]);
            $couponId = $coupon->id;
            $params['discounts'] = [['coupon' => $couponId]];
        }

        try {
            $session = Session::create($params);
        } catch (\Throwable $e) {
            if ($couponId !== null) {
                try { Coupon::retrieve($couponId)->delete(); } catch (\Throwable) {}
            }
            throw $e;
        }

        return ['url' => $session->url, 'id' => $session->id];
    }
}
