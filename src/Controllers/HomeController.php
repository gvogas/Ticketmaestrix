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
        // Events with active sale tickets for the featured "On Sale" row.
        // Limit to 3 in SQL; count separately to avoid fetching all rows just for the badge.
        $featured = $this->eventModel->hydrate(
            $this->eventModel->getWithOnSaleTickets(3),
            $this->venueModel,
            $this->ticketModel
        );

        // All upcoming events for the main grid below.
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

    /**
     * "All On Sale" listing: every upcoming event that has at least one
     * ticket currently within its sale window.
     */
    public function showOnSale(Request $request, Response $response): Response
    {
        $events = $this->eventModel->hydrate(
            $this->eventModel->getWithOnSaleTickets(),
            $this->venueModel,
            $this->ticketModel
        );

        // Tag every event so event-card.html.twig shows the SALE ribbon.
        foreach ($events as $event) {
            $event->badge = 'SALE';
        }

        $html = $this->twig->render('event/on_sale.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'events',
            'events'        => $events,
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
            'points_earned'     => (int) floor($subtotal * 0.10),
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
    public function showMap(Request $request, Response $response): Response
    {
        $events = $this->eventModel->hydrate(
            $this->eventModel->getUpcoming(),
            $this->venueModel,
            $this->ticketModel
        );

        $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';

        // Server-side geocoding: for venues without coordinates, geocode
        // the address and cache the result back to the venue record so
        // subsequent page loads have instant markers.
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

                        // Cache coordinates back to the venue record
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
