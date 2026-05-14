<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserModel;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

class OtpService
{
    private TwoFactorAuth $tfa;

    public function __construct(private UserModel $userModel)
    {
        $this->tfa = new TwoFactorAuth(new QRServerProvider(), $_ENV['APP_NAME'] ?? 'Ticketmaestrix');
    }

    public function hasSecret(string $email): bool
    {
        $user = $this->userModel->findByEmail($email);
        return $user !== null && !empty($user->totp_secret);
    }

    public function generate(string $label): array
    {
        $secret = $this->tfa->createSecret();
        return [
            'qr_code' => $this->tfa->getQRCodeImageAsDataUri($label, $secret),
            'secret'  => $secret,
        ];
    }

    public function generateForExisting(string $label): array
    {
        $secret = $this->tfa->createSecret();
        return [
            'qr_code' => $this->tfa->getQRCodeImageAsDataUri($label, $secret),
            'secret'  => $secret,
        ];
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->tfa->verifyCode($secret, $code);
    }

    public function verify(int $userId, string $input): bool
    {
        $user = $this->userModel->load($userId);
        if (!$user || empty($user->totp_secret)) {
            return false;
        }

        return $this->tfa->verifyCode($user->totp_secret, $input);
    }

    // Kept as a no-op. The secret lives in the database, so there is nothing to clear here.
    public function invalidate(): void {}
}
