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

        $events = $this->eventModel->hydrate(
            $this->eventModel->getAll(),
            $this->venueModel,
            $this->ticketModel
        );

        $users = $this->userModel->findAll();

        $html = $this->twig->render('admin/admin_dashboard.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'admin',
            'admin_user'    => Auth::user(),
            'stats'         => $stats,
            'events'        => $events,
            'users'         => $users,
            'categories'    => $this->categoryModel->getAll(),
            'venues'        => $this->venueModel->getAll(),
        ]);

        $response->getBody()->write($html);
        return $response;
    }

}