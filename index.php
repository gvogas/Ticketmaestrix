<?php
declare(strict_types=1);

// Hides deprecation warnings. Some of our libraries haven't caught up to PHP 8.4 yet.
error_reporting(E_ALL & ~E_DEPRECATED);

ini_set('display_errors', '0');

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CategoryController;
use App\Controllers\EventController;
use App\Controllers\HomeController;
use App\Controllers\OrderController;
use App\Controllers\OrderItemController;
use App\Controllers\StripeWebhookController;
use App\Controllers\TicketController;
use App\Controllers\UserController;
use App\Controllers\VenueController;
use App\Helpers\Auth;
use App\Helpers\Cart;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\MaintenanceMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use App\Models\CategoryModel;
use App\Models\EventModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\PointsHistoryModel;
use App\Models\TicketModel;
use App\Models\UserModel;
use App\Models\VenueModel;
use App\Services\OtpService;
use App\Services\StripeService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use RedBeanPHP\R;
use Slim\Factory\AppFactory;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

// Bootstrap: load Composer and the .env file.
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Session: start it and grab any flash message left over from the last request.
ini_set('session.cookie_lifetime', '0');
session_start();

$flashMessage = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Database: connect with RedBean
R::setup(
    'mysql:host=' . $_ENV['DB_SERVER']
        . ';port=' . ($_ENV['DB_PORT'] ?? '3306')
        . ';dbname=' . $_ENV['DB_NAME']
        . ';charset=utf8mb4',
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);

$debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
R::freeze(!$debug);

// Without this, R::begin / commit / rollback do nothing in debug (fluid) mode.
R::setAllowFluidTransactions(true);

// Auth: log the user back in if they have a remember-me cookie.
Auth::checkRememberToken();

// One random token per session. Every POST form sends it back to prove the request came from us.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// Template engine: Twig loads templates from /templates and reads globals from below.
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig   = new Environment($loader, ['cache' => false, 'auto_reload' => true]);

$twig->addGlobal('current_user',        Auth::user());
$twig->addGlobal('is_admin',            Auth::isAdmin());
$twig->addGlobal('cart_count',          Cart::count());
$twig->addGlobal('cart_expires_at',     (int) ($_SESSION['cart_expires_at'] ?? 0));
$twig->addGlobal('csrf_token',          $_SESSION['csrf_token']);
$twig->addGlobal('google_maps_api_key', $_ENV['GOOGLE_MAPS_API_KEY'] ?? '');
$twig->addGlobal('flash_message',       $flashMessage);

// Drops a hidden input with the CSRF token into any POST form.
$twig->addFunction(new TwigFunction(
    'csrf_field',
    static fn (): string => '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES | ENT_HTML5, 'UTF-8')
        . '">',
    ['is_safe' => ['html']]
));

// Translations: English and French. The active language is stored in the session.
$locale     = $_SESSION['lang'] ?? 'en';
$translator = new Translator($locale);
$translator->addLoader('array', new ArrayLoader());
$translator->addResource('array', require __DIR__ . '/translations/messages.en.php', 'en');
$translator->addResource('array', require __DIR__ . '/translations/messages.fr.php', 'fr');

$twig->addFunction(new TwigFunction('trans', function (string $key, array $params = []) use ($translator, $locale) {
    return $translator->trans($key, $params, null, $locale);
}));

$twig->addFunction(new TwigFunction('trans_cat', function ($name) use ($translator, $locale) {
    if (is_object($name) && method_exists($name, '__toString')) $name = (string) $name;
    if (!is_string($name) || $name === '') return is_string($name) ? $name : '';
    $key        = 'categories.' . strtolower(str_replace([' ', '-', '\''], '_', $name));
    $translated = $translator->trans($key, [], null, $locale);
    return $translated !== $key ? $translated : $name;
}));

$twig->addGlobal('current_locale', $locale);

// DI container: every controller is wired here by hand. Add new ones to the list.
$basePath = $_ENV['APP_BASE_PATH'] ?? '';

