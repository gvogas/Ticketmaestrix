<?php

declare(strict_types=1);

namespace App\Models;

use RedBeanPHP\R;
use App\Helpers\BeanHelper;

class UserModel
{
    public function findAll(): array
    {
        return BeanHelper::castBeanArray(R::findAll('users'));
    }

    public function load(int $id): mixed
    {
        return R::load('users', $id);
    }

    public function findByEmail(string $email): mixed
    {
        return R::findOne('users', 'email = ?', [$email]);
    }

    public function create(array $data): mixed
    {
        $bean = R::dispense('users');
        $bean->first_name   = $data['first_name'];
        $bean->last_name    = $data['last_name'];
        $bean->email        = $data['email'];
        $bean->password     = password_hash($data['password'], PASSWORD_DEFAULT);
        $bean->role         = $data['role'] ?? 'user';
        $bean->totp_secret  = $data['totp_secret'] ?? null;
        $bean->points       = 0;
        R::store($bean);
        return BeanHelper::castBeanProperties($bean);
    }

    public function save(mixed $bean): void
    {
        R::store($bean);
    }

    public function deleteById(int $id): void
    {
        $user = R::load('users', $id);
        if (!BeanHelper::isValidBean($user)) {
            return;
        }

        $avatar = (string) ($user->avatar ?? '');
        if ($avatar !== '') {
            $file = __DIR__ . '/../../' . ltrim($avatar, '/');
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        // dont forget to wipe auth tokens too - they wont expire on thier own
        R::exec('DELETE FROM authtoken WHERE user_id = ?', [$id]);

        R::trash($user);
    }

    public function update(int $id, array $data): mixed
    {
        $user = R::load('users', $id);
        if (!BeanHelper::isValidBean($user)) {
            return null;
        }

        if (array_key_exists('first_name', $data)) {
            $user->first_name = (string) $data['first_name'];
        }
        if (array_key_exists('last_name', $data)) {
            $user->last_name = (string) $data['last_name'];
        }
        if (array_key_exists('email', $data)) {
            $user->email = (string) $data['email'];
        }
        if (array_key_exists('birthday', $data)) {
            $user->birthday = ($data['birthday'] !== null && $data['birthday'] !== '') ? (string) $data['birthday'] : null;
        }
        if (array_key_exists('location', $data)) {
            $user->location = (string) $data['location'];
        }
        if (array_key_exists('bio', $data)) {
            $user->bio = (string) $data['bio'];
        }
        if (array_key_exists('avatar', $data)) {
            $user->avatar = $data['avatar'] !== '' ? (string) $data['avatar'] : null;
        }
        if (!empty($data['password'])) {
            $user->password = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }

        R::store($user);
        return BeanHelper::castBeanProperties($user);
    }

    public function customerCount(): int
    {
        return (int) R::getCell(
            "SELECT COUNT(*) FROM users WHERE role = 'user'"
        );
    }
}