<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\BeanHelper;
use RedBeanPHP\R;

class TicketModel
{
    public function getAll(): array
    {
        return BeanHelper::castBeanArray(R::findAll('ticket', 'ORDER BY event_id, `row`, seat'));
    }

    public function getById(int $id): mixed
    {
        $bean = R::load('ticket', $id);
        return BeanHelper::isValidBean($bean) ? BeanHelper::castBeanProperties($bean) : null;
    }

    public function findByEvent(int $eventId): array
    {
        return BeanHelper::castBeanArray(
            R::find('ticket', 'event_id = ? AND (sold IS NULL OR sold = 0) ORDER BY `row`, seat', [$eventId])
        );
    }

    public function markSold(int $ticketId): void
    {
        R::exec('UPDATE ticket SET sold = 1 WHERE id = ?', [$ticketId]);
    }

    public function load(int $id): mixed
    {
        return BeanHelper::castBeanProperties(R::load('ticket', $id));
    }

    public function create(
        float   $price,
        string  $seat,
        string  $row,
        int     $eventId,
        ?string $saleType   = null,
        ?float  $saleAmount = null,
        ?string $saleStart  = null,
        ?string $saleEnd    = null,
    ): void {
        $bean              = R::dispense('ticket');
        $bean->price       = $price;
        $bean->seat        = $seat;
        $bean->row         = $row;
        $bean->event_id    = $eventId;
        $bean->sale_type   = $saleType;
        $bean->sale_amount = $saleAmount;
        $bean->sale_start  = $saleStart;
        $bean->sale_end    = $saleEnd;
        R::store($bean);
    }

    public function isOnSale(object $ticket): bool
    {
        if (empty($ticket->sale_type) || empty($ticket->sale_start) || empty($ticket->sale_end)) {
            return false;
        }
        $now = time();
        return strtotime((string) $ticket->sale_start) <= $now
            && strtotime((string) $ticket->sale_end)   >= $now;
    }

    public function effectivePrice(object $ticket): float
    {
        if (!$this->isOnSale($ticket)) {
            return (float) $ticket->price;
        }
        $base   = (float) $ticket->price;
        $amount = (float) $ticket->sale_amount;
        if ($ticket->sale_type === 'percent') {
            return round(max(0.0, $base * (1 - $amount / 100)), 2);
        }
        return round(max(0.0, $base - $amount), 2);
    }

    public function save(mixed $bean): void
    {
        R::store($bean);
    }

    public function delete(mixed $bean): void
    {
        R::trash($bean);
    }

    public function minPriceForEvent(int $eventId): ?float
    {
        $value = R::getCell(
            "SELECT MIN(
                CASE
                    WHEN sale_type IS NOT NULL AND sale_start IS NOT NULL AND sale_end IS NOT NULL
                         AND sale_start <= NOW() AND sale_end >= NOW() AND sale_type = 'percent'
                    THEN GREATEST(0, price * (1 - sale_amount / 100))
                    WHEN sale_type IS NOT NULL AND sale_start IS NOT NULL AND sale_end IS NOT NULL
                         AND sale_start <= NOW() AND sale_end >= NOW() AND sale_type = 'fixed'
                    THEN GREATEST(0, price - sale_amount)
                    ELSE price
                END
            ) FROM ticket WHERE event_id = ? AND (sold IS NULL OR sold = 0)",
            [$eventId]
        );
        return $value === null ? null : (float) $value;
    }

    public function countByOrderItemsForUser(int $userId): int
    {
        $sql = 'SELECT COALESCE(SUM(oi.quantity), 0)
                  FROM order_items oi
                  JOIN orders o ON o.id = oi.order_id
                 WHERE o.user_id = ? AND o.status > 0';
        return (int) R::getCell($sql, [$userId]);
    }
}
