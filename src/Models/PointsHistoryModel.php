<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\BeanHelper;
use RedBeanPHP\R;

class PointsHistoryModel
{
    public function addPoints(int $userId, int $amount, string $description, ?int $orderId = null): void
    {
        // The table name has an underscore, so RedBean's dispense throws. Use raw SQL instead.
        R::exec(
            'INSERT INTO points_history (user_id, order_id, amount, description, created_at)
                  VALUES (?, ?, ?, ?, ?)',
            [$userId, $orderId, $amount, $description, date('Y-m-d H:i:s')]
        );
    }

    public function findByUser(int $userId, int $limit = 50): array
    {
        return BeanHelper::castBeanArray(
            R::findAll(
                'points_history',
                'user_id = ? ORDER BY created_at DESC LIMIT ?',
                [$userId, $limit]
            )
        );
    }

    public function getTotal(int $userId): int
    {
        return (int) R::getCell(
            'SELECT COALESCE(SUM(amount), 0) FROM points_history WHERE user_id = ?',
            [$userId]
        );
    }
}
