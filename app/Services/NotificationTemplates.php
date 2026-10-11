<?php
/**
 * Notification Templates — central registry of email/SMS notification types.
 *
 * Adding a new notification type = add ONE entry to TEMPLATES plus (optionally)
 * a PHP view in app/Views/emails/. No core service changes required.
 *
 * Each template defines:
 *   type        unique event identifier (used by callers & prefs UI)
 *   group       display group for the preferences page
 *   label       human name shown to users
 *   description what it covers (preferences page)
 *   subject     email subject ({{vars}} substituted)
 *   sms         SMS text ({{vars}} substituted; auto-truncated to 320 chars)
 *   email_view  optional PHP view in app/Views/emails/ (rich HTML); when absent
 *               a clean generic wrapper is used with `message`
 *   message     fallback/plain body ({{vars}} substituted)
 *   channels    default channels: any of ['email','sms']
 */

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Security;

class NotificationTemplates
{
    /** @var array<string, array> type id → definition */
    public const TEMPLATES = [
        'ACCOUNT_CREATED' => [
            'group'       => 'Account & security',
            'label'       => 'Account created',
            'description' => 'Confirmation when your account is registered',
            'subject'     => 'Welcome to RentalWebsite, {{userName}}',
            'email_view'  => 'welcome',
            'message'     => 'Hi {{userName}}, your RentalWebsite account has been created successfully.',
            'sms'         => 'Hi {{userName}}, your RentalWebsite account has been created. Welcome aboard!',
            'channels'    => ['email'],
        ],
        'PASSWORD_RESET' => [
            'group'       => 'Account & security',
            'label'       => 'Password reset',
            'description' => 'Password reset links and confirmations',
            'subject'     => 'Reset your password – RentalWebsite',
            'email_view'  => 'password_reset',
            'message'     => 'Hi {{userName}}, use this link to reset your password: {{link}} (expires in 60 minutes).',
            'sms'         => 'RentalWebsite password reset: {{link}} (expires in 60 min). Ignore if not you.',
            'channels'    => ['email', 'sms'],
        ],
        'SECURITY_ALERT' => [
            'group'       => 'Account & security',
            'label'       => 'Login & security alerts',
            'description' => 'New-device sign-ins and security notices',
            'subject'     => 'New sign-in to your account – RentalWebsite',
            'message'     => 'Hi {{userName}}, a new sign-in to your account was detected on {{device}} at {{time}}. If this was not you, reset your password immediately.',
            'sms'         => 'RentalWebsite: new sign-in on {{device}} at {{time}}. Not you? Reset your password now.',
            'channels'    => ['email', 'sms'],
        ],
        'VIEWING_REQUESTED' => [
            'group'       => 'Viewings',
            'label'       => 'Viewing requests',
            'description' => 'When a renter requests a viewing on your property',
            'subject'     => 'New viewing request for {{propertyTitle}}',
            'message'     => 'Hi {{userName}}, {{actorName}} requested a viewing for "{{propertyTitle}}" on {{date}} at {{time}}.',
            'sms'         => 'New viewing request for {{propertyTitle}} on {{date}} at {{time}}.',
            'channels'    => ['email', 'sms'],
        ],
        'VIEWING_CONFIRMED' => [
            'group'       => 'Viewings',
            'label'       => 'Viewing confirmations',
            'description' => 'When a viewing you requested is confirmed',
            'subject'     => 'Viewing confirmed – {{propertyTitle}}',
            'message'     => 'Hi {{userName}}, your viewing for "{{propertyTitle}}" is confirmed for {{date}} at {{time}}.',
            'sms'         => 'Viewing confirmed: {{propertyTitle}}, {{date}} at {{time}}.',
            'channels'    => ['email', 'sms'],
        ],
        'VIEWING_REMINDER' => [
            'group'       => 'Viewings',
            'label'       => 'Viewing reminders',
            'description' => 'Reminders before a scheduled viewing',
            'subject'     => 'Reminder: viewing tomorrow – {{propertyTitle}}',
            'message'     => 'Hi {{userName}}, a reminder that your viewing for "{{propertyTitle}}" is on {{date}} at {{time}}.',
            'sms'         => 'Reminder: viewing for {{propertyTitle}} on {{date}} at {{time}}.',
            'channels'    => ['email', 'sms'],
        ],
        'VIEWING_CANCELLED' => [
            'group'       => 'Viewings',
            'label'       => 'Viewing cancellations',
            'description' => 'When a viewing is cancelled',
            'subject'     => 'Viewing cancelled – {{propertyTitle}}',
            'message'     => 'Hi {{userName}}, the viewing for "{{propertyTitle}}" on {{date}} has been cancelled.{{reasonLine}}',
            'sms'         => 'Viewing cancelled: {{propertyTitle}}, {{date}}.{{reasonLine}}',
            'channels'    => ['email', 'sms'],
        ],
        'PAYMENT_SUCCESSFUL' => [
            'group'       => 'Payments',
            'label'       => 'Payment receipts',
            'description' => 'Successful payment confirmations',
            'subject'     => 'Payment received – {{purpose}}',
            'message'     => 'Hi {{userName}}, your payment of {{amount}} for {{purpose}} was received. Reference: {{reference}}.',
            'sms'         => 'Payment of {{amount}} for {{purpose}} received. Ref: {{reference}}.',
            'channels'    => ['email', 'sms'],
        ],
        'PAYMENT_FAILED' => [
            'group'       => 'Payments',
            'label'       => 'Payment failures',
            'description' => 'Failed payment alerts',
            'subject'     => 'Payment failed – {{purpose}}',
            'message'     => 'Hi {{userName}}, your payment of {{amount}} for {{purpose}} could not be processed. No money was taken.',
            'sms'         => 'Payment of {{amount}} for {{purpose}} FAILED. No money was taken. Please try again.',
            'channels'    => ['email', 'sms'],
        ],
        'PROPERTY_STATUS' => [
            'group'       => 'Properties',
            'label'       => 'Property status updates',
            'description' => 'Approval, rejection, verification and suspension of listings',
            'subject'     => 'Your listing "{{propertyTitle}}" was {{status}}',
            'message'     => 'Hi {{userName}}, your property "{{propertyTitle}}" has been {{status}}.{{reasonLine}}',
            'sms'         => 'Your listing {{propertyTitle}} was {{status}}.{{reasonLine}}',
            'channels'    => ['email'],
        ],
        'ADMIN_ANNOUNCEMENT' => [
            'group'       => 'Announcements',
            'label'       => 'Announcements',
            'description' => 'Important platform announcements from the team',
            'subject'     => '{{title}} – RentalWebsite',
            'message'     => 'Hi {{userName}}, {{message}}',
            'sms'         => 'RentalWebsite: {{message}}',
            'channels'    => ['email', 'sms'],
        ],
    ];

    /** Distinct preference groups (for the settings UI). */
    public static function groups(): array
    {
        $out = [];
        foreach (self::TEMPLATES as $def) {
            $out[$def['group']][] = true;
        }
        return array_keys($out);
    }

    /** True when a type id is known. */
    public static function has(string $type): bool
    {
        return isset(self::TEMPLATES[$type]);
    }

    /** Substitute {{var}} placeholders. Values are escaped for HTML when $html=true. */
    public static function render(string $text, array $data, bool $html = false): string
    {
        $replacements = [];
        foreach ($data as $k => $v) {
            $v = (string) $v;
            $replacements['{{' . $k . '}}'] = $html ? Security::e($v) : $v;
        }
        // Remove any unresolved placeholders (don't leak template internals).
        $out = strtr($text, $replacements);
        return preg_replace('/\{\{[a-zA-Z0-9_]+\}\}/', '', $out);
    }
}
