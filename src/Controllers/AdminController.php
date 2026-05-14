<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\CategoryModel;
use App\Models\EventModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\TicketModel;
use App\Models\UserModel;
use App\Models\VenueModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

/**
 * Admin landing page. Aggregates site-wide stats, lists all events,
 * and manages administrative users.
 */
class AdminController
{
    public function __construct(
        private Environment     $twig,
        private UserModel       $userModel,
        private EventModel      $eventModel,
        private OrderModel      $orderModel,
        private OrderItemModel  $orderItemModel,
        private CategoryModel   $categoryModel,
        private VenueModel      $venueModel,
        private TicketModel     $ticketModel,
        private string          $basePath,
    ) {}

    /**
     * GET /admin — The main dashboard
     * Displays stats, events, and the admin management list.
     */
    public function showAdminDashboard(Request $request, Response $response): Response
    {
        // 1. Aggregate site-wide stats
        $stats = [
            'revenue'       => $this->orderModel->getTotalRevenue(),
            'tickets_sold'  => $this->orderItemModel->totalQuantitySold(),
            'active_events' => $this->eventModel->countActive(),
            'customers'     => $this->userModel->customerCount(),
        ];

        // Two tabs paginate independently via two distinct query params so a
        // navigation click on one tab does not reset the other.
        //   ?ev=N — events tab page
        //   ?u=N  — users tab page
        $queryParams = $request->getQueryParams();
        $perPage     = 30;

        $evPage   = max(1, (int) ($queryParams['ev'] ?? 1));
        $evOffset = ($evPage - 1) * $perPage;

        $uPage   = max(1, (int) ($queryParams['u'] ?? 1));
        $uOffset = ($uPage - 1) * $perPage;

        // 2. Fetch and hydrate events for the "My Events" tab
        $events = $this->eventModel->hydrate(
            $this->eventModel->getPaginated($perPage, $evOffset),
            $this->venueModel,
            $this->ticketModel
        );
        $eventsTotal = $this->eventModel->countAll();
        $eventsPages = (int) ceil($eventsTotal / $perPage);

        // 3. Fetch users for the "Manage Users" tab
        $users      = $this->userModel->findAllPaginated($perPage, $uOffset);
        $usersTotal = $this->userModel->countAll();
        $usersPages = (int) ceil($usersTotal / $perPage);

        $html = $this->twig->render('admin/admin_dashboard.html.twig', [
            'base_path'        => $this->basePath,
            'current_route'    => 'admin',
            'admin_user'       => Auth::user(),
            'stats'            => $stats,
            'events'           => $events,
            'users'            => $users,
            'categories'       => $this->categoryModel->getAll(),
            'venues'           => $this->venueModel->getAll(),
            'events_page'      => $evPage,
            'events_pages'     => $eventsPages,
            // Pass totals separately so the tab badges and "X total" labels
            // keep showing the full count, not just the current page.
            'events_total'     => $eventsTotal,
            'users_page'       => $uPage,
            'users_pages'      => $usersPages,
            'users_total'      => $usersTotal,
            'query_params'     => $queryParams,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

}