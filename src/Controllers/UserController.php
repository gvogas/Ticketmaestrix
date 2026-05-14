<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\OrderModel;
use App\Models\PointsHistoryModel;
use App\Models\TicketModel;
use App\Models\UserModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class UserController
{
    public function __construct(
        private Environment       $twig,
        private UserModel         $userModel,
        private TicketModel       $ticketModel,
        private OrderModel        $orderModel,
        private PointsHistoryModel $pointsHistoryModel,
        private string            $basePath,
    ) {}

    public function store(Request $request, Response $response): Response
    {
        $data = (array) ($request->getParsedBody() ?? []);

        $errors = [];
        if (empty($data['first_name'])) $errors['first_name'] = ['First name is required.'];
        if (empty($data['last_name']))  $errors['last_name']  = ['Last name is required.'];
        if (empty($data['email']))      $errors['email']      = ['Email is required.'];
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = ['Enter a valid email address.'];
        if (empty($data['password']))   $errors['password']   = ['Password is required.'];
        elseif (strlen($data['password']) < 8) $errors['password'] = ['Password must be at least 8 characters.'];

        if ($errors) {
            return $response
                ->withHeader('Location', $this->basePath . '/admin')
                ->withStatus(302);
        }

        $this->userModel->create([
            'first_name'   => $data['first_name'] ?? '',
            'last_name'    => $data['last_name'] ?? '',
            'email'        => $data['email'] ?? '',
            'password'     => $data['password'] ?? '',
            'role'         => $data['role'] ?? 'user',
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.user_created'];
        return $response
            ->withHeader('Location', $this->basePath . '/admin#users')
            ->withStatus(302);
    }

    public function roleToggle(Request $request, Response $response, array $args): Response
    {
        $user = $this->userModel->load((int) ($args['id'] ?? 0));

        // Block an admin from toggling their own role and locking themselves out.
        if ($user->id && $user->id !== Auth::userId()) {
            $user->role = $user->role === 'admin' ? 'user' : 'admin';
            $this->userModel->save($user);
        }

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.role_updated'];
        return $response
            ->withHeader('Location', $this->basePath . '/admin#users')
            ->withStatus(302);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id   = (int) ($args['id'] ?? 0);
        $data = $request->getParsedBody();
        $this->userModel->update($id, $data);

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.user_updated'];
        return $response
            ->withHeader('Location', $this->basePath . '/admin#users')
            ->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $userId = (int) ($args['id'] ?? 0);

        // Block an admin from deleting themselves.
        if ($userId === Auth::userId()) {
            return $response->withHeader('Location', $this->basePath . '/admin#users')->withStatus(302);
        }

        $this->userModel->deleteById($userId);

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.user_deleted'];
        return $response->withHeader('Location', $this->basePath . '/admin#users')->withStatus(302);
    }

    public function viewDetails(Request $request, Response $response, array $args): Response
    {
        $user = $this->userModel->load((int) ($args['id'] ?? 0));

        if (!$user->id) {
            return $response
                ->withHeader('Location', $this->basePath . '/users')
                ->withStatus(302);
        }

        $html = $this->twig->render('user/user_detail.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'profile',
            'user'          => $user,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function index(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page    = max(1, (int) ($queryParams['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $users      = $this->userModel->findAllPaginated($perPage, $offset);
        $total      = $this->userModel->countAll();
        $totalPages = (int) ceil($total / $perPage);

        $html = $this->twig->render('user/index.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'admin',
            'users'         => $users,
            'total_users'   => $total,
            'current_page'  => $page,
            'total_pages'   => $totalPages,
            'query_params'  => $queryParams,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function showProfile(Request $request, Response $response): Response
    {
        $user = Auth::user();
        $id   = (int) $user->id;

        $queryParams = $request->getQueryParams();
        $page    = max(1, (int) ($queryParams['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $totalOrders = $this->orderModel->countByUser($id);
        $totalPages  = (int) ceil($totalOrders / $perPage);

        $html = $this->twig->render('user/profile.html.twig', [
            'base_path'       => $this->basePath,
            'current_route'   => 'profile',
            'user'            => $user,
            'tickets_count'   => $this->ticketModel->countByOrderItemsForUser($id),
            'total_spent'     => number_format($this->orderModel->totalSpentByUser($id), 2, '.', ''),
            'events_attended' => $this->orderModel->eventsAttendedByUser($id),
            'orders'          => $this->orderModel->findByUserWithItemsPaginated($id, $perPage, $offset),
            'points_history'  => $this->pointsHistoryModel->findByUser($id),
            'current_page'    => $page,
            'total_pages'     => $totalPages,
            'query_params'    => $queryParams,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function editProfile(Request $request, Response $response): Response
    {
        $html = $this->twig->render('user/edit_profile.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'profile',
            'user'          => Auth::user(),
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function updateProfile(Request $request, Response $response): Response
    {
        $id   = (int) Auth::userId();
        $data = (array) ($request->getParsedBody() ?? []);
        $form = (array) ($data['user'] ?? []);

        $name        = trim((string) ($form['name']     ?? ''));
        $email       = trim((string) ($form['email']    ?? ''));
        $birthday    = trim((string) ($form['birthday'] ?? ''));
        $location    = trim((string) ($form['location'] ?? ''));
        $bio         = trim((string) ($form['bio']      ?? ''));
        $newPassword = (string) ($form['password'] ?? '');

        $errors = [];

        $newAvatarPath = null;
        $avatarFile = ($request->getUploadedFiles()['avatar'] ?? null);
        if ($avatarFile !== null && $avatarFile->getError() !== UPLOAD_ERR_NO_FILE) {
            if ($avatarFile->getError() !== UPLOAD_ERR_OK) {
                $errors['avatar'] = ['Upload failed. Please try again.'];
            } else {
                $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $clientName   = $avatarFile->getClientFilename() ?? '';
                $ext          = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
                $stream       = $avatarFile->getStream();
                $tmpPath      = $stream->getMetadata('uri');
                $cleanupTmp   = false;
                if ($tmpPath === null) {
                    // In-memory uploads have no file path, so write to a temp file before checking the type.
                    $tmpPath    = tempnam(sys_get_temp_dir(), 'tm_avatar_');
                    file_put_contents($tmpPath, (string) $stream);
                    $cleanupTmp = true;
                }
                $imageInfo = @getimagesize($tmpPath);
                $mime      = $imageInfo ? $imageInfo['mime'] : '';
                if ($cleanupTmp) {
                    @unlink($tmpPath);
                }

                if (!in_array($ext, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
                    $errors['avatar'] = ['Only JPG, PNG, GIF, and WebP images are allowed.'];
                } elseif ($avatarFile->getSize() > 5 * 1024 * 1024) {
                    $errors['avatar'] = ['Image must be under 5 MB.'];
                } else {
                    $uploadDir = __DIR__ . '/../../uploads/avatars/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    // Random hex in the filename stops two uploads in the same second from overwriting each other.
                    $filename = $id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                    $oldAvatarPath = (string) (Auth::user()->avatar ?? '');
                    $avatarFile->moveTo($uploadDir . $filename);
                    // Force readable permissions. The default umask on some shared hosts leaves uploads at 0600.
                    @chmod($uploadDir . $filename, 0644);
                    $newAvatarPath = '/uploads/avatars/' . $filename;
                }
            }
        }

        if ($name === '') {
            $errors['name'] = ['Full name is required.'];
        }
        if ($email === '') {
            $errors['email'] = ['Email is required.'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Enter a valid email address.'];
        } else {
            $existing = $this->userModel->findByEmail($email);
            if ($existing !== null && $existing->id && (int) $existing->id !== $id) {
                $errors['email'] = ['This email is already in use by another account.'];
            }
        }
        if ($newPassword !== '' && strlen($newPassword) < 8) {
            $errors['password'] = ['New password must be at least 8 characters.'];
        }

        if ($errors) {
            $html = $this->twig->render('user/edit_profile.html.twig', [
                'base_path'     => $this->basePath,
                'current_route' => 'profile',
                'user'          => Auth::user(),
                'errors'        => $errors,
                'input'         => $form,
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(422);
        }

        $parts     = explode(' ', $name, 2);
        $firstName = $parts[0] ?? '';
        $lastName  = $parts[1] ?? '';

        $updateData = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'birthday'   => $birthday !== '' ? $birthday : null,
            'location'   => $location,
            'bio'        => $bio,
            'password'   => $newPassword !== '' ? $newPassword : null,
        ];
        if ($newAvatarPath !== null) {
            $updateData['avatar'] = $newAvatarPath;
        }
        $this->userModel->update($id, $updateData);

        // Only delete the old file after the new one is saved. If the save fails, the old file is still there.
        if ($newAvatarPath !== null && ($oldAvatarPath ?? '') !== '') {
            $old = __DIR__ . '/../../' . ltrim($oldAvatarPath, '/');
            if (file_exists($old)) {
                @unlink($old);
            }
        }

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.profile_updated'];
        return $response
            ->withHeader('Location', $this->basePath . '/profile')
            ->withStatus(302);
    }

    public function deleteAccount(Request $request, Response $response): Response
    {
        $userId = (int) Auth::userId();

        $this->userModel->deleteById($userId);

        Auth::logout();

        return $response
            ->withHeader('Location', $this->basePath . '/login')
            ->withStatus(302);
    }
}
