<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\UserModel;
use App\Services\OtpService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class AuthController
{
    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_SECONDS = 900;

    public function __construct(
        private Environment $twig,
        private UserModel $users,
        private OtpService $otpService,
        private string $basePath,
    ) {}

    public function showSignup(Request $request, Response $response): Response
    {
        return $this->render($response, 'auth/signup.html.twig');
    }

    public function showLogin(Request $request, Response $response): Response
    {
        return $this->render($response, 'auth/login.html.twig');
    }

    public function showForgotPassword(Request $request, Response $response): Response
    {
        return $this->render($response, 'auth/forgot_password_p1.html.twig');
    }

    public function showVerificationCode(Request $request, Response $response): Response
    {
        return $this->render($response, 'auth/forgot_password_p2.html.twig');
    }

    public function showNewPassword(Request $request, Response $response): Response
    {
        return $this->render($response, 'auth/forgot_password_p3.html.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        $lockedUntil = (int) ($_SESSION['login_lockout_until'] ?? 0);
        if ($lockedUntil > time()) {
            $remaining = (int) ceil(($lockedUntil - time()) / 60);
            $html = $this->twig->render('auth/login.html.twig', [
                'base_path' => $this->basePath,
                'error'     => "Too many failed attempts. Try again in {$remaining} minute(s).",
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(429);
        }

        $data     = (array) ($request->getParsedBody() ?? []);
        $email    = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $user     = $this->users->findByEmail($email);

        if ($user && $user->id && password_verify($password, $user->password)) {
            unset($_SESSION['login_attempts'], $_SESSION['login_lockout_until']);

            if (empty($user->totp_secret)) {
                $_SESSION['pending_user_id']            = (int) $user->id;
                $_SESSION['2fa_setup_pending_user_id']  = (int) $user->id;
                return $response->withHeader('Location', $this->basePath . '/2fa/setup')->withStatus(302);
            }

            // This browser already passed 2FA for this account — skip it.
            if (Auth::check2faTrust((int) $user->id)) {
                Auth::login((int) $user->id);
                Auth::setRememberToken((int) $user->id);
                return $response->withHeader('Location', $this->basePath . '/')->withStatus(302);
            }

            $_SESSION['pending_user_id'] = (int) $user->id;
            return $response
                ->withHeader('Location', $this->basePath . '/2fa/login')
                ->withStatus(302);
        }

        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

        if ($_SESSION['login_attempts'] >= self::MAX_ATTEMPTS) {
            $_SESSION['login_lockout_until'] = time() + self::LOCKOUT_SECONDS;
            unset($_SESSION['login_attempts']);
            $html = $this->twig->render('auth/login.html.twig', [
                'base_path' => $this->basePath,
                'error'     => 'Too many failed attempts. Try again in 15 minutes.',
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(429);
        }

        $attemptsLeft = self::MAX_ATTEMPTS - $_SESSION['login_attempts'];
        $html = $this->twig->render('auth/login.html.twig', [
            'base_path' => $this->basePath,
            'error'     => "Invalid email or password. {$attemptsLeft} attempt(s) remaining.",
            'input'     => $data,
        ]);
        $response->getBody()->write($html);
        return $response->withStatus(401);
    }

    public function signup(Request $request, Response $response): Response
    {
        $data     = (array) ($request->getParsedBody() ?? []);
        $errors   = [];
        $fullname = trim((string) ($data['fullname'] ?? ''));
        $email    = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $password2 = (string) ($data['password2'] ?? '');

        if (empty($fullname)) $errors['fullname'] = ['Full name is required.'];
        if (empty($email)) {
            $errors['email'] = ['Email is required.'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Enter a valid email address.'];
        } elseif ($this->users->findByEmail($email)) {
            $errors['email'] = ['This email is already registered.'];
        }
        if (empty($password)) {
            $errors['password'] = ['Password is required.'];
        } elseif (strlen($password) < 8) {
            $errors['password'] = ['Password must be at least 8 characters.'];
        }
        if (empty($password2)) {
            $errors['password2'] = ['Please confirm your password.'];
        } elseif ($password !== $password2) {
            $errors['password2'] = ['Passwords do not match.'];
        }

        if ($errors) {
            $html = $this->twig->render('auth/signup.html.twig', [
                'base_path' => $this->basePath,
                'errors'    => $errors,
                'input'     => $data,
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(422);
        }

        $parts     = explode(' ', $fullname, 2);
        $firstName = $parts[0] ?? '';
        $lastName  = $parts[1] ?? '';

        $_SESSION['signup_user_data'] = [
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'email'        => $email,
            'password'     => $password,
        ];

        return $response->withHeader('Location', $this->basePath . '/2fa/setup')->withStatus(302);
    }

public function show2faSetup(Request $request, Response $response): Response
    {
        $signupData = $_SESSION['signup_user_data'] ?? null;
        $pendingUserId = $_SESSION['2fa_setup_pending_user_id'] ?? null;

        if (!$signupData && !$pendingUserId) {
            return $response
                ->withHeader('Location', $this->basePath . '/signup')
                ->withStatus(302);
        }

        $cached = $_SESSION['2fa_setup_data'] ?? null;
        if (!$cached) {
            $email = $pendingUserId
                ? $this->users->load($pendingUserId)->email
                : $signupData['email'];

            $cached = $pendingUserId
                ? $this->otpService->generateForExisting($email)
                : $this->otpService->generate($email);

            $_SESSION['2fa_setup_data'] = $cached;
        }

        $error = $_SESSION['2fa_setup_error'] ?? null;
        unset($_SESSION['2fa_setup_error']);

        $html = $this->twig->render('auth/2fa_qr.html.twig', [
            'base_path' => $this->basePath,
            'qr_code'   => $cached['qr_code'],
            'secret'    => $cached['secret'],
            'error'     => $error,
        ]);

        $response->getBody()->write($html);
        return $response;
    }
    public function verify2faSetup(Request $request, Response $response): Response
    {
        $signupData    = $_SESSION['signup_user_data'] ?? null;
        $pendingUserId = $_SESSION['2fa_setup_pending_user_id'] ?? null;

        if (!$signupData && !$pendingUserId) {
            return $response
                ->withHeader('Location', $this->basePath . '/signup')
                ->withStatus(302);
        }

        // Session may have expired between QR display and code entry.
        $setupData = $_SESSION['2fa_setup_data'] ?? null;
        if (!$setupData || empty($setupData['secret'])) {
            // Existing user who just set up their first TOTP — log them in now
            Auth::login((int) $pendingUserId);
            Auth::setRememberToken((int) $pendingUserId);
            return $response
                ->withHeader('Location', $this->basePath . '/')
                ->withStatus(302);
        }

        $otp    = (string) ($request->getParsedBody()['otp'] ?? '');
        $secret = $setupData['secret'];

        if (!$this->otpService->verifyCode($secret, $otp)) {
            $_SESSION['2fa_setup_error'] = 'Invalid code. Please try again.';
            return $response
                ->withHeader('Location', $this->basePath . '/2fa/setup')
                ->withStatus(302);
        }

        // Code verified — now safe to persist.
        if ($pendingUserId) {
            // Existing user completing 2FA setup for the first time.
            $user = $this->users->load((int) $pendingUserId);
            if (!\App\Helpers\BeanHelper::isValidBean($user)) {
                // User was deleted between login and 2FA completion — restart.
                unset($_SESSION['2fa_setup_pending_user_id'], $_SESSION['2fa_setup_data']);
                return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
            }
            $user->totp_secret = $secret;
            $this->users->save($user);
            unset($_SESSION['2fa_setup_pending_user_id'], $_SESSION['2fa_setup_data']);
            return $response
                ->withHeader('Location', $this->basePath . '/2fa/login')
                ->withStatus(302);
        }

        // New signup — create the account now that setup is confirmed.
        $signupData['totp_secret'] = $secret;
        $newUser = $this->users->create($signupData);
        unset($_SESSION['signup_user_data'], $_SESSION['2fa_setup_data']);
        Auth::login((int) $newUser->id);
        Auth::setRememberToken((int) $newUser->id);
        Auth::set2faTrustToken((int) $newUser->id);

        return $response
            ->withHeader('Location', $this->basePath . '/')
            ->withStatus(302);
    }

    public function show2faLogin(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['pending_user_id'])) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        // Read and clear the error that verify2faLogin() may have written on the previous attempt.
        $error = $_SESSION['2fa_login_error'] ?? null;
        unset($_SESSION['2fa_login_error']);

        return $this->render($response, 'auth/2fa_login.html.twig', ['error' => $error]);
    }

    public function verify2faLogin(Request $request, Response $response): Response
    {
        $pendingId = $_SESSION['pending_user_id'] ?? null;
        if (!$pendingId) {
            return $response
                ->withHeader('Location', $this->basePath . '/login')
                ->withStatus(302);
        }

        $otp = (string) ($request->getParsedBody()['otp'] ?? '');

        if (!$this->otpService->verify((int) $pendingId, $otp)) {
            $_SESSION['2fa_login_error'] = 'Invalid code. Please try again.';
            return $response
                ->withHeader('Location', $this->basePath . '/2fa/login')
                ->withStatus(302);
        }

        Auth::login((int) $pendingId);
        Auth::setRememberToken((int) $pendingId);
        Auth::set2faTrustToken((int) $pendingId);
        unset($_SESSION['pending_user_id']);

        return $response
            ->withHeader('Location', $this->basePath . '/')
            ->withStatus(302);
    }

    public function logout(Request $request, Response $response): Response
    {
        Auth::logout();
        return $response->withHeader('Location', $this->basePath . '/')->withStatus(302);
    }

    /**
     * Internal helper to render templates with the base_path.
     */
    private function render(Response $response, string $template, array $data = []): Response
    {
        $data['base_path'] = $this->basePath;
        try {
            $html = $this->twig->render($template, $data);
            $response->getBody()->write($html);
        } catch (\Exception $e) {
            $response->getBody()->write("Template Error: " . $e->getMessage());
            return $response->withStatus(500);
        }
        return $response;
    }
}