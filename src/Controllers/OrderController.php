<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\OrderModel;
use App\Models\UserModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class OrderController {
    public function __construct(
        private Environment $twig,
        private OrderModel $orderModel,
        private UserModel $userModel,
        private string $basePath,
    ) {}

     public function store(Request $request, Response $response): Response {
       $data = $request->getParsedBody();

       $this->orderModel->create(
           (float) ($data['total_price'] ?? 0),
           (int) ($data['user_id'] ?? 0)
       );

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.order_created'];
        return $response
            ->withHeader('Location', $this->basePath . '/orders')
            ->withStatus(302);
     }

     public function update(Request $request, Response $response, array $args): Response {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $order = $this->orderModel->load($id);

        if ($order->id) {
            $order->total_price = (float) ($data['total_price'] ?? $order->total_price);
            $order->status = (int) ($data['status'] ?? $order->status);
            $order->user_id = (int) ($data['user_id'] ?? $order->user_id);
            $order->order_time = (string) ($data['order_time'] ?? $order->order_time);
            $this->orderModel->save($order);
        }

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.order_updated'];
        return $response
            ->withHeader('Location', $this->basePath . '/orders')
            ->withStatus(302);

     }

     public function delete(Request $request, Response $response, array $args): Response {
        $order = $this->orderModel->load((int) ($args['id'] ?? 0));

        if ($order->id) {
            $this->orderModel->delete($order);
        }

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.order_deleted'];
        return $response
            ->withHeader('Location', $this->basePath . '/orders')
            ->withStatus(302);
     }

     public function viewDetails(Request $request, Response $response, array $args): Response {
        $order = $this->orderModel->load((int) ($args['id'] ?? 0));

        if (!$order->id) {
            return $response
                ->withHeader('Location', $this->basePath . '/orders')
                ->withStatus(302);
        }

        $user = $this->userModel->load((int) $order->user_id);
        $customerName = '';
        if ($user && $user->id) {
            $customerName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                ?: (string) ($user->email ?? '');
        }

        $html = $this->twig->render('order/order_detail.html.twig', [
            'base_path'     => $this->basePath,
            'order'         => $order,
            'customer_name' => $customerName,
        ]);

        $response->getBody()->write($html);
        return $response;
     }

     public function byUser(Request $request, Response $response, array $args): Response {
        $userId      = (int) ($args['id'] ?? 0);
        $queryParams = $request->getQueryParams();
        $page    = max(1, (int) ($queryParams['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $orders     = $this->orderModel->findByUserPaginated($userId, $perPage, $offset);
        $total      = $this->orderModel->countByUser($userId);
        $totalPages = (int) ceil($total / $perPage);

        $html = $this->twig->render('order/orders_by_user.html.twig', [
            'base_path'    => $this->basePath,
            'orders'       => $orders,
            'user_id'      => $userId,
            // Total separately so the "X order(s)" badge keeps showing the full count.
            'total_orders' => $total,
            'current_page' => $page,
            'total_pages'  => $totalPages,
            'query_params' => $queryParams,
        ]);

        $response->getBody()->write($html);
        return $response;
     }
}