$container = new \DI\Container();
$container->set(Environment::class, $twig);

$responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
$container->set(AuthMiddleware::class,  fn() => new AuthMiddleware($responseFactory, $basePath));
$container->set(AdminMiddleware::class, fn() => new AdminMiddleware($responseFactory, $basePath));

$container->set(HomeController::class, fn() => new HomeController(
    $twig,
    new EventModel(),
    new CategoryModel(),
    new TicketModel(),
    new VenueModel(),
    new OrderItemModel(),
    $basePath,
));

$container->set(AuthController::class, function () use ($twig, $basePath) {
    $userModel = new UserModel();
    return new AuthController($twig, $userModel, new OtpService($userModel), $basePath);
});

$container->set(UserController::class, fn() => new UserController(
    $twig,
    new UserModel(),
    new TicketModel(),
    new OrderModel(),
    new PointsHistoryModel(),
    $basePath,
));

$container->set(AdminController::class, fn() => new AdminController(
    $twig,
    new UserModel(),
    new EventModel(),
    new OrderModel(),
    new OrderItemModel(),
    new CategoryModel(),
    new VenueModel(),
    new TicketModel(),
    $basePath,
));

$container->set(CartController::class, fn() => new CartController(
    $twig,
    new TicketModel(),
    new EventModel(),
    new VenueModel(),
    new OrderModel(),
    new OrderItemModel(),
    new PointsHistoryModel(),
    new StripeService($_ENV['STRIPE_SECRET_KEY'] ?? ''),
    $basePath,
));

$container->set(StripeWebhookController::class, fn() => new StripeWebhookController(
    new OrderModel(),
    new OrderItemModel(),
    new PointsHistoryModel(),
    new TicketModel(),
    $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '',
));

$container->set(CategoryController::class, fn() => new CategoryController(
    $twig,
    new CategoryModel(),
    $basePath,
));

$container->set(EventController::class, fn() => new EventController(
    $twig,
    new EventModel(),
    new CategoryModel(),
    new VenueModel(),
    new TicketModel(),
    $basePath,
));

$container->set(OrderController::class, fn() => new OrderController(
    $twig,
    new OrderModel(),
    new UserModel(),
    $basePath,
));

$container->set(OrderItemController::class, fn() => new OrderItemController(
    $twig,
    new OrderItemModel(),
    $basePath,
));

$container->set(TicketController::class, fn() => new TicketController(
    $twig,
    new TicketModel(),
    new EventModel(),
    new VenueModel(),
    $basePath,
));

$container->set(VenueController::class, fn() => new VenueController(
    $twig,
    new VenueModel(),
    $basePath,
));

// Build the Slim app and turn on the built-in middleware (body parser, router, error handler).
AppFactory::setContainer($container);

$app = AppFactory::create();
$app->setBasePath($basePath);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware($debug, true, true);

// Middleware: a simple request logger that writes one line per request to var/app.log.
$logFile = __DIR__ . '/var/app.log';

$loggerMiddleware = function (Request $request, RequestHandler $handler) use ($logFile) {
    $start    = microtime(true);
    $response = $handler->handle($request);
    $elapsed  = round((microtime(true) - $start) * 1000);

    file_put_contents($logFile, sprintf(
        "[%s] %-6s %-25s → %d  (%dms)\n",
        date('Y-m-d H:i:s'),
        $request->getMethod(),
        $request->getUri()->getPath(),
        $response->getStatusCode(),
        $elapsed
    ), FILE_APPEND);

    return $response;
};

// App-wide middleware. Runs from bottom to top: logger first, then CSRF, headers, maintenance.
$app->add(new MaintenanceMiddleware(__DIR__ . '/var/maintenance.flag', $app->getResponseFactory()));
$app->add(new SecurityHeadersMiddleware());
// Stripe posts /stripe/webhook from their own server, so they cannot send our token.
$app->add(new CsrfMiddleware($responseFactory, ['/stripe/webhook']));
$app->add($loggerMiddleware);

