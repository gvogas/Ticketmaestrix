<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CsrfMiddleware implements MiddlewareInterface
{
    private const PROTECTED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private array $skipPathSuffixes = [],
        private string $sessionKey = 'csrf_token',
    ) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        $method = strtoupper($request->getMethod());
        if (!in_array($method, self::PROTECTED_METHODS, true)) {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();
        foreach ($this->skipPathSuffixes as $suffix) {
            // Stripe calls /stripe/webhook from their server, so they cannot send our token.
            if ($suffix !== '' && str_ends_with($path, $suffix)) {
                return $handler->handle($request);
            }
        }

        $expected  = (string) ($_SESSION[$this->sessionKey] ?? '');
        $headerTok = $request->getHeaderLine('X-CSRF-Token');
        $bodyTok   = '';
        $parsed    = $request->getParsedBody();
        if (is_array($parsed) && isset($parsed['csrf_token'])) {
            $bodyTok = (string) $parsed['csrf_token'];
        }
        $submitted = $headerTok !== '' ? $headerTok : $bodyTok;

        // hash_equals stops timing attacks where the attacker measures how long the check takes.
        if ($expected === '' || $submitted === '' || !hash_equals($expected, $submitted)) {
            $response = $this->responseFactory->createResponse(403);
            $response->getBody()->write('Forbidden — CSRF token missing or invalid.');
            return $response->withHeader('Content-Type', 'text/plain; charset=utf-8');
        }

        return $handler->handle($request);
    }
}
