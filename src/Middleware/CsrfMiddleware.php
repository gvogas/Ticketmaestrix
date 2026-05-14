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

        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        $postMaxBytes  = self::shorthandToBytes((string) ini_get('post_max_size'));
        $overflowed    = $postMaxBytes > 0 && $contentLength > $postMaxBytes;

        $expected  = (string) ($_SESSION[$this->sessionKey] ?? '');
        $headerTok = $request->getHeaderLine('X-CSRF-Token');
        $bodyTok   = '';
        $parsed    = $request->getParsedBody();
        if (is_array($parsed) && isset($parsed['csrf_token'])) {
            $bodyTok = (string) $parsed['csrf_token'];
        }
        $submitted = $headerTok !== '' ? $headerTok : $bodyTok;

        // hash_equals stops timing attacks where the attacker measures how long the check takes.
        $tokenInvalid = $expected === '' || $submitted === '' || !hash_equals($expected, $submitted);

        if (!$overflowed && !$tokenInvalid) {
            return $handler->handle($request);
        }

        if ($this->isBrowserForm($request)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'key'  => $overflowed ? 'flash.upload_too_large' : 'flash.csrf_expired',
            ];
            $response = $this->responseFactory->createResponse(303);
            return $response->withHeader('Location', $this->safeReferer($request));
        }

        $response = $this->responseFactory->createResponse(403);
        $response->getBody()->write('Forbidden — CSRF token missing or invalid.');
        return $response->withHeader('Content-Type', 'text/plain; charset=utf-8');
    }

    private function isBrowserForm(Request $request): bool
    {
        if (strcasecmp($request->getHeaderLine('X-Requested-With'), 'XMLHttpRequest') === 0) {
            return false;
        }
        $contentType = strtolower($request->getHeaderLine('Content-Type'));
        $isFormType  = str_starts_with($contentType, 'multipart/form-data')
                    || str_starts_with($contentType, 'application/x-www-form-urlencoded');
        if (!$isFormType) {
            return false;
        }
        $accept = strtolower($request->getHeaderLine('Accept'));
        return $accept === '' || str_contains($accept, 'text/html');
    }

    private function safeReferer(Request $request): string
    {
        $referer = $request->getHeaderLine('Referer');
        if ($referer === '') {
            return '/';
        }
        $parts = parse_url($referer);
        if ($parts === false || empty($parts['host'])) {
            return '/';
        }
        // Only honour same-host referers so a forged header can't bounce users off-site.
        if (strcasecmp($parts['host'], $request->getUri()->getHost()) !== 0) {
            return '/';
        }
        $target = $parts['path'] ?? '/';
        if (!empty($parts['query'])) {
            $target .= '?' . $parts['query'];
        }
        return $target;
    }

    private static function shorthandToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }
        $unit = strtolower($value[strlen($value) - 1]);
        $num  = (int) $value;
        return match ($unit) {
            'g'     => $num * 1024 * 1024 * 1024,
            'm'     => $num * 1024 * 1024,
            'k'     => $num * 1024,
            default => $num,
        };
    }
}
