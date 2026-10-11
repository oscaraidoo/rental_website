<?php
/**
 * Mailer — sends transactional email via SMTP, PHP mail(), or a log file.
 *
 * Configuration is resolved per key, first non-empty value wins:
 *   1. .env MAIL_* variables          (deployment-level config)
 *   2. system_settings, group 'email' (Admin → Settings → Email)
 *   3. Built-in defaults
 *
 * Drivers:
 *   smtp      — real SMTP client: implicit SSL (465) or STARTTLS (587), AUTH LOGIN
 *   mail      — PHP mail()
 *   log       — append the message to storage/logs/mail.log (no network)
 *   (empty)   — "log" in development, "mail" otherwise
 *
 * Safety net: in development, if a configured send (smtp/mail) fails, the
 * message is ALSO written to storage/logs/mail.log so verification /
 * password-reset links are never lost while SMTP is being configured.
 */

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;

class Mailer
{
    private const TIMEOUT = 15; // seconds, for connect + each read

    /** @var array<string,string>|null cached system_settings rows for group 'email' */
    private static ?array $dbSettings = null;

    // ── Public API ────────────────────────────────────────────

    public static function send(string $to, string $subject, string $html, string $text = ''): bool
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            self::logError("Invalid recipient address '{$to}'", $subject);
            return false;
        }

        if ($text === '') {
            $text = self::htmlToText($html);
        }

        $cfg = self::config();

        try {
            $ok = match ($cfg['driver']) {
                'smtp' => self::sendViaSmtp($cfg, $to, $subject, $html, $text),
                'log'  => self::sendViaLog($to, $subject, $text),
                default => self::sendViaPhpMail($cfg, $to, $subject, $html, $text),
            };
        } catch (\Throwable $e) {
            self::logError('Delivery failed: ' . $e->getMessage(), "{$subject} → {$to}");
            $ok = false;
        }

        // Development safety net: never lose a verification/reset link because
        // SMTP was misconfigured. Dump the message to the log so it is reachable.
        if (!$ok && $cfg['driver'] !== 'log' && self::isDevelopment()) {
            self::sendViaLog($to, $subject, $text);
            self::logError('Development fallback: message written to storage/logs/mail.log', "{$subject} → {$to}");
        }

        return $ok;
    }

    // ── Configuration ─────────────────────────────────────────

    private static function config(): array
    {
        $db = self::dbSettings();

        $driver = self::pick(
            $_ENV['MAIL_DRIVER']     ?? null,
            $db['mail_driver']       ?? null
        );
        $driver = strtolower($driver);
        if ($driver === 'sendmail') $driver = 'mail';
        if ($driver === '') {
            $driver = self::isDevelopment() ? 'log' : 'mail';
        }

        $encryption = strtolower(self::pick(
            $_ENV['MAIL_ENCRYPTION'] ?? null,
            $db['mail_encryption']   ?? null
        ));
        if (!in_array($encryption, ['tls', 'ssl'], true)) {
            $encryption = '';
        }

        $port = (int) self::pick(
            $_ENV['MAIL_PORT']    ?? null,
            $db['mail_port']      ?? null,
            (string) match ($encryption) { 'ssl' => 465, 'tls' => 587, default => 25 }
        );

        return [
            'driver'       => $driver,
            'host'         => self::pick($_ENV['MAIL_HOST']      ?? null, $db['mail_host']      ?? null),
            'port'         => $port,
            'username'     => self::pick($_ENV['MAIL_USERNAME']  ?? null, $db['mail_username']  ?? null),
            'password'     => self::pick($_ENV['MAIL_PASSWORD']  ?? null, $db['mail_password']  ?? null),
            'encryption'   => $encryption,   // '' (none) | tls (STARTTLS) | ssl (implicit)
            'from_address' => self::pick(
                $_ENV['MAIL_FROM_ADDRESS'] ?? null,
                $db['mail_from_address']   ?? null,
                'noreply@rentalwebsite.com'
            ),
            'from_name'    => self::pick(
                $_ENV['MAIL_FROM_NAME'] ?? null,
                $db['mail_from_name']   ?? null,
                'RentalWebsite'
            ),
        ];
    }

    /** First non-empty string among the candidates. */
    private static function pick(?string ...$candidates): string
    {
        foreach ($candidates as $candidate) {
            if ($candidate !== null && trim($candidate) !== '') {
                return trim($candidate);
            }
        }
        return '';
    }

    private static function dbSettings(): array
    {
        if (self::$dbSettings === null) {
            try {
                $rows = Database::fetchAll(
                    "SELECT `key`, `value` FROM system_settings WHERE `group` = 'email'"
                );
                self::$dbSettings = array_column($rows, 'value', 'key');
            } catch (\Throwable) {
                self::$dbSettings = []; // settings table missing — .env only
            }
        }
        return self::$dbSettings;
    }

    private static function isDevelopment(): bool
    {
        return strtolower($_ENV['APP_ENV'] ?? 'development') === 'development';
    }

    // ── Driver: SMTP ──────────────────────────────────────────

    private static function sendViaSmtp(array $cfg, string $to, string $subject, string $html, string $text): bool
    {
        if ($cfg['host'] === '') {
            throw new \RuntimeException('SMTP host is not configured (MAIL_HOST or Admin → Settings → Email).');
        }

        $remote = ($cfg['encryption'] === 'ssl' ? 'ssl://' : 'tcp://')
                . $cfg['host'] . ':' . $cfg['port'];

        $errno  = 0;
        $errstr = '';
        $stream = @stream_socket_client($remote, $errno, $errstr, self::TIMEOUT);
        if (!$stream) {
            throw new \RuntimeException("Could not connect to {$cfg['host']}:{$cfg['port']} — {$errstr} ({$errno})");
        }
        stream_set_timeout($stream, self::TIMEOUT);

        try {
            $hostname = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';

            self::smtpRead($stream, [220], 'server greeting');
            self::smtpExchange($stream, 'EHLO ' . $hostname, [250], 'EHLO');

            if ($cfg['encryption'] === 'tls') {
                self::smtpExchange($stream, 'STARTTLS', [220], 'STARTTLS');
                if (!@stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('STARTTLS negotiation failed.');
                }
                self::smtpExchange($stream, 'EHLO ' . $hostname, [250], 'EHLO after TLS');
            }

            if ($cfg['username'] !== '') {
                self::smtpExchange($stream, 'AUTH LOGIN', [334], 'AUTH LOGIN');
                self::smtpExchange($stream, base64_encode($cfg['username']), [334], 'SMTP username');
                self::smtpExchange($stream, base64_encode($cfg['password']), [235], 'SMTP authentication');
            }

            self::smtpExchange($stream, 'MAIL FROM:<' . $cfg['from_address'] . '>', [250], 'MAIL FROM');
            self::smtpExchange($stream, 'RCPT TO:<' . $to . '>', [250, 251], 'RCPT TO');
            self::smtpExchange($stream, 'DATA', [354], 'DATA');

            // Dot-stuffing: RFC 5321 requires a leading dot to be doubled.
            $message = preg_replace('/^\./m', '..', self::buildMessage($cfg, $to, $subject, $html, $text));

            if (@fwrite($stream, $message . "\r\n.\r\n") === false) {
                throw new \RuntimeException('Failed writing message to SMTP socket.');
            }
            self::smtpRead($stream, [250], 'message acceptance');

            @fwrite($stream, "QUIT\r\n");
        } finally {
            @fclose($stream);
        }

        return true;
    }

    /** Send a command, read the reply, verify the expected status code. */
    private static function smtpExchange($stream, string $command, array $expectedCodes, string $stage): array
    {
        if (@fwrite($stream, $command . "\r\n") === false) {
            throw new \RuntimeException("SMTP connection lost while sending {$stage}.");
        }
        return self::smtpRead($stream, $expectedCodes, $stage);
    }

    /**
     * Read a (possibly multiline) SMTP reply and verify its final status code.
     * Returns the reply lines. Throws on unexpected codes / timeouts.
     */
    private static function smtpRead($stream, array $expectedCodes, string $stage): array
    {
        $lines    = [];
        $deadline = microtime(true) + self::TIMEOUT;

        do {
            $line = fgets($stream, 1024);
            if ($line === false || $line === '') {
                $meta = stream_get_meta_data($stream);
                $why  = !empty($meta['timed_out']) ? ' (timed out)' : ' (connection closed)';
                throw new \RuntimeException("SMTP {$stage} failed{$why}.");
            }
            $lines[] = trim($line);
        } while (isset($line[3]) && $line[3] === '-' && microtime(true) < $deadline);

        $last   = end($lines);
        $code   = (int) substr($last, 0, 3);
        $detail = substr($last, 4);

        if (!in_array($code, $expectedCodes, true)) {
            throw new \RuntimeException("SMTP {$stage} rejected: [{$code}] {$detail}");
        }

        return $lines;
    }

    // ── Driver: PHP mail() ────────────────────────────────────

    private static function sendViaPhpMail(array $cfg, string $to, string $subject, string $html, string $text): bool
    {
        $message = self::buildMessage($cfg, $to, $subject, $html, $text);

        // mail() takes To and Subject separately; the rest are headers.
        $headerEnd = strpos($message, "\r\n\r\n");
        if ($headerEnd === false) {
            throw new \RuntimeException('Internal error: malformed message.');
        }
        $headers = substr($message, 0, $headerEnd);
        $body    = substr($message, $headerEnd + 4);

        $params = '';
        if (PHP_OS_FAMILY !== 'Windows') {
            $params = '-f' . $cfg['from_address']; // set envelope sender on sendmail-based systems
        }

        if (!@mail($to, self::encodeHeader($subject), $body, $headers, $params)) {
            throw new \RuntimeException('PHP mail() rejected the message (is a local mail transport configured?).');
        }
        return true;
    }

    // ── Driver: log file (development) ────────────────────────

    private static function sendViaLog(string $to, string $subject, string $text): bool
    {
        $log = ROOT_PATH . '/storage/logs/mail.log';
        $dir = dirname($log);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        $entry = sprintf(
            "[%s] TO: %s | SUBJECT: %s\n---\n%s\n===\n",
            date('Y-m-d H:i:s'), $to, $subject, $text
        );
        if (@file_put_contents($log, $entry, FILE_APPEND | LOCK_EX) === false) {
            error_log('[Mailer] Could not write to ' . $log);
            return false;
        }
        return true;
    }

    // ── Message construction ──────────────────────────────────

    private static function buildMessage(array $cfg, string $to, string $subject, string $html, string $text): string
    {
        $boundary = '=_rw_' . bin2hex(random_bytes(16));
        $domain   = substr(strrchr($cfg['from_address'], '@'), 1) ?: 'localhost';

        $headers =
            'Date: ' . date('r') . "\r\n" .
            'From: ' . self::encodeHeader($cfg['from_name']) . ' <' . $cfg['from_address'] . ">\r\n" .
            'Reply-To: ' . $cfg['from_address'] . "\r\n" .
            'To: ' . $to . "\r\n" .
            'Subject: ' . self::encodeHeader($subject) . "\r\n" .
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $domain . ">\r\n" .
            "MIME-Version: 1.0\r\n" .
            'Content-Type: multipart/alternative; boundary="' . $boundary . "\"\r\n" .
            'X-Mailer: RentalWebsite Mailer (PHP/' . PHP_VERSION . ")\r\n";

        $body =
            '--' . $boundary . "\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: quoted-printable\r\n\r\n" .
            quoted_printable_encode($text) . "\r\n" .
            '--' . $boundary . "\r\n" .
            "Content-Type: text/html; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: quoted-printable\r\n\r\n" .
            quoted_printable_encode($html) . "\r\n" .
            '--' . $boundary . "--\r\n";

        return $headers . "\r\n" . $body;
    }

    /** RFC 2047 encode a header value; neutralises header injection. */
    private static function encodeHeader(string $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', $value);
        if (!preg_match('/[\x80-\xFF]/', $value)) {
            return $value; // plain ASCII needs no encoding
        }
        if (function_exists('mb_encode_mimeheader')) {
            return mb_encode_mimeheader($value, 'UTF-8', 'B', "\r\n");
        }
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    /** Derive a readable plain-text version of the HTML email. Links become "text <url>". */
    private static function htmlToText(string $html): string
    {
        $text = preg_replace('/<(script|style)[\s\S]*?<\/\1>/i', '', $html) ?? $html;
        $text = preg_replace_callback(
            '/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>([\s\S]*?)<\/a>/i',
            fn($m) => trim(strip_tags($m[2])) . ' <' . $m[1] . '>',
            $text
        ) ?? $text;
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text) ?? $text;
        $text = preg_replace('/<\/(p|div|tr|h[1-6]|li)>/i', "\n\n", $text) ?? $text;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", trim($text)) ?? $text;

        return $text;
    }

    // ── Failure logging ───────────────────────────────────────

    private static function logError(string $message, string $context): void
    {
        $line = sprintf('[%s] %s | %s', date('Y-m-d H:i:s'), $message, $context);

        $log = ROOT_PATH . '/storage/logs/mail_error.log';
        $dir = dirname($log);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents($log, $line . "\n", FILE_APPEND | LOCK_EX);

        error_log('[Mailer] ' . $line);
    }
}
