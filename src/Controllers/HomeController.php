<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Cart;
use App\Models\CategoryModel;
use App\Models\EventModel;
use App\Models\OrderItemModel;
use App\Models\TicketModel;
use App\Models\VenueModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class HomeController
{
    public function __construct(
        private Environment     $twig,
        private EventModel      $eventModel,
        private CategoryModel   $categoryModel,
        private TicketModel     $ticketModel,
        private VenueModel      $venueModel,
        private OrderItemModel  $orderItemModel,
        private string          $basePath,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $featured = $this->eventModel->hydrate(
            $this->eventModel->getWithOnSaleTickets(3),
            $this->venueModel,
            $this->ticketModel
        );

        $rest = $this->eventModel->hydrate(
            $this->eventModel->getUpcoming(),
            $this->venueModel,
            $this->ticketModel
        );

        $html = $this->twig->render('home/home.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'home',
            'featured'      => $featured,
            'events'        => $rest,
            'categories'    => $this->categoryModel->getAll(),
            'live_count'    => $this->eventModel->countActive(),
            'on_sale_count' => $this->eventModel->countWithOnSaleTickets(),
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function showOnSale(Request $request, Response $response): Response
    {
        $perPage = 9;
        $page    = max(1, (int) ($request->getQueryParams()['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;
        $total   = $this->eventModel->countWithOnSaleTickets();

        $events = $this->eventModel->hydrate(
            $this->eventModel->getWithOnSaleTickets($perPage, $offset),
            $this->venueModel,
            $this->ticketModel
        );

        foreach ($events as $event) {
            $event->badge = 'SALE';
        }

        $html = $this->twig->render('event/on_sale.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'events',
            'events'        => $events,
            'total'         => $total,
            'currentPage'   => $page,
            'totalPages'    => (int) ceil($total / $perPage),
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function showCart(Request $request, Response $response): Response
    {
        // bfcache means back-button can land here after Stripe cancel - restore old expiry
        $pre = $_SESSION['cart_expires_at_pre_stripe'] ?? null;
        if ($pre !== null) {
            unset($_SESSION['cart_expires_at_pre_stripe']);
            if ($pre > time()) {
                $_SESSION['cart_expires_at'] = $pre;
            }
        }

        Cart::checkExpiry();

        $cart     = Cart::hydrate($this->ticketModel, $this->eventModel, $this->venueModel);
        $subtotal = Cart::subtotal($cart);

        $serviceFee = round($subtotal * Cart::SERVICE_FEE_RATE, 2);
        $total      = round($subtotal + $serviceFee, 2);

        $userId      = Auth::userId();
        $userPoints  = 0;
        $maxDiscount = 0;

        if ($userId) {
            $user = Auth::user();
            $userPoints  = (int) ($user->points ?? 0);
            $maxDiscount = (int) floor($subtotal * 100);
        }

        $html = $this->twig->render('home/cart.html.twig', [
            'base_path'         => $this->basePath,
            'current_route'     => 'cart',
            'cart'              => $cart,
            'subtotal'          => $subtotal,
            'service_fee'       => $serviceFee,
            'total'             => $total,
            'points_earned'     => (int) floor($subtotal * 0.20),
            'user_points'       => $userPoints,
            'max_discount'      => min($userPoints, $maxDiscount),
            'seconds_remaining' => Cart::secondsRemaining(),
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function showMap(Request $request, Response $response): Response
    {
        $events = $this->eventModel->hydrate(
            $this->eventModel->getUpcoming(),
            $this->venueModel,
            $this->ticketModel
        );

        $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';

        if (!empty($apiKey)) {
            foreach ($events as $event) {
                if (
                    ($event->venue_lat === null || $event->venue_lng === null)
                    && !empty($event->venue_address)
                ) {
                    $coords = $this->geocodeAddress($event->venue_address, $apiKey);
                    if ($coords !== null) {
                        $event->venue_lat = $coords['lat'];
                        $event->venue_lng = $coords['lng'];

                        $venue = $this->venueModel->getById((int) ($event->venue_id ?? 0));
                        if ($venue) {
                            $venue->lat = $coords['lat'];
                            $venue->lng = $coords['lng'];
                            $this->venueModel->save($venue);
                        }
                    }
                }
            }
        }

        $mapEvents = array_map(function ($event) {
            return [
                'id'            => $event->id ?? 0,
                'title'         => $event->title ?? '',
                'venue_name'    => $event->venue_name ?? '',
                'venue_address' => $event->venue_address ?? '',
                'venue_lat'     => $event->venue_lat ?? null,
                'venue_lng'     => $event->venue_lng ?? null,
                'min_price'     => $event->min_price ?? null,
                'date'          => $event->date ?? '',
                'event_image'   => $event->event_image ?? '',
                'category'      => $event->category ?? '',
            ];
        }, $events);

        $html = $this->twig->render('home/map.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'map',
            'events'        => $events,
            'events_json'   => json_encode($mapEvents, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    private function geocodeAddress(string $address, string $apiKey): ?array
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='
             . urlencode($address) . '&key=' . $apiKey;

        $context  = stream_context_create(['http' => ['timeout' => 3]]);
        $response = file_get_contents($url, false, $context);
        if ($response === false) {
            error_log('Geocoding request failed for address: ' . $address);
            return null;
        }

        $data = json_decode($response, true);
        if (
            ($data['status'] ?? '') !== 'OK'
            || empty($data['results'][0]['geometry']['location'])
        ) {
            return null;
        }

        return [
            'lat' => (float) $data['results'][0]['geometry']['location']['lat'],
            'lng' => (float) $data['results'][0]['geometry']['location']['lng'],
        ];
    }
}
