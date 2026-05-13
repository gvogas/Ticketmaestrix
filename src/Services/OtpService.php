<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserModel;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

/**
 * OtpService
 *
 * Generates and verifies Time-based One-Time Passwords (TOTP).
 * Secrets are stored in the database (user table) so users only need to
 * scan the QR code once — subsequent logins go straight to code entry.
 *
 * Session structure used:
 *   $_SESSION['username'] — set by AuthController, used here to look up the secret.
 */
class OtpService
{
    private TwoFactorAuth $tfa;

    public function __construct(private UserModel $userModel)
    {
        $this->tfa = new TwoFactorAuth(new QRServerProvider(), $_ENV['APP_NAME'] ?? 'Ticketmaestrix');
    }

    /**
     * Returns true if the user already has a TOTP secret stored in the database.
     * Used by AuthController to decide whether to show the QR setup screen.
     */
    public function hasSecret(string $email): bool
    {
        $user = $this->userModel->findByEmail($email);
        return $user !== null && !empty($user->totp_secret);
    }

    /**
     * Generate a new TOTP secret and return the QR data URI and raw secret.
     */
    public function generate(string $label): array
    {
        $secret = $this->tfa->createSecret();
        return [
            'qr_code' => $this->tfa->getQRCodeImageAsDataUri($label, $secret),
            'secret'  => $secret,
        ];
    }

    /**
     * Generate a replacement TOTP secret for an existing user.
     */
    public function generateForExisting(string $label): array
    {
        $secret = $this->tfa->createSecret();
        return [
            'qr_code' => $this->tfa->getQRCodeImageAsDataUri($label, $secret),
            'secret'  => $secret,
        ];
    }
    /**
     * Verify a TOTP code against a raw secret (e.g. one held in session before being persisted).
     */
    public function verifyCode(string $secret, string $code): bool
    {
        return $this->tfa->verifyCode($secret, $code);
    }

    /**
     * Verify a user-supplied code against the secret stored in the database.
     */
    public function verify(int $userId, string $input): bool
    {
        $user = $this->userModel->load($userId);
        if (!$user || empty($user->totp_secret)) {
            return false;
        }

        return $this->tfa->verifyCode($user->totp_secret, $input);
    }

    /**
     * No-op: the secret lives in the database and is reused across logins.
     * Kept so AuthController doesn't need to change.
     */
    public function invalidate(): void {}
}