// Public pages.
$app->get('/',     [HomeController::class, 'index']);
$app->get('/cart', [HomeController::class, 'showCart']);
$app->get('/map',  [HomeController::class, 'showMap']);

// Auth: signup, login, logout, 2FA setup, 2FA login.
$app->group('', function ($group) {
    $group->get('/signup',           [AuthController::class, 'showSignup']);
    $group->post('/signup',          [AuthController::class, 'signup']);
    $group->get('/login',            [AuthController::class, 'showLogin']);
    $group->post('/login',           [AuthController::class, 'login']);
    $group->post('/logout',          [AuthController::class, 'logout']);
    $group->get('/2fa/setup',        [AuthController::class, 'show2faSetup']);
    $group->post('/2fa/setup',       [AuthController::class, 'verify2faSetup']);
    $group->get('/2fa/login',        [AuthController::class, 'show2faLogin']);
    $group->post('/2fa/login',       [AuthController::class, 'verify2faLogin']);
});

// Admin dashboard.
$app->get('/admin', [AdminController::class, 'showAdminDashboard'])->add(AdminMiddleware::class);

// Admin: manage users.
$app->group('/users', function ($group) {
    $group->get('',               [UserController::class, 'index']);
    $group->post('',              [UserController::class, 'store']);
    $group->get('/{id}',          [UserController::class, 'viewDetails']);
    $group->post('/{id}',         [UserController::class, 'update']);
    $group->post('/{id}/role',    [UserController::class, 'roleToggle']);
    $group->post('/{id}/delete',  [UserController::class, 'delete']);
})->add(AdminMiddleware::class);

// Profile: the logged-in user's own pages.
$app->group('', function ($group) {
    $group->get('/profile',            [UserController::class, 'showProfile']);
    $group->get('/editprofile',        [UserController::class, 'editProfile']);
    $group->post('/editprofile',      [UserController::class, 'updateProfile']);
    $group->post('/delete-account',   [UserController::class, 'deleteAccount']);
    $group->post('/profile/delete',   [UserController::class, 'deleteAccount']);
})->add(AuthMiddleware::class);

// Admin: manage categories.
$app->group('/categories', function ($group) {
    $group->get('',               [CategoryController::class, 'index']);
    $group->get('/create',        [CategoryController::class, 'create']);
    $group->post('',              [CategoryController::class, 'store']);
    $group->get('/{id}',          [CategoryController::class, 'viewDetails']);
    $group->get('/{id}/edit',     [CategoryController::class, 'edit']);
    $group->post('/{id}/update',  [CategoryController::class, 'update']);
    $group->post('/{id}/delete',  [CategoryController::class, 'destroy']);
})->add(AdminMiddleware::class);

// Events: list is public, create / edit / delete are admin only.
$app->group('/events', function ($group) {
    $group->get('',               [EventController::class, 'index']);
    $group->post('',              [EventController::class, 'store'])->add(AdminMiddleware::class);
    $group->get('/create',        [EventController::class, 'create'])->add(AdminMiddleware::class);
    $group->get('/on-sale',       [HomeController::class,  'showOnSale']);
    $group->get('/category/{id}', [EventController::class, 'byCategory']);
    $group->get('/{id}',          [EventController::class, 'viewDetails']);
    $group->get('/{id}/edit',     [EventController::class, 'edit'])->add(AdminMiddleware::class);
    $group->post('/{id}/update',  [EventController::class, 'update'])->add(AdminMiddleware::class);
    $group->post('/{id}/delete',  [EventController::class, 'destroy'])->add(AdminMiddleware::class);
});

// Admin: manage venues.
$app->group('/venues', function ($group) {
    $group->get('',               [VenueController::class, 'index']);
    $group->get('/create',        [VenueController::class, 'create']);
    $group->post('',              [VenueController::class, 'store']);
    $group->get('/{id}',          [VenueController::class, 'viewDetails']);
    $group->get('/{id}/edit',     [VenueController::class, 'edit']);
    $group->post('/{id}/update',  [VenueController::class, 'update']);
    $group->post('/{id}/delete',  [VenueController::class, 'destroy']);
})->add(AdminMiddleware::class);

