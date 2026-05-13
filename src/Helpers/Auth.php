<?php

declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Response;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

/**
 * Thin static facade over $_SESSION for authentication state.
 *
 * Stores only the user id in the session and lazily loads the bean on
 * demand. Caches the loaded bean for the duration of one request so
 * callers can hit Auth::user() repeatedly without extra DB hits.
 */
class Auth
{
    /** Per-request cache of the loaded user bean. */
    private static ?OODBBean $cachedUser = null;

    /** Mark a user as logged in by storing their id in the session. */
    public static function login(int $userId): void
    {
        // Regenerate the session id on login to prevent session fixation
        // — without this, an id that was set before authentication would
        // remain valid after, letting an attacker hijack a fresh session.
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userId;
        self::$cachedUser = null; // force reload on next user() call
    }

    /** Wipe all session data and the cookie, ending the login. */
    public static function logout(): void
    {
        self::clearRememberToken();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            // Use the options-array form so the samesite attribute survives the
            // expiry write — the legacy positional form silently drops it.
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

    // ──────────────────────────────────────────────
    //  Remember-me token (persistent cookie after 2FA)
    // ──────────────────────────────────────────────

    /**
     * Generate a random token, store a SHA-256 hash in the authtoken table,
     * and set a cookie valid for 2 hours.
     */
    public static function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $token);

        // Purge any existing tokens for this user so only one device is active.
        $old = R::find('authtoken', 'user_id = ?', [$userId]);
        R::trashAll($old);

        $bean = R::dispense('authtoken');
        $bean->user_id    = $userId;
        $bean->token_hash = $hash;
        $bean->expires_at = date('Y-m-d H:i:s', time() + 7200);
        R::store($bean);

        self::writeRememberCookie($token, time() + 7200);
    }

    /**
     * If no session exists but a valid auth_token cookie is present,
     * log the user in via the stored token silently.
     *
     * Should be called once per request, right after the database is ready
     * and before any auth-dependent code runs.
     */
    public static function checkRememberToken(): void
    {
        if (self::userId() !== null) {
            return; // already authenticated via session
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

        // Token is valid — restore the session.
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $bean->user_id;
        self::$cachedUser    = null;
    }

    /**
     * Delete the current auth_token from the database and expire the cookie.
     */
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

    // ──────────────────────────────────────────────
    //  2FA device-trust token (skip 2FA for same account on same browser)
    // ──────────────────────────────────────────────

    private const TFA_TRUST_COOKIE = 'tfa_token';
    private const TFA_TRUST_TTL    = 2592000; // 30 days

    /**
     * Generate a per-user 2FA trust token, store its hash in tfatoken,
     * and set a 30-day cookie. Future logins on this browser for the same
     * account will skip the 2FA step.
     */
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

    /**
     * Return true only if the tfa_token cookie is present, unexpired,
     * and belongs to the given user. Account-specific — will not bypass
     * 2FA if the user logs in with a different account on the same browser.
     */
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

        return BeanHelper::isValidBean($bean);
    }

    /**
     * Delete the tfa_token record from the database and expire the cookie.
     */
    public static function clear2faTrustToken(): void
    {
        $token = $_COOKIE[self::TFA_TRUST_COOKIE] ?? '';
        if ($token !== '') {
            $hash = hash('sha256', $token);
            $bean = R::findOne('tfatoken', 'token_hash = ?', [$hash]);
            if (BeanHelper::isValidBean($bean)) {
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

    /** Return the current user's id from session, or null when logged out. */
    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    /**
     * Load and cache the current user bean. Returns null when no user is
     * logged in or when the stored id no longer maps to a real row.
     */
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
            return null; // session pointed at a deleted user
        }

        self::$cachedUser = $bean;
        return $bean;
    }

    /** Convenience predicate used by templates and controllers. */
    public static function isLoggedIn(): bool
    {
        return self::user() !== null;
    }

    /** True only if a user is logged in AND has the admin role. */
    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u !== null && (string) $u->role === 'admin';
    }

    /**
     * Guard helper: returns a 302-to-/login response if not logged in,
     * otherwise null so the caller can continue.
     */
    public static function requireLogin(Response $response, string $basePath): ?Response
    {
        if (self::isLoggedIn()) {
            return null;
        }
        return $response
            ->withHeader('Location', $basePath . '/login')
            ->withStatus(302);
    }

    /**
     * Guard helper: returns a 302 response if not an admin, otherwise null.
     * Logged-out users go to /login; logged-in non-admins go to /.
     */
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
