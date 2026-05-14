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
        $queryParams = $request->getQueryParams();
        $page    = max(1, (int) ($queryParams['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        // Featured row is fixed at 3 cards. Limit in SQL so we don't fetch all on-sale rows just for the badge.
        $featured = $this->eventModel->hydrate(
            $this->eventModel->getWithOnSaleTickets(3),
            $this->venueModel,
            $this->ticketModel
        );

        foreach ($featured as $event) {
            $sale = $this->ticketModel->cheapestSaleForEvent((int) $event->id);
            if ($sale && $sale['pct_off'] > 0) {
                $event->original_price = $sale['original'];
                $event->min_price      = $sale['effective'];
                $event->badge          = '-' . $sale['pct_off'] . '% OFF';
            } else {
                $event->badge          = 'SALE';
            }
        }

        $rest = $this->eventModel->hydrate(
            $this->eventModel->getUpcomingPaginated($perPage, $offset),
            $this->venueModel,
            $this->ticketModel
        );
        $totalEvents = $this->eventModel->countUpcoming();
        $totalPages  = (int) ceil($totalEvents / $perPage);

        $html = $this->twig->render('home/home.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'home',
            'featured'      => $featured,
            'events'        => $rest,
            'categories'    => $this->categoryModel->getAll(),
            'live_count'    => $this->eventModel->countActive(),
            'on_sale_count' => $this->eventModel->countWithOnSaleTickets(),
            'current_page'  => $page,
            'total_pages'   => $totalPages,
            'query_params'  => $queryParams,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function showOnSale(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page    = max(1, (int) ($queryParams['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $hasFilters = !empty($queryParams['q'])
            || !empty($queryParams['category'])
            || !empty($queryParams['venue']);

        if ($hasFilters) {
            $filters = [
                'q'        => $queryParams['q']        ?? '',
                'category' => $queryParams['category'] ?? '',
                'venue'    => $queryParams['venue']    ?? '',
            ];
            $rawEvents   = $this->eventModel->searchOnSale($filters, $perPage, $offset);
            $totalEvents = $this->eventModel->countSearchOnSale($filters);
        } else {
            $rawEvents   = $this->eventModel->getWithOnSaleTickets($perPage, $offset);
            $totalEvents = $this->eventModel->countWithOnSaleTickets();
        }

        $events = $this->eventModel->hydrate(
            $rawEvents,
            $this->venueModel,
            $this->ticketModel,
            $this->categoryModel
        );

        foreach ($events as $event) {
            $sale = $this->ticketModel->cheapestSaleForEvent((int) $event->id);
            if ($sale && $sale['pct_off'] > 0) {
                $event->original_price = $sale['original'];
                $event->min_price      = $sale['effective'];
                $event->badge          = '-' . $sale['pct_off'] . '% OFF';
            } else {
                $event->badge          = 'SALE';
            }
        }

        $totalPages = (int) ceil($totalEvents / $perPage);

        $html = $this->twig->render('event/on_sale.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'events',
            'events'        => $events,
            'total_events'  => $totalEvents,
            'current_page'  => $page,
            'total_pages'   => $totalPages,
            'query_params'  => $queryParams,
            'categories'    => $this->categoryModel->getAll(),
            'venues'        => $this->venueModel->getAll(),
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function showCart(Request $request, Response $response): Response
    {
        // The back button can land here after a Stripe cancel. Put the old expiry back so the timer doesn't jump.
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
            // Each $1 spent gives back 20 points.
            'points_earned'     => (int) floor($subtotal * 0.20),
            'user_points'       => $userPoints,
            'max_discount'      => min($userPoints, $maxDiscount),
            'seconds_remaining' => Cart::secondsRemaining(),
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    private const MAP_PAGE_SIZE = 20;

    public function showMap(Request $request, Response $response): Response
    {
        // First batch only. The sidebar loads more in via /api/map-events as the user scrolls.
        $events = $this->eventModel->hydrate(
            $this->eventModel->getUpcomingPaginated(self::MAP_PAGE_SIZE, 0),
            $this->venueModel,
            $this->ticketModel
        );
        $this->backfillVenueCoords($events);

        $totalEvents = $this->eventModel->countUpcoming();
        $mapEvents   = $this->mapEventShape($events);

        $html = $this->twig->render('home/map.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'map',
            'events'        => $events,
            'events_json'   => json_encode($mapEvents, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'map_total'     => $totalEvents,
            'map_per_page'  => self::MAP_PAGE_SIZE,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function mapEventsJson(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page   = max(1, (int) ($params['page'] ?? 1));
        $offset = ($page - 1) * self::MAP_PAGE_SIZE;

        $events = $this->eventModel->hydrate(
            $this->eventModel->getUpcomingPaginated(self::MAP_PAGE_SIZE, $offset),
            $this->venueModel,
            $this->ticketModel
        );
        $this->backfillVenueCoords($events);

        $total       = $this->eventModel->countUpcoming();
        $loadedSoFar = $offset + count($events);

        $response->getBody()->write(json_encode([
            'events'   => $this->mapEventShape($events),
            'page'     => $page,
            'per_page' => self::MAP_PAGE_SIZE,
            'total'    => $total,
            // has_more lets the JS stop firing requests once everything is loaded.
            'has_more' => $loadedSoFar < $total,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));

        return $response->withHeader('Content-Type', 'application/json');
    }

    // Map data shape shared by showMap and mapEventsJson so the two paths stay in sync.
    private function mapEventShape(array $events): array
    {
        return array_map(function ($event) {
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
    }

    // Geocode any venue that has no cached lat/lng and write the coordinates back so the next request skips the API call.
    private function backfillVenueCoords(array $events): void
    {
        $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';
        if (empty($apiKey)) {
            return;
        }

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