// Tickets: list is public, create / edit / delete are admin only.
$app->group('/tickets', function ($group) {
    $group->get('',               [TicketController::class, 'index']);
    $group->post('',              [TicketController::class, 'store'])->add(AdminMiddleware::class);
    $group->get('/create',        [TicketController::class, 'create'])->add(AdminMiddleware::class);
    $group->get('/event/{id}',    [TicketController::class, 'byEvent']);
    $group->get('/{id}',          [TicketController::class, 'viewDetails']);
    $group->get('/{id}/edit',     [TicketController::class, 'edit'])->add(AdminMiddleware::class);
    $group->post('/{id}/update',  [TicketController::class, 'update'])->add(AdminMiddleware::class);
    $group->post('/{id}/delete',  [TicketController::class, 'destroy'])->add(AdminMiddleware::class);
});

// Admin: manage orders.
$app->group('/orders', function ($group) {
    $group->post('',              [OrderController::class, 'store']);
    $group->get('/user/{id}',     [OrderController::class, 'byUser']);
    $group->get('/{id}',          [OrderController::class, 'viewDetails']);
    $group->post('/{id}',         [OrderController::class, 'update']);
    $group->post('/{id}/delete',  [OrderController::class, 'delete']);
})->add(AdminMiddleware::class);

// Admin: manage order items.
$app->group('/order-items', function ($group) {
    $group->post('',              [OrderItemController::class, 'store']);
    $group->get('/order/{id}',    [OrderItemController::class, 'byOrder']);
    $group->get('/{id}',          [OrderItemController::class, 'viewDetails']);
    $group->post('/{id}',         [OrderItemController::class, 'update']);
    $group->post('/{id}/delete',  [OrderItemController::class, 'delete']);
})->add(AdminMiddleware::class);

// JSON APIs used by the search bar and the map page sidebar.
$app->get('/api/search',      [EventController::class, 'searchJson']);
$app->get('/api/map-events',  [HomeController::class, 'mapEventsJson']);

// Cart: needs the user to be logged in.
$app->group('/cart', function ($group) {
    $group->post('/add',                [CartController::class, 'add']);
    $group->post('/remove/{ticket_id}', [CartController::class, 'remove']);
    $group->post('/clear',              [CartController::class, 'clear']);
    $group->post('/expire',             [CartController::class, 'expire']);
})->add(AuthMiddleware::class);

// Checkout: form is auth-only, the success and cancel pages are reachable from Stripe.
$app->group('/checkout', function ($group) {
    $group->get('',         [CartController::class, 'showCheckout'])->add(AuthMiddleware::class);
    $group->post('',        [CartController::class, 'checkout'])->add(AuthMiddleware::class);
    $group->get('/success', [CartController::class, 'checkoutSuccess']);
    $group->get('/cancel',  [CartController::class, 'checkoutCancel']);
});

// Stripe webhook: skipped by CsrfMiddleware. The signature header proves it really came from Stripe.
$app->post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Language switcher: flips the session locale and sends the user back where they came from.
$app->get('/lang/{locale}', function (Request $request, Response $response, array $args) use ($basePath) {
    if (in_array($args['locale'], ['en', 'fr'], true)) {
        $_SESSION['lang'] = $args['locale'];
    }

    $referer = $request->getHeaderLine('Referer');
    $dest    = $basePath . '/';
    if ($referer) {
        $parts = parse_url($referer);
        $dest  = ($parts['path'] ?? '')
               . (isset($parts['query'])    ? '?' . $parts['query']    : '')
               . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
        if ($basePath && str_starts_with($dest, $basePath)) {
            $dest = substr($dest, strlen($basePath));
        }
        if (!$dest || $dest === '?' || $dest === '#') {
            $dest = '/';
        }
    }
    return $response->withHeader('Location', $basePath . $dest)->withStatus(302);
});

// Go.
$app->run();
