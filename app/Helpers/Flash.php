<?php
/**
 * Flash Message Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Flash
{
    private const KEY = '_flash';

    /** Set a flash message */
    public static function set(string $type, string $message): void
    {
        $_SESSION[self::KEY][] = compact('type', 'message');
    }

    public static function success(string $message): void { self::set('success', $message); }
    public static function error(string $message): void   { self::set('danger',  $message); }
    public static function warning(string $message): void { self::set('warning', $message); }
    public static function info(string $message): void    { self::set('info',    $message); }

    /** Get all flash messages and clear them */
    public static function get(): array
    {
        $messages = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);
        return $messages;
    }

    /** Check if there are any flash messages */
    public static function has(): bool
    {
        return !empty($_SESSION[self::KEY]);
    }

    /** Render all flash messages as Bootstrap alerts */
    public static function render(): string
    {
        $messages = self::get();
        if (empty($messages)) return '';
        $html = '';
        foreach ($messages as $msg) {
            $type = htmlspecialchars($msg['type'], ENT_QUOTES, 'UTF-8');
            $text = htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8');
            $html .= <<<HTML
<div class="alert alert-{$type} alert-dismissible fade show" role="alert">
    {$text}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
HTML;
        }
        return $html;
    }
}
