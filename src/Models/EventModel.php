<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\BeanHelper;
use RedBeanPHP\R;

class EventModel
{
    public function findAll(): array
    {
        return BeanHelper::castBeanArray(R::findAll('events', 'ORDER BY date ASC'));
    }

    public function getAll(): array
    {
        return $this->findAll();
    }

    public function load(int $id): mixed
    {
        return BeanHelper::castBeanProperties(R::load('events', $id));
    }

    public function getById(int $id): mixed
    {
        $bean = R::load('events', $id);
        return BeanHelper::isValidBean($bean) ? BeanHelper::castBeanProperties($bean) : null;
    }

    public function findByCategory(int $categoryId): array
    {
        return BeanHelper::castBeanArray(R::find('events', 'category_id = ? ORDER BY date ASC', [$categoryId]));
    }

    public function create(string $title, string $description, string $date,
                           int $venueId, int $categoryId, string $eventImage): mixed
    {
        $bean = R::dispense('events');
        $bean->title       = $title;
        $bean->description = $description;
        $bean->date        = $date;
        $bean->venue_id    = $venueId;
        $bean->category_id = $categoryId;
        $bean->event_image = $eventImage;
        R::store($bean);
        return BeanHelper::castBeanProperties($bean);
    }

    public function save(mixed $bean): void
    {
        R::store($bean);
    }

    public function delete(mixed $bean): void
    {
        R::trash($bean);
    }

    public function getUpcoming(): array
    {
        return BeanHelper::castBeanArray(
            R::find('events', 'date >= NOW() ORDER BY date ASC')
        );
    }

    public function getWithOnSaleTickets(?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT DISTINCT e.*
                  FROM events e
                  JOIN ticket t ON t.event_id = e.id
                 WHERE e.date >= NOW()
                   AND t.sale_type   IS NOT NULL
                   AND t.sale_start  IS NOT NULL
                   AND t.sale_end    IS NOT NULL
                   AND t.sale_start <= NOW()
                   AND t.sale_end   >= NOW()
                   AND (t.sold IS NULL OR t.sold = 0)
                 ORDER BY e.date ASC";

        $bindings = [];
        if ($limit !== null) {
            $sql .= ' LIMIT ? OFFSET ?';
            $bindings[] = $limit;
            $bindings[] = $offset;
        }

        $rows = R::getAll($sql, $bindings);

        $out = [];
        foreach ($rows as $row) {
            $obj = new \stdClass();
            foreach ($row as $k => $v) {
                $obj->$k = $v;
            }
            foreach (['id', 'category_id', 'venue_id'] as $field) {
                if (isset($obj->$field)) {
                    $obj->$field = (int) $obj->$field;
                }
            }
            $out[] = $obj;
        }
        return $out;
    }

    public function countWithOnSaleTickets(): int
    {
        return (int) R::getCell(
            "SELECT COUNT(DISTINCT e.id)
               FROM events e
               JOIN ticket t ON t.event_id = e.id
              WHERE e.date >= NOW()
                AND t.sale_type   IS NOT NULL
                AND t.sale_start  IS NOT NULL
                AND t.sale_end    IS NOT NULL
                AND t.sale_start <= NOW()
                AND t.sale_end   >= NOW()
                AND (t.sold IS NULL OR t.sold = 0)"
        );
    }

    public function countActive(): int
    {
        return (int) R::getCell('SELECT COUNT(*) FROM events WHERE date >= NOW()');
    }

    // stdClass instead of modifying frozen beans directly - they cant take extra props
    public function hydrate(array $events, VenueModel $venues, TicketModel $tickets, ?CategoryModel $categories = null): array
    {
        $out = [];
        foreach ($events as $event) {
            $hydrated = new \stdClass();

            foreach ($event as $key => $value) {
                $hydrated->$key = $value;
            }

            $venue = $venues->getById((int) ($hydrated->venue_id ?? 0));
            $hydrated->venue_name    = $venue ? (string) $venue->name : '';
            $hydrated->venue_address = $venue ? (string) $venue->address : '';
            $hydrated->venue_lat     = $venue && isset($venue->lat) ? (float) $venue->lat : null;
            $hydrated->venue_lng     = $venue && isset($venue->lng) ? (float) $venue->lng : null;

            $hydrated->min_price = $tickets->minPriceForEvent((int) ($hydrated->id ?? 0));

            if ($categories !== null && isset($hydrated->category_id)) {
                $category = $categories->getById((int) $hydrated->category_id);
                $hydrated->category = $category ? (string) $category->name : 'Uncategorized';
            } elseif (!isset($hydrated->category)) {
                $hydrated->category = 'Uncategorized';
            }

            $out[] = $hydrated;
        }
        return $out;
    }


    public function getPaginated(int $limit, int $offset): array
    {
        return R::findAll('events', ' LIMIT ? OFFSET ? ', [$limit, $offset]);
    }

    public function countAll(): int
    {
        return (int) R::count('events');
    }

public function search(array $filters, int $limit, int $offset): array
{
    $where = [];
    $bindings = [];

    if (!empty($filters['q'])) {
        $searchTerm = '%' . $filters['q'] . '%';
        $where[] = '(events.title LIKE ? OR venue.name LIKE ? OR venue.address LIKE ?)';
        $bindings[] = $searchTerm;
        $bindings[] = $searchTerm;
        $bindings[] = $searchTerm;
    }

    if (!empty($filters['category'])) {
        $where[] = 'events.category_id = ?';
        $bindings[] = (int) $filters['category'];
    }

    if (!empty($filters['venue'])) {
        $where[] = 'events.venue_id = ?';
        $bindings[] = (int) $filters['venue'];
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $query = "SELECT events.* FROM events
              LEFT JOIN venue ON venue.id = events.venue_id
              $whereClause
              ORDER BY events.date ASC
              LIMIT ? OFFSET ?";

    $bindings[] = $limit;
    $bindings[] = $offset;

    $rawResults = R::getAll($query, $bindings);

    $results = [];
    foreach ($rawResults as $row) {
        $obj = new \stdClass();
        foreach ($row as $key => $value) {
            $obj->$key = $value;
        }
        $results[] = $obj;
    }

    return $results;
}

public function countSearch(array $filters): int
{
    $where = [];
    $bindings = [];

    if (!empty($filters['q'])) {
        $searchTerm = '%' . $filters['q'] . '%';
        $where[] = '(events.title LIKE ? OR venue.name LIKE ? OR venue.address LIKE ?)';
        $bindings[] = $searchTerm;
        $bindings[] = $searchTerm;
        $bindings[] = $searchTerm;
    }

    if (!empty($filters['category'])) {
        $where[] = 'events.category_id = ?';
        $bindings[] = (int) $filters['category'];
    }

    if (!empty($filters['venue'])) {
        $where[] = 'events.venue_id = ?';
        $bindings[] = (int) $filters['venue'];
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $query = "SELECT COUNT(*) FROM events
              LEFT JOIN venue ON venue.id = events.venue_id
              $whereClause";
    
    return (int) R::getCell($query, $bindings);
}
}
