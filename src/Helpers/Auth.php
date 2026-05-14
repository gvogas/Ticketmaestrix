<?php

declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Response;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class Auth
{
    private static ?OODBBean $cachedUser = null;

    public static function login(int $userId): void
    {
        // New session id stops a stolen old cookie from being used.
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userId;
        self::$cachedUser = null;
    }

    public static function logout(): void
    {
        self::clearRememberToken();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
        self::$cachedUser = null;
    }

    public static function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        // Store the hashed token, not the real one. If the database leaks, real cookies still don't work.
        $hash  = hash('sha256', $token);

        $bean = R::dispense('authtoken');
        $bean->user_id    = $userId;
        $bean->token_hash = $hash;
        $bean->expires_at = date('Y-m-d H:i:s', time() + 7200);
        R::store($bean);

        self::writeRememberCookie($token, time() + 7200);
    }

    public static function checkRememberToken(): void
    {
        if (self::userId() !== null) {
            return;
        }

        $token = $_COOKIE['auth_token'] ?? '';
        if ($token === '') {
            return;
        }

        $hash = hash('sha256', $token);
        $bean = R::findOne('authtoken', 'token_hash = ? AND expires_at > NOW()', [$hash]);

        if (!BeanHelper::isValidBean($bean)) {
            self::expireRememberCookie();
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $bean->user_id;
        self::$cachedUser    = null;
    }

    public static function clearRememberToken(): void
    {
        $token = $_COOKIE['auth_token'] ?? '';
        if ($token !== '') {
            $hash = hash('sha256', $token);
            $bean = R::findOne('authtoken', 'token_hash = ?', [$hash]);
            if (BeanHelper::isValidBean($bean)) {
                R::trash($bean);
            }
        }
        self::expireRememberCookie();
    }

    private const TFA_TRUST_COOKIE = 'tfa_token';
    // 30 days.
    private const TFA_TRUST_TTL    = 2592000;

    public static function set2faTrustToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $token);

        $old = R::find('tfatoken', 'user_id = ?', [$userId]);
        R::trashAll($old);

        $bean = R::dispense('tfatoken');
        $bean->user_id    = $userId;
        $bean->token_hash = $hash;
        $bean->expires_at = date('Y-m-d H:i:s', time() + self::TFA_TRUST_TTL);
        R::store($bean);

        setcookie(self::TFA_TRUST_COOKIE, $token, [
            'expires'  => time() + self::TFA_TRUST_TTL,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }

    public static function check2faTrust(int $userId): bool
    {
        $token = $_COOKIE[self::TFA_TRUST_COOKIE] ?? '';
        if ($token === '') {
            return false;
        }

        $hash = hash('sha256', $token);
        $bean = R::findOne(
            'tfatoken',
            'token_hash = ? AND user_id = ? AND expires_at > NOW()',
            [$hash, $userId]
        );

        return $bean !== null && BeanHelper::isValidBean($bean);
    }

    public static function clear2faTrustToken(): void
    {
        $token = $_COOKIE[self::TFA_TRUST_COOKIE] ?? '';
        if ($token !== '') {
            $hash = hash('sha256', $token);
            $bean = R::findOne('tfatoken', 'token_hash = ?', [$hash]);
            if ($bean !== null && BeanHelper::isValidBean($bean)) {
                R::trash($bean);
            }
        }
        setcookie(self::TFA_TRUST_COOKIE, '', [
            'expires'  => time() - 42000,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }

    private static function writeRememberCookie(string $token, int $expires): void
    {
        setcookie('auth_token', $token, [
            'expires'  => $expires,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }

    private static function expireRememberCookie(): void
    {
        setcookie('auth_token', '', [
            'expires'  => time() - 42000,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function user(): ?OODBBean
    {
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $id = self::userId();
        if ($id === null) {
            return null;
        }

        $bean = R::load('users', $id);
        if (!BeanHelper::isValidBean($bean)) {
            return null;
        }

        self::$cachedUser = $bean;
        return $bean;
    }

    public static function isLoggedIn(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u !== null && (string) $u->role === 'admin';
    }

    public static function requireLogin(Response $response, string $basePath): ?Response
    {
        if (self::isLoggedIn()) {
            return null;
        }
        return $response
            ->withHeader('Location', $basePath . '/login')
            ->withStatus(302);
    }

    public static function requireAdmin(Response $response, string $basePath): ?Response
    {
        if (self::isAdmin()) {
            return null;
        }
        $target = self::isLoggedIn() ? '/' : '/login';
        return $response
            ->withHeader('Location', $basePath . $target)
            ->withStatus(302);
    }
}
