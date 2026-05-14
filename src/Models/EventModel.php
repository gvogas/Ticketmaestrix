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

    /**
     * Paginated variant of findByCategory for the /events/category/{id} page.
     * Mirrors getPaginated's LIMIT ? OFFSET ? convention.
     */
    public function findByCategoryPaginated(int $categoryId, int $limit, int $offset): array
    {
        return BeanHelper::castBeanArray(
            R::find('events', 'category_id = ? ORDER BY date ASC LIMIT ? OFFSET ?', [$categoryId, $limit, $offset])
        );
    }

    /**
     * Count of events in a single category — pairs with findByCategoryPaginated
     * to drive the page count for the partial.
     */
    public function countByCategory(int $categoryId): int
    {
        return (int) R::count('events', 'category_id = ?', [$categoryId]);
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

    /**
     * All future-or-now events ordered by date.
     *
     * Used by the home page "All Events" grid and the map sidebar.
     */
    public function getUpcoming(): array
    {
        return BeanHelper::castBeanArray(
            R::find('events', 'date >= NOW() ORDER BY date ASC')
        );
    }

    /**
     * Paginated variant of getUpcoming for the home page main grid.
     * Same WHERE/ORDER as getUpcoming with LIMIT ? OFFSET ? appended.
     */
    public function getUpcomingPaginated(int $limit, int $offset): array
    {
        return BeanHelper::castBeanArray(
            R::find('events', 'date >= NOW() ORDER BY date ASC LIMIT ? OFFSET ?', [$limit, $offset])
        );
    }

    /**
     * Count of upcoming events — pairs with getUpcomingPaginated to drive
     * the home-page paginator. Same WHERE as countActive(), kept under a
     * dedicated name for symmetry with the paginated fetcher.
     */
    public function countUpcoming(): int
    {
        return (int) R::count('events', 'date >= NOW()');
    }

    /**
     * Events that currently have at least one ticket actively on sale.
     * Drives both the home-page featured row and the /events/on-sale listing.
     */
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

        // Offset is only meaningful with a limit — MySQL rejects OFFSET without LIMIT.
        // Existing callers that pass only $limit (home-featured row) keep working.
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
            // Cast FK fields to int so templates and controllers behave consistently.
            foreach (['id', 'category_id', 'venue_id'] as $field) {
                if (isset($obj->$field)) {
                    $obj->$field = (int) $obj->$field;
                }
            }
            $out[] = $obj;
        }
        return $out;
    }

    /**
     * Count of events with at least one active sale ticket.
     * Powers the "On Sale" hero badge on the home page.
     */
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

    /**
     * Count of events whose date is today or later. Drives the
     * "Live Events" hero stat and the admin "Active Events" card.
     */
    public function countActive(): int
    {
        return (int) R::getCell('SELECT COUNT(*) FROM events WHERE date >= NOW()');
    }

    /**
     * Decorate event beans with venue_name, venue_address, category_name, and min_price
     * so listing templates have everything they need without per-row
     * lookups in Twig. Returns a new array of stdClass objects with extra properties.
     * 
     * Note: Returns stdClass objects instead of modifying frozen beans directly.
     */
    public function hydrate(array $events, VenueModel $venues, TicketModel $tickets, ?CategoryModel $categories = null): array
    {
        $out = [];
        foreach ($events as $event) {
            // Create a new stdClass to hold all properties (avoids frozen bean issues)
            $hydrated = new \stdClass();
            
            // Copy all existing bean properties
            foreach ($event as $key => $value) {
                $hydrated->$key = $value;
            }
            
            // Add venue information
            $venue = $venues->getById((int) ($hydrated->venue_id ?? 0));
            $hydrated->venue_name    = $venue ? (string) $venue->name : '';
            $hydrated->venue_address = $venue ? (string) $venue->address : '';
            $hydrated->venue_lat     = $venue && isset($venue->lat) ? (float) $venue->lat : null;
            $hydrated->venue_lng     = $venue && isset($venue->lng) ? (float) $venue->lng : null;
            
            // Add ticket pricing
            $hydrated->min_price = $tickets->minPriceForEvent((int) ($hydrated->id ?? 0));
            
            // Add category name if category model is provided
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

/**
 * Search and filter events by query string, category, and venue.
 * Returns paginated results.
 */
public function search(array $filters, int $limit, int $offset): array
{
    $where = [];
    $bindings = [];
    
    // Search by event title, venue name, or venue address
    if (!empty($filters['q'])) {
        $searchTerm = '%' . $filters['q'] . '%';
        $where[] = '(events.title LIKE ? OR venue.name LIKE ? OR venue.address LIKE ?)';
        $bindings[] = $searchTerm;
        $bindings[] = $searchTerm;
        $bindings[] = $searchTerm;
    }

    // Filter by category
    if (!empty($filters['category'])) {
        $where[] = 'events.category_id = ?';
        $bindings[] = (int) $filters['category'];
    }

    // Filter by venue
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
    
    // Convert raw arrays to stdClass objects for compatibility with hydrate()
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

/**
 * Count events matching the search criteria.
 */
public function countSearch(array $filters): int
{
    $where = [];
    $bindings = [];
    
    // Search by event title, venue name, or venue address 
    if (!empty($filters['q'])) {
        $searchTerm = '%' . $filters['q'] . '%';
        $where[] = '(events.title LIKE ? OR venue.name LIKE ? OR venue.address LIKE ?)';
        $bindings[] = $searchTerm;
        $bindings[] = $searchTerm;
        $bindings[] = $searchTerm;
    }

    // Filter by category
    if (!empty($filters['category'])) {
        $where[] = 'events.category_id = ?';
        $bindings[] = (int) $filters['category'];
    }

    // Filter by venue
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
