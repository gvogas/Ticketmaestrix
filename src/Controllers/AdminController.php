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

    public function showAdminDashboard(Request $request, Response $response): Response
    {
        $stats = [
            'revenue'       => $this->orderModel->getTotalRevenue(),
            'tickets_sold'  => $this->orderItemModel->totalQuantitySold(),
            'active_events' => $this->eventModel->countActive(),
            'customers'     => $this->userModel->customerCount(),
        ];

        $topEvents = $this->eventModel->topByPerformance(10);

        // Each tab uses its own page param (?ev= for events, ?u= for users) so flipping pages on one tab doesn't reset the other.
        $queryParams = $request->getQueryParams();
        $perPage     = 30;

        $evPage   = max(1, (int) ($queryParams['ev'] ?? 1));
        $evOffset = ($evPage - 1) * $perPage;

        $uPage   = max(1, (int) ($queryParams['u'] ?? 1));
        $uOffset = ($uPage - 1) * $perPage;

        $events = $this->eventModel->hydrate(
            $this->eventModel->getPaginated($perPage, $evOffset),
            $this->venueModel,
            $this->ticketModel
        );
        $eventsTotal = $this->eventModel->countAll();
        $eventsPages = (int) ceil($eventsTotal / $perPage);

        $users      = $this->userModel->findAllPaginated($perPage, $uOffset);
        $usersTotal = $this->userModel->countAll();
        $usersPages = (int) ceil($usersTotal / $perPage);

        $html = $this->twig->render('admin/admin_dashboard.html.twig', [
            'base_path'        => $this->basePath,
            'current_route'    => 'admin',
            'admin_user'       => Auth::user(),
            'stats'            => $stats,
            'top_events'       => $topEvents,
            'events'           => $events,
            'users'            => $users,
            'categories'       => $this->categoryModel->getAll(),
            'venues'           => $this->venueModel->getAll(),
            'events_page'      => $evPage,
            'events_pages'     => $eventsPages,
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
