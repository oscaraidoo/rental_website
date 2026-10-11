<?php
/**
 * Base Controller
 * All controllers extend this class.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\{Security, Flash, Url, Response, Request};
use App\Middleware\AuthMiddleware;

abstract class BaseController
{
    protected array $config;

    public function __construct()
    {
        $this->config = require ROOT_PATH . '/config/app.php';
    }

    // ── View Rendering ──────────────────────────────────────

    /**
     * Render a view file with data.
     * Views are located under app/Views/
     */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        // Make data available as variables
        extract($data, EXTR_SKIP);

        // Capture view content
        ob_start();
        $viewFile = ROOT_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: $view");
        }
        include $viewFile;
        $content = ob_get_clean();

        // Render with layout if specified
        if ($layout) {
            $layoutFile = ROOT_PATH . '/app/Views/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
                return;
            }
        }

        echo $content;
    }

    /** Render admin layout */
    protected function adminView(string $view, array $data = []): void
    {
        $this->view($view, $data, 'layouts/admin');
    }

    /** Render renter layout */
    protected function renterView(string $view, array $data = []): void
    {
        $this->view($view, $data, 'layouts/renter');
    }

    // ── Security shortcuts ───────────────────────────────────

    protected function requireCsrf(): void { Security::requireCsrf(); }
    protected function e(mixed $v): string { return Security::e($v); }

    // ── Auth shortcuts ───────────────────────────────────────

    protected function auth(): void       { AuthMiddleware::require(); }
    protected function adminOnly(): void  { AuthMiddleware::requireAdmin(); }
    protected function renterOnly(): void { AuthMiddleware::requireRenter(); }

    protected function currentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    protected function currentUserRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    // ── Response shortcuts ───────────────────────────────────

    protected function json(mixed $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function jsonSuccess(mixed $data = null, string $msg = 'Success'): never
    {
        Response::success($data, $msg);
    }

    protected function jsonError(string $msg, int $status = 400, mixed $errors = null): never
    {
        Response::error($msg, $status, $errors);
    }

    protected function redirect(string $url): never
    {
        Url::redirect($url);
    }

    protected function back(string $fallback = '/'): never
    {
        Url::back($fallback);
    }

    protected function notFound(): never
    {
        Response::notFound();
    }

    protected function forbidden(): never
    {
        Response::forbidden();
    }

    // ── Flash shortcuts ───────────────────────────────────────

    protected function success(string $msg): void { Flash::success($msg); }
    protected function error(string $msg): void   { Flash::error($msg); }
    protected function warning(string $msg): void { Flash::warning($msg); }
}
