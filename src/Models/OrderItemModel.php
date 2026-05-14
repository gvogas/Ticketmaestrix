<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\BeanHelper;
use RedBeanPHP\R;

class OrderItemModel
{
    public function findByOrder(int $orderId): array
    {
        return BeanHelper::castBeanArray(R::find('order_items', 'order_id = ?', [$orderId]));
    }

    /**
     * Paginated variant of findByOrder for admin /order-items/order/{id}.
     * Ordered by id ASC so pagination is stable; LIMIT ? OFFSET ? appended.
     * R::find (read) is safe on the underscored `order_items` table — the
     * CLAUDE.md ban only applies to R::dispense / inserts.
     */
    public function findByOrderPaginated(int $orderId, int $limit, int $offset): array
    {
        return BeanHelper::castBeanArray(
            R::find('order_items', 'order_id = ? ORDER BY id ASC LIMIT ? OFFSET ?', [$orderId, $limit, $offset])
        );
    }

    /**
     * Count of items on a single order. Uses raw SQL via R::getCell to mirror
     * the rest of this model (the create() method also uses raw SQL because
     * R::dispense rejects underscored bean types — see CLAUDE.md).
     */
    public function countByOrder(int $orderId): int
    {
        return (int) R::getCell(
            'SELECT COUNT(*) FROM order_items WHERE order_id = ?',
            [$orderId]
        );
    }

    public function load(int $id): mixed
    {
        return BeanHelper::castBeanProperties(R::load('order_items', $id));
    }

    public function create(int $quantity, int $orderId, int $ticketId): void
    {
        
        R::exec(
            'INSERT INTO order_items (quantity, order_id, ticket_id) VALUES (?, ?, ?)',
            [$quantity, $orderId, $ticketId]
        );
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
     * Total tickets sold across all paid orders. Drives the
     * "Tickets Sold" admin stat card.
     */
    public function totalQuantitySold(): int
    {
        $sql = 'SELECT COALESCE(SUM(oi.quantity), 0)
                  FROM order_items oi
                  JOIN orders o ON o.id = oi.order_id
                 WHERE o.status > 0';
        return (int) R::getCell($sql);
    }
}
