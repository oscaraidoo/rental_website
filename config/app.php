<?php
/**
 * Application Configuration
 */

return [
    'name'          => $_ENV['APP_NAME']    ?? 'RentalWebsite',
    'url'           => $_ENV['APP_URL']     ?? '',
    'env'           => $_ENV['APP_ENV']     ?? 'development',
    'debug'         => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'timezone'      => $_ENV['APP_TIMEZONE'] ?? 'Africa/Accra',
    'locale'        => 'en',
    'key'           => $_ENV['APP_KEY']     ?? 'base64:CHANGE_THIS_TO_RANDOM_32_BYTE_KEY=',

    // Storage paths (relative to project root)
    'upload_path'         => 'storage/uploads',
    'property_img_path'   => 'storage/uploads/properties/images',
    'property_vid_path'   => 'storage/uploads/properties/videos',
    'property_doc_path'   => 'storage/uploads/properties/documents',
    'avatar_path'         => 'storage/uploads/avatars',
    'agreement_path'      => 'storage/uploads/agreements',
    'export_path'         => 'storage/exports',
    'log_path'            => 'storage/logs',

    // Upload limits
    'max_image_size'      => 5 * 1024 * 1024,   // 5 MB
    'max_video_size'      => 100 * 1024 * 1024,  // 100 MB
    'max_doc_size'        => 10 * 1024 * 1024,   // 10 MB

    // Allowed MIME types
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    'allowed_video_types' => ['video/mp4', 'video/webm', 'video/ogg'],
    'allowed_doc_types'   => ['application/pdf', 'image/jpeg', 'image/png'],

    // Pagination
    'per_page'    => 12,
    'admin_per_page' => 25,

    // Session
    'session_name'     => 'rw_session',
    'session_lifetime' => 86400, // 24 hours

    // Google OAuth 2.0 (optional — set GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET
    // in .env to enable the "Continue with Google" buttons on login/register)
    'google_oauth' => [
        'client_id'     => $_ENV['GOOGLE_CLIENT_ID']     ?? '',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
        'redirect_uri'  => rtrim($_ENV['APP_URL'] ?? '', '/') . '/auth/google/callback',
    ],

    // Security
    'bcrypt_cost'         => 12,
    'csrf_token_name'     => '_csrf_token',
    'csrf_token_lifetime' => 3600,  // 1 hour

    // Reference prefixes
    'property_prefix'   => 'RW',
    'viewing_prefix'    => 'VW',
    'payment_prefix'    => 'PAY',
    'agreement_prefix'  => 'AGR',
    'verify_prefix'     => 'VER',
];
