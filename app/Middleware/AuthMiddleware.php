<?php
/**
 * Authentication Middleware
 * Protects routes that require a logged-in user.
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Flash;
use App\Helpers\Url;

class AuthMiddleware
{
    /** Require any authenticated user */
    public static function require(): void
    {
        if (empty($_SESSION['user_id'])) {
            // Prevent redirect loop: only redirect if we're not already on the login page
            $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
            if (!str_contains($current, 'auth/login') && !str_contains($current, 'auth/register')) {
                Flash::warning('Please log in to continue.');
            }
            Url::redirect('auth/login');
        }
    }

    /** Require a specific role (or array of roles) */
    public static function requireRole(string|array $roles): void
    {
        self::require();
        $roles = (array) $roles;
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, $roles, true)) {
            // Wrong role — send them to their own dashboard instead of 403
            $correct = match ($userRole) {
                'super_admin', 'admin' => 'admin/dashboard',
                'renter'               => 'renter/dashboard',
                default                => '',
            };
            Url::redirect($correct ?: 'auth/login');
        }
    }

    /** Require admin role */
    public static function requireAdmin(): void
    {
        self::requireRole(['super_admin', 'admin']);
    }

    /** Require renter role */
    public static function requireRenter(): void
    {
        self::requireRole('renter');
    }

    /** Require that the user has verified their email */
    public static function requireEmailVerified(): void
    {
        self::require();
        if (empty($_SESSION['email_verified'])) {
            Flash::warning('Please verify your email address before continuing.');
            Url::redirect('auth/verify-email');
        }
    }

    /** Check if user is logged in (no redirect) */
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    /** Check if logged-in user has role */
    public static function hasRole(string $role): bool
    {
        return ($_SESSION['user_role'] ?? '') === $role;
    }

    /** Check if logged-in user is admin */
    public static function isAdmin(): bool
    {
        return in_array($_SESSION['user_role'] ?? '', ['super_admin', 'admin'], true);
    }

    /** Check if current user owns a resource */
    public static function ownsResource(int $ownerId): bool
    {
        return ((int) ($_SESSION['user_id'] ?? 0)) === $ownerId;
    }
}
