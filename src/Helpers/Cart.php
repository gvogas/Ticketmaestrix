<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\EventModel;
use App\Models\TicketModel;
use App\Models\VenueModel;

class Cart
{
    public const SERVICE_FEE_RATE = 0.15;

    public static function add(int $ticketId, int $qty = 1): void
    {
        if ($ticketId <= 0 || $qty <= 0) {
            return;
        }

        self::checkExpiry();

        $wasEmpty = empty($_SESSION['cart'] ?? []);
        $cart = $_SESSION['cart'] ?? [];
        $cart[$ticketId] = ($cart[$ticketId] ?? 0) + $qty;
        $_SESSION['cart'] = $cart;

        if ($wasEmpty) {
            $_SESSION['cart_expires_at'] = time() + 300;
        }
    }

    public static function remove(int $ticketId): void
    {
        self::checkExpiry();

        if (!isset($_SESSION['cart'])) {
            return;
        }

        unset($_SESSION['cart'][$ticketId]);

        if (empty($_SESSION['cart'])) {
            self::clear();
        }
    }

    public static function clear(): void
    {
        unset($_SESSION['cart']);
        unset($_SESSION['cart_expires_at']);
    }

    // extend before Stripe redirect - 5 min would expire mid-payment
    public static function extendExpiry(int $seconds): void
    {
        $_SESSION['cart_expires_at'] = time() + $seconds;
    }

    public static function items(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    public static function isExpired(): bool
    {
        return isset($_SESSION['cart_expires_at']) && time() > $_SESSION['cart_expires_at'];
    }

    public static function checkExpiry(): void
    {
        if (self::isExpired()) {
            self::clear();
        }
    }

    public static function secondsRemaining(): int
    {
        return max(0, (int) (($_SESSION['cart_expires_at'] ?? 0) - time()));
    }

    public static function count(): int
    {
        self::checkExpiry();
        return array_sum(self::items());
    }

    public static function hydrate(
        TicketModel $tickets,
        EventModel  $events,
        VenueModel  $venues
    ): array {
        self::checkExpiry();
        $rows = [];
        foreach (self::items() as $ticketId => $qty) {
            $ticket = $tickets->getById((int) $ticketId);
            if ($ticket === null || !empty($ticket->sold)) {
                continue;
            }
            $event = $events->getById((int) $ticket->event_id);
            if ($event === null) {
                continue;
            }
            $onSale        = $tickets->isOnSale($ticket);
            $effectivePrice = $tickets->effectivePrice($ticket);
            $rows[] = [
                'ticket_id'      => (int) $ticket->id,
                'name'           => (string) $event->title,
                'image'          => (string) $event->event_image,
                'date'           => (string) $event->date,
                'price'          => $effectivePrice,
                'original_price' => $onSale ? (float) $ticket->price : null,
                'on_sale'        => $onSale,
                'quantity'       => (int) $qty,
                'total'          => $effectivePrice * (int) $qty,
            ];
        }
        return $rows;
    }

    public static function subtotal(array $hydrated): float
    {
        $sum = 0.0;
        foreach ($hydrated as $row) {
            $sum += (float) $row['total'];
        }
        return $sum;
    }
}
