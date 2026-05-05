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
        $data     = $request->getParsedBody();
        $email    = (string) ($data['email'] ?? '');
        $password = (string) ($data['password'] ?? '');

        $user = $this->users->findByEmail($email);

        if ($user && $user->id && password_verify($password, $user->password)) {
            $_SESSION['pending_user_id'] = (int) $user->id;

            if (empty($user->totp_secret)) {
                $_SESSION['2fa_setup_pending_user_id'] = (int) $user->id;
                return $response->withHeader('Location', $this->basePath . '/2fa/setup')->withStatus(302);
            }

            return $response->withHeader('Location', $this->basePath . '/2fa/login')->withStatus(302);
        }

        $html = $this->twig->render('auth/login.html.twig', [
            'base_path' => $this->basePath,
            'error'     => 'Invalid email or password.',
        ]);
        $response->getBody()->write($html);
        return $response->withStatus(401);
    }

    public function signup(Request $request, Response $response): Response
    {
        $data      = $request->getParsedBody();
        $fullname  = trim((string) ($data['fullname'] ?? ''));
        $parts     = explode(' ', $fullname, 2);
        $firstName = $parts[0] ?? '';
        $lastName  = $parts[1] ?? '';
        $email     = (string) ($data['email'] ?? '');

        $_SESSION['signup_user_data'] = [
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'email'        => $email,
            'password'     => (string) ($data['password'] ?? ''),
        ];

        return $response->withHeader('Location', $this->basePath . '/2fa/setup')->withStatus(302);
    }

    public function show2faSetup(Request $request, Response $response): Response
    {
        $signupData = $_SESSION['signup_user_data'] ?? null;
        $pendingUserId = $_SESSION['2fa_setup_pending_user_id'] ?? null;

        if (!$signupData && !$pendingUserId) {
            return $response->withHeader('Location', $this->basePath . '/signup')->withStatus(302);
        }

        $cached = $_SESSION['2fa_setup_data'] ?? null;
        if ($cached) {
            $qrCode = $cached['qr_code'];
            $secret = $cached['secret'];
        } else {
            $userEmail = $signupData['email'] ?? '';
            if (!$userEmail && $pendingUserId) {
                $u = $this->users->load((int)$pendingUserId);
                $userEmail = $u->email ?? '';
            }

            $secret = $this->otpService->generateSecret();
            $qrCode = $this->otpService->getQrCode($userEmail, $secret);
            
            $_SESSION['2fa_setup_data'] = [
                'qr_code' => $qrCode,
                'secret'  => $secret
            ];
        }

        return $this->render($response, 'auth/2fa_setup.html.twig', [
            'qr_code' => $qrCode,
            'secret'  => $secret,
        ]);
    }

    public function verify2faSetup(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $code = preg_replace('/\s+/', '', (string) ($data['code'] ?? ''));

        $setupData = $_SESSION['2fa_setup_data'] ?? null;
        $signupData = $_SESSION['signup_user_data'] ?? null;
        $pendingUserId = $_SESSION['2fa_setup_pending_user_id'] ?? null;

        if ($setupData && $this->otpService->verifySecret($setupData['secret'], $code)) {
            if ($signupData) {
                $signupData['totp_secret'] = $setupData['secret'];
                $user = $this->users->create($signupData);
                Auth::login((int) $user->id);
                unset($_SESSION['signup_user_data'], $_SESSION['2fa_setup_data']);
                return $response->withHeader('Location', $this->basePath . '/')->withStatus(302);
            } elseif ($pendingUserId) {
                $user = $this->users->load((int)$pendingUserId);
                $user->totp_secret = $setupData['secret'];
                $this->users->save($user);
                Auth::login((int) $user->id);
                unset($_SESSION['2fa_setup_pending_user_id'], $_SESSION['2fa_setup_data'], $_SESSION['pending_user_id']);
                return $response->withHeader('Location', $this->basePath . '/')->withStatus(302);
            }
        }

        return $this->render($response, 'auth/2fa_setup.html.twig', [
            'qr_code' => $setupData['qr_code'] ?? '',
            'secret'  => $setupData['secret'] ?? '',
            'error'   => 'Invalid verification code. Please try again.'
        ]);
    }

    public function show2faLogin(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['pending_user_id'])) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }
        return $this->render($response, 'auth/2fa_login.html.twig');
    }

    public function verify2faLogin(Request $request, Response $response): Response
    {
        $userId = $_SESSION['pending_user_id'] ?? null;
        $code = preg_replace('/\s+/', '', (string) ($request->getParsedBody()['code'] ?? ''));

        if ($userId && $this->otpService->verify((int) $userId, $code)) {
            Auth::login((int) $userId);
            unset($_SESSION['pending_user_id']);
            return $response->withHeader('Location', $this->basePath . '/')->withStatus(302);
        }

        return $this->render($response, 'auth/2fa_login.html.twig', ['error' => 'Invalid code.']);
    }

    /**
     * POST /logout — Terminate session.
     */
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