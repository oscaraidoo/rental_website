<?php
/**
 * String Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Str
{
    /** Generate a URL-friendly slug */
    public static function slug(string $text, string $separator = '-'): string
    {
        // Transliterate non-ASCII if intl extension is available, otherwise strip
        if (function_exists('transliterator_transliterate')) {
            $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text) ?? $text;
        } else {
            // Fallback: replace common accented characters manually
            $from = ['à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï',
                     'ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','À','Á','Â','Ã',
                     'Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô',
                     'Õ','Ö','Ù','Ú','Û','Ü','Ý','&'];
            $to   = ['a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i',
                     'n','o','o','o','o','o','u','u','u','u','y','y','A','A','A','A',
                     'A','A','AE','C','E','E','E','E','I','I','I','I','N','O','O','O',
                     'O','O','U','U','U','U','Y','and'];
            $text = str_replace($from, $to, $text);
        }
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', $separator, $text);
        return trim($text, $separator);
    }

    /** Generate a unique property reference like RW-A1B2C3 */
    public static function generateReference(string $prefix = 'RW', int $length = 6): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // avoid ambiguous I,1,O,0
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $prefix . '-' . $code;
    }

    /** Truncate text to a maximum length, appending ellipsis */
    public static function truncate(string $text, int $max = 150, string $ellipsis = '...'): string
    {
        if (mb_strlen($text) <= $max) return $text;
        return rtrim(mb_substr($text, 0, $max - mb_strlen($ellipsis))) . $ellipsis;
    }

    /** Format a number as currency */
    public static function currency(float $amount, string $symbol = 'GH₵'): string
    {
        return $symbol . number_format($amount, 2);
    }

    /** Convert bytes to human-readable size */
    public static function fileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /** Mask a string (e.g. for email display: j***@example.com) */
    public static function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $masked = substr($local, 0, 1) . str_repeat('*', max(strlen($local) - 1, 3));
        return $masked . '@' . $domain;
    }

    /** Format walking distance for display */
    public static function walkingTime(?int $minutes): string
    {
        if ($minutes === null) return 'Unknown';
        if ($minutes < 2) return 'Less than 2 mins walk';
        return $minutes . ' min' . ($minutes > 1 ? 's' : '') . ' walk';
    }

    /** Format a distance in km */
    public static function distance(?float $km): string
    {
        if ($km === null) return 'Unknown';
        if ($km < 1) return round($km * 1000) . ' m';
        return round($km, 1) . ' km';
    }

    /** Convert a SET column string to array */
    public static function setToArray(?string $set): array
    {
        if (empty($set)) return [];
        return array_filter(array_map('trim', explode(',', $set)));
    }

    /** Convert array to SET column string */
    public static function arrayToSet(array $arr): string
    {
        return implode(',', array_filter(array_map('trim', $arr)));
    }
}
