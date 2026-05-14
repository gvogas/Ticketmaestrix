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

/**
 * User-facing profile pages and admin-only user management.
 */
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

    /** POST /users — create a new user (admin form). */
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

    /** POST /users/{id}/role — toggle admin/user role. Admin-only. */
    public function roleToggle(Request $request, Response $response, array $args): Response
    {
        $user = $this->userModel->load((int) ($args['id'] ?? 0));

        if ($user->id && $user->id !== Auth::userId()) {
            $user->role = $user->role === 'admin' ? 'user' : 'admin';
            $this->userModel->save($user);
        }

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.role_updated'];
        return $response
            ->withHeader('Location', $this->basePath . '/admin#users')
            ->withStatus(302);
    }

    /** POST /users/{id} — admin updates a user's details. */
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

    /** POST /users/{id}/delete — admin deletes a user. */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $userId = (int) ($args['id'] ?? 0);

        if ($userId === Auth::userId()) {
            return $response->withHeader('Location', $this->basePath . '/admin#users')->withStatus(302);
        }

        $this->userModel->deleteById($userId);

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.user_deleted'];
        return $response->withHeader('Location', $this->basePath . '/admin#users')->withStatus(302);
    }

    /** GET /users/{id} — admin views one user's detail page. */
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

    /** GET /users — admin-only listing of all users. */
    public function index(Request $request, Response $response): Response
    {
        $users = $this->userModel->findAll();

        $html = $this->twig->render('user/index.html.twig', [
            'base_path'     => $this->basePath,
            'current_route' => 'admin',
            'users'         => $users,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    /** GET /profile — the logged-in user's own profile page with stats. */
    public function showProfile(Request $request, Response $response): Response
    {
        $user = Auth::user();
        $id   = (int) $user->id;

        $html = $this->twig->render('user/profile.html.twig', [
            'base_path'       => $this->basePath,
            'current_route'   => 'profile',
            'user'            => $user,
            'tickets_count'   => $this->ticketModel->countByOrderItemsForUser($id),
            'total_spent'     => number_format($this->orderModel->totalSpentByUser($id), 2, '.', ''),
            'events_attended' => $this->orderModel->eventsAttendedByUser($id),
            // Orders + line items + event titles for the Purchase History card,
            // shaped as stdClass[] with embedded items[] (single SQL query).
            'orders'          => $this->orderModel->findByUserWithItems($id),
            'points_history'  => $this->pointsHistoryModel->findByUser($id),
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    /** GET /editprofile — show the form for editing the logged-in user. */
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

    /**
     * POST /editprofile — save the logged-in user's edits.
     */
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

        // Avatar upload
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
                    // Stream has no filesystem URI (e.g. in-memory); copy to a real temp file.
                    $tmpPath    = tempnam(sys_get_temp_dir(), 'tm_avatar_');
                    file_put_contents($tmpPath, (string) $stream);
                    $cleanupTmp = true;
                }
                // getimagesize() is used instead of finfo because the fileinfo
                // extension is not guaranteed to be available on all hosts.
                // It also actually parses the image data, which is stricter than
                // a pure MIME sniff and prevents disguised non-image uploads.
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
                    $filename = $id . '_' . time() . '.' . $ext;
                    $oldAvatarPath = (string) (Auth::user()->avatar ?? '');
                    $avatarFile->moveTo($uploadDir . $filename);
                    // Ensure the file is world-readable so the web server can serve it.
                    // PHP's move_uploaded_file inherits the process umask, which on many
                    // shared hosts leaves files at 0600 (owner-only), blocking Apache.
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

        // Safe to remove old avatar now that the DB row is committed.
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

    /** POST /delete-account — user deletes their own account and is logged out. */
    public function deleteAccount(Request $request, Response $response): Response
    {
        $userId = (int) Auth::userId();

        // deleteById handles avatar file removal and auth token revocation.
        $this->userModel->deleteById($userId);

        Auth::logout();

        return $response
            ->withHeader('Location', $this->basePath . '/login')
            ->withStatus(302);
    }
}
