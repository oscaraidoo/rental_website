<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\{UserModel, RenterModel};
use App\Helpers\{Security, Request, Paginator};
use App\Services\AuditService;

class UserController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        $filters = [
            'role'   => Request::get('role', ''),
            'status' => Request::get('status', ''),
            'search' => Security::sanitize(Request::get('q', '')),
        ];

        $total  = UserModel::count($filters);
        $pager  = Paginator::fromRequest($total, 25);
        $users  = UserModel::all($pager->perPage, $pager->offset, $filters);

        $this->adminView('admin.users.index', compact('users', 'filters', 'pager', 'total') + [
            'title' => 'Manage Users', 'breadcrumb' => ['Users'],
        ]);
    }

    public function create(): void
    {
        $this->adminOnly();
        $this->adminView('admin.users.create', ['title' => 'Create User', 'breadcrumb' => ['Users', 'Create']]);
    }

    public function store(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $data = [
            'first_name' => Security::sanitize(Request::post('first_name', '')),
            'last_name'  => Security::sanitize(Request::post('last_name', '')),
            'email'      => Security::sanitizeEmail(Request::post('email', '')),
            'phone'      => Security::sanitize(Request::post('phone', '')),
            'password'   => Request::post('password', ''),
            'role'       => Security::sanitize(Request::post('role', 'renter')),
        ];

        if (empty($data['first_name']) || empty($data['email']) || empty($data['password'])) {
            $this->error('Required fields are missing.');
            $this->redirect('admin/users/create');
        }

        if (UserModel::existsByEmail($data['email'])) {
            $this->error('Email already exists.');
            $this->redirect('admin/users/create');
        }

        $roleId = UserModel::getRoleIdBySlug($data['role']);
        $userId = UserModel::create([
            'role_id'        => $roleId,
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'email'          => $data['email'],
            'phone'          => $data['phone'],
            'password'       => $data['password'],
            'email_verified' => 1,
            'status'         => 'active',
        ]);

        // Create the extended profile row, same as public registration does.
        // Without it the user can log in but their dashboard bounces them
        // into an infinite login/dashboard redirect loop.
        // Idempotent: re-creating a user whose email matches a soft-deleted
        // account resurrects the old row, which may already have a profile.
        if ($data['role'] === 'renter') {
            RenterModel::findOrCreateByUserId($userId);
        } elseif ($data['role'] === 'landlord') {
            // Landlord module was removed — keep legacy accounts working
            // (profile row must exist or their dashboard redirect loops).
            Database::query("INSERT IGNORE INTO landlords (user_id) VALUES (?)", [$userId]);
        }

        AuditService::log('create_user', 'users', $userId, 'user', [], $data, "Admin created user: {$data['email']}");

        $this->success("User {$data['first_name']} {$data['last_name']} created.");
        $this->redirect('admin/users');
    }

    public function edit(string $id): void
    {
        $this->adminOnly();
        $user = UserModel::findById((int) $id);
        if (!$user) $this->notFound();

        $this->adminView('admin.users.edit', compact('user') + [
            'title' => 'Edit User', 'breadcrumb' => ['Users', 'Edit'],
        ]);
    }

    public function update(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $user = UserModel::findById((int) $id);
        if (!$user) $this->notFound();

        $data = [
            'first_name' => Security::sanitize(Request::post('first_name', '')),
            'last_name'  => Security::sanitize(Request::post('last_name', '')),
            'phone'      => Security::sanitize(Request::post('phone', '')),
            'status'     => Security::sanitize(Request::post('status', 'active')),
        ];

        // Only super admin can change roles
        if ($_SESSION['user_role'] === 'super_admin') {
            $newRole = Request::post('role', 'renter');
            $roleId  = UserModel::getRoleIdBySlug($newRole);
            if ($roleId) $data['role_id'] = $roleId;

            // Role changed — make sure the matching profile row exists,
            // otherwise the user's dashboard becomes unreachable.
            if ($roleId && $roleId !== (int) $user['role_id']) {
                if ($newRole === 'renter') {
                    if (!RenterModel::findByUserId((int) $id)) RenterModel::create((int) $id);
                } elseif ($newRole === 'landlord') {
                    // Landlord module removed — legacy fallback keeps the
                    // existing landlords row present so old accounts still work.
                    Database::query("INSERT IGNORE INTO landlords (user_id) VALUES (?)", [(int) $id]);
                }
            }
        }

        UserModel::update((int) $id, $data);
        AuditService::log('update_user', 'users', (int) $id, 'user', $user, $data);

        $this->success('User updated successfully.');
        $this->redirect('admin/users');
    }

    public function suspend(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $user = UserModel::findById((int) $id);
        if (!$user) $this->notFound();

        // Prevent suspending own account
        if ((int) $id === $this->currentUserId()) {
            $this->error('You cannot suspend your own account.');
            $this->redirect('admin/users');
        }

        UserModel::update((int) $id, ['status' => 'suspended']);
        AuditService::log('suspend_user', 'users', (int) $id, 'user');
        $this->success('User account suspended.');
        $this->redirect('admin/users');
    }

    public function activate(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        UserModel::update((int) $id, ['status' => 'active']);
        AuditService::log('activate_user', 'users', (int) $id, 'user');
        $this->success('User account activated.');
        $this->redirect('admin/users');
    }

    public function destroy(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        // Prevent deleting own account
        if ((int) $id === $this->currentUserId()) {
            $this->error('You cannot delete your own account.');
            $this->redirect('admin/users');
        }

        $user = UserModel::findById((int) $id);
        if (!$user) $this->notFound();

        // Prevent deleting super admin accounts
        if ($user['role_slug'] === 'super_admin') {
            $this->error('Super admin accounts cannot be deleted.');
            $this->redirect('admin/users');
        }

        // Soft delete the user
        UserModel::softDelete((int) $id);
        AuditService::log('delete_user', 'users', (int) $id, 'user', $user, [], "Admin deleted user: {$user['email']}");
        $this->success("User \"{$user['first_name']} {$user['last_name']}\" has been deleted.");
        $this->redirect('admin/users');
    }
}
