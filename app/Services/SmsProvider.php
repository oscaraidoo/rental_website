<?php
/**
 * SMS Provider — configurable transport, isolated from business logic.
 *
 * Drivers (SMS_PROVIDER env / system setting):
 *   http    — generic HTTP gateway (POST JSON with bearer token; covers
 *             ArkeHub/Arkesel/Hubtel-style APIs via SMS_ENDPOINT + payload map)
 *   log     — append to storage/logs/sms.log (development / no gateway yet)
 *   (empty) — "log" in development, "log" otherwise until configured
 *
 * No credentials are ever logged. Provider code lives only here.
 */

declare(strict_types=1);

namespace App\Services;

class SmsProvider
{
    private const TIMEOUT = 15;

    /** Send an SMS. Returns [ok(bool), providerMessageId(?string), error(?string)]. */
    public static function send(string $to, string $message): array
    {
        $to = trim($to);
        if (!self::isValidE164($to)) {
            return [false, null, 'Invalid phone number format'];
        }
        if (trim($message) === '') {
            return [false, null, 'Empty message body'];
        }

        $cfg = self::config();

        try {
            return match ($cfg['provider']) {
                'http'  => self::sendViaHttp($cfg, $to, $message),
                default => self::sendViaLog($to, $message),
            };
        } catch (\Throwable $e) {
            return [false, null, 'SMS transport error: ' . mb_substr($e->getMessage(), 0, 200)];
        }
    }

    /** Current driver name (used for the outbound log). */
    public static function driverName(): string
    {
        return self::config()['provider'];
    }

    // ── Validation ───────────────────────────────────────────

    /** E.164-ish: + followed by 8..15 digits. */
    public static function isValidE164(string $phone): bool
    {
        return (bool) preg_match('/^\+[1-9]\d{7,14}$/', $phone);
    }

    /**
     * Normalise a Ghana-centric local number to E.164.
     * Accepts 0XXXXXXXXX, 233XXXXXXXXX, +233XXXXXXXXX, with spaces/dashes.
     */
    public static function normalizeToE164(string $phone, string $defaultCountryCode = '233'): ?string
    {
        $digits = preg_replace('/[\s\-\(\)\.]/', '', trim($phone));
        if ($digits === '' || !preg_match('/^\+?\d+$/', $digits)) {
            return null;
        }
        $digits = ltrim($digits, '+');

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = $defaultCountryCode . substr($digits, 1);
        } elseif (strlen($digits) <= 9) { // local without leading 0
            $digits = $defaultCountryCode . $digits;
        }

        $candidate = '+' . $digits;
        return self::isValidE164($candidate) ? $candidate : null;
    }

    // ── Config ───────────────────────────────────────────────

    private static function config(): array
    {
        $provider = trim((string) ($_ENV['SMS_PROVIDER'] ?? ''));
        if ($provider === '') {
            $provider = 'log';
        }
        return [
            'provider' => $provider,
            'endpoint' => (string) ($_ENV['SMS_ENDPOINT'] ?? ''),
            'apiKey'   => (string) ($_ENV['SMS_API_KEY'] ?? ''),
            'apiSecret'=> (string) ($_ENV['SMS_API_SECRET'] ?? ''),
            'from'     => (string) ($_ENV['SMS_FROM'] ?? 'RentalWebsite'),
        ];
    }

    // ── Drivers ──────────────────────────────────────────────

    /**
     * Generic JSON HTTP gateway. Expected env:
     *   SMS_PROVIDER=http
     *   SMS_ENDPOINT=https://api.example.com/v1/sms/send
     *   SMS_API_KEY=(bearer token)
     * Payload: { "from": SMS_FROM, "to": "+233...", "message": "..." }
     * Success: HTTP 2xx; provider_message_id from data.id|message_id|id.
     */
    private static function sendViaHttp(array $cfg, string $to, string $message): array
    {
        if ($cfg['endpoint'] === '' || $cfg['apiKey'] === '') {
            return [false, null, 'SMS gateway not configured (SMS_ENDPOINT/SMS_API_KEY missing)'];
        }

        $ch = curl_init($cfg['endpoint']);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'from'    => $cfg['from'],
                'to'      => $to,
                'message' => $message,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $cfg['apiKey'],
            ],
        ]);
        $body   = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            return [false, null, 'SMS gateway connection failed' . ($err ? ": {$err}" : '')];
        }
        if ($status < 200 || $status >= 300) {
            // Never echo the full provider body (may echo the key back); truncate defensively.
            return [false, null, "SMS gateway HTTP {$status}"];
        }

        $json = json_decode((string) $body, true);
        $id   = $json['data']['id'] ?? $json['message_id'] ?? $json['id'] ?? null;

        return [true, $id !== null ? (string) $id : null, null];
    }

    /** Development driver — writes to storage/logs/sms.log. */
    private static function sendViaLog(string $to, string $message): array
    {
        $dir = ROOT_PATH . '/storage/logs';
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        $ok = @file_put_contents(
            $dir . '/sms.log',
            sprintf("[%s] TO=%s MSG=%s\n", date('Y-m-d H:i:s'), $to, str_replace("\n", ' ', $message)),
            FILE_APPEND | LOCK_EX
        );
        return $ok !== false
            ? [true, 'log-' . bin2hex(random_bytes(6)), null]
            : [false, null, 'Could not write SMS log file'];
    }
}
