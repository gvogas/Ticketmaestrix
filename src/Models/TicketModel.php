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
        return BeanHelper::castBeanArray(R::find('ticket', 'event_id = ? ORDER BY `row`, seat', [$eventId]));
    }

    public function load(int $id): mixed
    {
        return BeanHelper::castBeanProperties(R::load('ticket', $id));
    }

    public function create(float $price, string $seat, string $row, int $eventId): void
    {
        $bean = R::dispense('ticket');
        $bean->price    = $price;
        $bean->seat     = $seat;
        $bean->row      = $row;
        $bean->event_id = $eventId;
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
}
