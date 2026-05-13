<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Auth;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * AuthMiddleware
 *
 * Protects routes that require an authenticated user.
 * If the visitor is not logged in, returns a 302 redirect to /login
 * before the controller is invoked. Otherwise, hands off to the next handler.
 *
 */
class AuthMiddleware
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private string $basePath,
    ) {}

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Not logged in → short-circuit with a redirect; the controller never runs.
        if (!Auth::isLoggedIn()) {
            return $this->responseFactory
                ->createResponse(302)
                ->withHeader('Location', $this->basePath . '/login');
        }

        return $handler->handle($request);
    }
}
