<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Auth;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private string $basePath,
    ) {}

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if (!Auth::isLoggedIn()) {
            return $this->responseFactory
                ->createResponse(302)
                ->withHeader('Location', $this->basePath . '/login');
        }

        return $handler->handle($request);
    }
}
