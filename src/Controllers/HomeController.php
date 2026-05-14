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

/**
 * Public-facing landing pages: home grid, events-near-you map, and the
 * shopping cart. None of these require login, but cart checkout (handled
 * by CartController) does.
 */
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

    /**
     * Home page: featured upcoming events on top, the rest in a grid below,
     * dynamic category pills, and live counts in the hero badges.
     */
    public function index(Request $request, Response $response): Response
    {
        // Paginate the main grid (3 cols × 10 rows). The featured row above
        // is a fixed 3 cards and stays unpaginated.
        $queryParams = $request->getQueryParams();
        $page    = max(1, (int) ($queryParams['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        // Events with active sale tickets for the featured "On Sale" row.
        // Limit to 3 in SQL; count separately to avoid fetching all rows just for the badge.
        $featured = $this->eventModel->hydrate(
            $this->eventModel->getWithOnSaleTickets(3),
            $this->venueModel,
            $this->ticketModel
        );

        // Paginated upcoming events for the main grid below.
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

    /**
     * "All On Sale" listing: every upcoming event that has at least one
     * ticket currently within its sale window.
     */
    public function showOnSale(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page    = max(1, (int) ($queryParams['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $events = $this->eventModel->hydrate(
            $this->eventModel->getWithOnSaleTickets($perPage, $offset),
            $this->venueModel,
            $this->ticketModel
        );

        // Tag every event so event-card.html.twig shows the SALE ribbon.
        foreach ($events as $event) {
            $event->badge = 'SALE';
        }

        $totalEvents = $this->eventModel->countWithOnSaleTickets();
        $totalPages  = (int) ceil($totalEvents / $perPage);

        $html = $this->twig->render('event/on_sale.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'events',
            'events'        => $events,
            // Pass the total separately so the "X events" header stays
            // accurate across pages (events|length is now just the page count).
            'total_events'  => $totalEvents,
            'current_page'  => $page,
            'total_pages'   => $totalPages,
            'query_params'  => $queryParams,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Cart page: hydrates the session cart at render time so prices and
     * names are always fresh.
     */
    public function showCart(Request $request, Response $response): Response
    {
        // If the user is returning from the Stripe redirect (cancel button or browser
        // back button — bfcache means back button never hits showCheckout), restore
        // the original expiry so the timer doesn't jump to 30 minutes.
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

        // 15% service fee on the (post-discount) subtotal. On this page no
        // discount has been applied yet, so it's just 15% of the subtotal —
        // the cart's inline JS recomputes this live when the user types a
        // points value, mirroring the server-side math in CartController.
        $serviceFee = round($subtotal * 0.15, 2);
        $total      = round($subtotal + $serviceFee, 2);

        $userId      = Auth::userId();
        $userPoints  = 0;
        // Points cap is subtotal in cents — applying all of it zeroes the
        // taxable amount (and the tax), which the JS handles automatically.
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
            // Earn rate is 20% of subtotal (20 points per $1). Keep this in sync with
            // CartController::createOrderDirectly() and StripeWebhookController so the
            // cart preview matches what actually credits to the user post-purchase.
            'points_earned'     => (int) floor($subtotal * 0.20),
            'user_points'       => $userPoints,
            'max_discount'      => min($userPoints, $maxDiscount),
            'seconds_remaining' => Cart::secondsRemaining(),
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Map page: shows upcoming events on the map with their
     * venue + cheapest ticket price.
     *
     * For venues without stored lat/lng coordinates, the address is
     * geocoded server-side (via Google Geocoding API) and cached back
     * to the venue record so subsequent loads are instant.
     */
    /** Per-page batch size for the /map infinite-scroll sidebar. */
    private const MAP_PAGE_SIZE = 20;

    public function showMap(Request $request, Response $response): Response
    {
        // Initial render shows only the first batch — the rest stream in via
        // /api/map-events as the user scrolls the sidebar.
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
            // Surfaced to JS so the load-more loop knows when to stop firing.
            'map_total'     => $totalEvents,
            'map_per_page'  => self::MAP_PAGE_SIZE,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * GET /api/map-events?page=N — JSON endpoint that powers the map page's
     * infinite-scroll sidebar. Returns the next batch of upcoming events in
     * the same shape as the initial `events_json` blob.
     */
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

    /**
     * Map shape — the same payload both showMap and mapEventsJson hand to JS.
     * Keeping it in one place stops the two paths from drifting.
     */
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

    /**
     * For each event whose venue has no cached lat/lng yet, geocode the
     * address server-side and write the coordinates back so the next request
     * skips the Google API call. Pulled out of showMap so the new
     * mapEventsJson endpoint gets the same behavior without duplicating
     * the loop.
     */
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

    /**
     * Geocode an address string via the Google Geocoding API.
     *
     * Returns ['lat' => float, 'lng' => float] on success, or null on failure.
     */
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
