<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\CategoryModel;
use App\Models\EventModel;
use App\Models\TicketModel;
use App\Models\VenueModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class EventController
{
    public function __construct(
        private Environment $twig,
        private EventModel $eventModel,
        private CategoryModel $categoryModel,
        private VenueModel $venueModel,
        private string $basePath,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;

        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        // Check if there are any search/filter parameters
        $hasFilters = !empty($queryParams['q']) || !empty($queryParams['category']) || !empty($queryParams['venue']);

        if ($hasFilters) {
            // Use search functionality
            $filters = [
                'q' => $queryParams['q'] ?? '',
                'category' => $queryParams['category'] ?? '',
                'venue' => $queryParams['venue'] ?? '',
            ];

            $events = $this->eventModel->search($filters, $perPage, $offset);
            $totalEvents = $this->eventModel->countSearch($filters);
        } else {
            // No filters - use regular pagination
            $events = $this->eventModel->getPaginated($perPage, $offset);
            $totalEvents = $this->eventModel->countAll();
        }

        $totalPages = ceil($totalEvents / $perPage);

        // Check if user is admin - render admin dashboard or user-friendly page
        if (\App\Helpers\Auth::isAdmin()) {
            // Admin sees the management console with edit/delete capabilities
            $html = $this->twig->render('event/index.html.twig', [
                'base_path'   => $this->basePath,
                'events'      => $events,
                'currentPage' => $page,
                'totalPages'  => $totalPages,
            ]);
        } else {
            // Regular users see a clean events listing page
            // Hydrate events with venue, category, and ticket information for the user view
            $events = $this->eventModel->hydrate($events, $this->venueModel, new \App\Models\TicketModel(), $this->categoryModel);

            $html = $this->twig->render('event/user_index.html.twig', [
                'base_path'    => $this->basePath,
                'events'       => $events,
                'currentPage'  => $page,
                'totalPages'   => $totalPages,
                'categories'   => $this->categoryModel->getAll(),
                'venues'       => $this->venueModel->getAll(),
                'query_params' => $queryParams,
            ]);
        }

        $response->getBody()->write($html);
        return $response;
    }

    public function create(Request $request, Response $response): Response
    {
        // Admin-only: anyone else gets bounced to /login or /.
        if ($redirect = Auth::requireAdmin($response, $this->basePath)) {
            return $redirect;
        }
        $html = $this->twig->render('event/create.html.twig', [
            'base_path'  => $this->basePath,
            'categories' => $this->categoryModel->getAll(),
            'venues'     => $this->venueModel->getAll(),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function store(Request $request, Response $response): Response
    {
        if ($redirect = Auth::requireAdmin($response, $this->basePath)) {
            return $redirect;
        }

        $data = (array) ($request->getParsedBody() ?? []);

        $errors = [];
        if (empty($data['title']))       $errors['title']       = ['Title is required.'];
        if (empty($data['description'])) $errors['description'] = ['Description is required.'];
        if (empty($data['date']))        $errors['date']        = ['Date is required.'];
        if (empty($data['venue_id']))    $errors['venue_id']    = ['Please select a venue.'];
        if (empty($data['category_id'])) $errors['category_id'] = ['Please select a category.'];

        if ($errors) {
            $html = $this->twig->render('event/create.html.twig', [
                'base_path'  => $this->basePath,
                'categories' => $this->categoryModel->getAll(),
                'venues'     => $this->venueModel->getAll(),
                'errors'     => $errors,
                'input'      => $data,
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(422);
        }

        $this->eventModel->create(
            (string) ($data['title'] ?? ''),
            (string) ($data['description'] ?? ''),
            (string) ($data['date'] ?? ''),
            (int) ($data['venue_id'] ?? 0),
            (int) ($data['category_id'] ?? 0),
            (string) ($data['event_image'] ?? ''),
        );

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.event_created'];
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        if ($redirect = Auth::requireAdmin($response, $this->basePath)) {
            return $redirect;
        }
        $event = $this->eventModel->getById((int) $args['id']);

        if (!$event) {
            return $response->withHeader('Location', $this->basePath . '/events')->withStatus(302);
        }

        $html = $this->twig->render('event/edit.html.twig', [
            'base_path'  => $this->basePath,
            'event'      => $event,
            'categories' => $this->categoryModel->getAll(),
            'venues'     => $this->venueModel->getAll(),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        if ($redirect = Auth::requireAdmin($response, $this->basePath)) {
            return $redirect;
        }

        $id   = (int) $args['id'];
        $data = (array) ($request->getParsedBody() ?? []);

        $errors = [];
        if (empty($data['title']))       $errors['title']       = ['Title is required.'];
        if (empty($data['description'])) $errors['description'] = ['Description is required.'];
        if (empty($data['date']))        $errors['date']        = ['Date is required.'];
        if (empty($data['venue_id']))    $errors['venue_id']    = ['Please select a venue.'];
        if (empty($data['category_id'])) $errors['category_id'] = ['Please select a category.'];

        if ($errors) {
            $event = $this->eventModel->getById($id);
            $html  = $this->twig->render('event/edit.html.twig', [
                'base_path'  => $this->basePath,
                'event'      => $event,
                'categories' => $this->categoryModel->getAll(),
                'venues'     => $this->venueModel->getAll(),
                'errors'     => $errors,
                'input'      => $data,
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(422);
        }

        $event = $this->eventModel->load($id);

        if ($event->id) {
            $event->title       = (string) ($data['title'] ?? $event->title);
            $event->description = (string) ($data['description'] ?? $event->description);
            $event->date        = (string) ($data['date'] ?? $event->date);
            $event->venue_id    = (int) ($data['venue_id'] ?? $event->venue_id);
            $event->category_id = (int) ($data['category_id'] ?? $event->category_id);
            $event->event_image = (string) ($data['event_image'] ?? $event->event_image);
            $this->eventModel->save($event);
        }

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.event_updated'];
        return $response->withHeader('Location', $this->basePath . '/events/' . $id . '/edit')->withStatus(302);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        if ($redirect = Auth::requireAdmin($response, $this->basePath)) {
            return $redirect;
        }
        $event = $this->eventModel->load((int) $args['id']);
        if ($event->id) {
            $this->eventModel->delete($event);
        }
        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.event_deleted'];
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }

    public function viewDetails(Request $request, Response $response, array $args): Response
    {
        $event = $this->eventModel->getById((int) $args['id']);

        if (!$event) {
            return $response->withHeader('Location', $this->basePath . '/events')->withStatus(302);
        }

        // Check if user is admin to show different views
        $isAdmin = \App\Helpers\Auth::isAdmin();

        // For admin view, show basic event info (admin can manage from index page)
        // For user view, hydrate with venue, category, and ticket information
        if (!$isAdmin) {
            $event = $this->eventModel->hydrate([$event], $this->venueModel, new \App\Models\TicketModel(), $this->categoryModel)[0];
        }

        $html = $this->twig->render('event/event_detail.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'events',
            'event'         => $event,
            'is_admin'      => $isAdmin,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function byCategory(Request $request, Response $response, array $args): Response
    {
        $categoryId = (int) $args['id'];
        $events     = $this->eventModel->findByCategory($categoryId);
        $category   = $this->categoryModel->getById($categoryId);

        $html = $this->twig->render('event/events_by_category.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'events',
            'events'        => $events,
            'category'      => $category,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    /** GET /api/search?q= — JSON live-search endpoint */
    public function searchJson(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $q      = trim($params['q'] ?? '');

        $events = [];
        $qLen = mb_strlen($q);
        if ($qLen >= 1 && $qLen <= 100) {
            $filters = ['q' => $q, 'category' => '', 'venue' => ''];
            $events  = $this->eventModel->search($filters, 10, 0);
            $events  = $this->eventModel->hydrate($events, $this->venueModel, new TicketModel(), $this->categoryModel);
        }

        $data = array_map(function ($e) {
            return [
                'id'         => $e->id,
                'title'      => $e->title,
                'date'       => $e->date,
                'venue_name' => $e->venue_name ?? '',
                'category'   => $e->category ?? '',
                'image'      => $e->event_image ?? '',
                'min_price'  => $e->min_price,
                'venue_address' => $e->venue_address ?? '',
                'url'        => $this->basePath . '/events/' . $e->id,
            ];
        }, $events);

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
