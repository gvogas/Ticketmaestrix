<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\BeanHelper;
use RedBeanPHP\R;

class OrderModel
{
    public function findByUser(int $userId): array
    {
        return BeanHelper::castBeanArray(R::find('orders', 'user_id = ? ORDER BY order_time DESC', [$userId]));
    }

    /**
     * Orders for a user with their line items + ticket + event already joined,
     * in a single query. Returns an array of stdClass orders (newest first),
     * each carrying an `items` array of stdClass line items. Used by the
     * `/profile` purchase-history card.
     *
     * One raw SQL query rather than bean hydration: hydrating orders →
     * order_items → ticket → events would fire 1 + 3N round-trips. This
     * mirrors the stdClass-from-raw-SQL pattern used by
     * EventModel::getWithOnSaleTickets (see CLAUDE.md "OODBBean vs stdClass").
     */
    public function findByUserWithItems(int $userId): array
    {
        // `t`.`row` is backticked because `row` is a MySQL reserved word —
        // TicketModel does the same in its `getAll`/`findByEvent` SQL.
        $sql = 'SELECT o.id            AS order_id,
                       o.total_price   AS total_price,
                       o.status        AS status,
                       o.order_time    AS order_time,
                       o.points_earned AS points_earned,
                       o.points_spent  AS points_spent,
                       oi.id           AS item_id,
                       oi.quantity     AS quantity,
                       t.id            AS ticket_id,
                       t.`row`         AS ticket_row,
                       t.seat          AS ticket_seat,
                       t.price         AS ticket_price,
                       e.id            AS event_id,
                       e.title         AS event_title,
                       e.event_image   AS event_image,
                       e.date          AS event_date
                  FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             LEFT JOIN ticket       t ON t.id        = oi.ticket_id
             LEFT JOIN events       e ON e.id        = t.event_id
                 WHERE o.user_id = ?
              ORDER BY o.order_time DESC, oi.id ASC';

        $rows = R::getAll($sql, [$userId]);

        // Collapse the join result into one entry per order with embedded items.
        $byOrder = [];
        foreach ($rows as $r) {
            $oid = (int) $r['order_id'];
            if (!isset($byOrder[$oid])) {
                $byOrder[$oid] = (object) [
                    'id'            => $oid,
                    'total_price'   => (float) $r['total_price'],
                    'status'        => (int)   $r['status'],
                    'order_time'    => (string) $r['order_time'],
                    'points_earned' => (int) ($r['points_earned'] ?? 0),
                    'points_spent'  => (int) ($r['points_spent']  ?? 0),
                    'items'         => [],
                ];
            }
            // LEFT JOIN can yield a single row with NULL item_id for an order
            // that somehow has no line items — keep the order visible but skip
            // the empty item.
            if ($r['item_id'] !== null) {
                $byOrder[$oid]->items[] = (object) [
                    'quantity'    => (int) $r['quantity'],
                    'ticket_id'   => $r['ticket_id']   !== null ? (int) $r['ticket_id']   : null,
                    'event_id'    => $r['event_id']    !== null ? (int) $r['event_id']    : null,
                    'event_title' => (string) ($r['event_title'] ?? ''),
                    'event_image' => (string) ($r['event_image'] ?? ''),
                    'event_date'  => (string) ($r['event_date']  ?? ''),
                    'row'         => (string) ($r['ticket_row']  ?? ''),
                    'seat'        => (string) ($r['ticket_seat'] ?? ''),
                    'price'       => (float)  ($r['ticket_price'] ?? 0),
                ];
            }
        }
        return array_values($byOrder);
    }

    public function load(int $id): mixed
    {
        return BeanHelper::castBeanProperties(R::load('orders', $id));
    }

    public function create(float $totalPrice, int $userId): void
    {
        $bean = R::dispense('orders');
        $bean->total_price   = $totalPrice;
        $bean->status        = 0;
        $bean->order_time    = date('Y-m-d H:i:s');
        $bean->user_id       = $userId;
        $bean->points_earned = 0;
        $bean->points_spent  = 0;
        R::store($bean);
    }

    public function save(mixed $bean): void
    {
        R::store($bean);
    }

    public function delete(mixed $bean): void
    {
        R::trash($bean);
    }

    /**
     * All orders, newest first. Used by the admin events tab indirectly
     * and any future admin-orders listing.
     */
    public function findAll(): array
    {
        return BeanHelper::castBeanArray(
            R::findAll('orders', 'ORDER BY order_time DESC')
        );
    }

    /**
     * Site-wide revenue from paid orders only (status > 0). Drives the
     * "Total Revenue" admin card.
     */
    public function getTotalRevenue(): float
    {
        return (float) R::getCell(
            'SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status > 0'
        );
    }

    /**
     * Total dollars one user has spent on paid orders. Drives the
     * "Total Spent" stat on the profile page.
     */
    public function totalSpentByUser(int $userId): float
    {
        return (float) R::getCell(
            'SELECT COALESCE(SUM(total_price), 0)
               FROM orders
              WHERE user_id = ? AND status > 0',
            [$userId]
        );
    }

    /**
     * Number of distinct events the user has tickets for, via paid orders.
     * Powers the "Events Attended" stat on the profile page.
     */
    public function eventsAttendedByUser(int $userId): int
    {
        $sql = 'SELECT COUNT(DISTINCT t.event_id)
                  FROM order_items oi
                  JOIN orders o  ON o.id = oi.order_id
                  JOIN ticket t  ON t.id = oi.ticket_id
                 WHERE o.user_id = ? AND o.status > 0';
        return (int) R::getCell($sql, [$userId]);
    }
}
