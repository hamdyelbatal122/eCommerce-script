<?php

/**
 * Application Configuration
 * 
 * Core configuration for the eCommerce application
 */

return [
    'name' => getenv('APP_NAME') ?: 'ECommerce Marketplace',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => (bool) getenv('APP_DEBUG') ?: false,
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'timezone' => 'UTC',
    
    // Session Configuration
    'session' => [
        'timeout' => (int) getenv('SESSION_TIMEOUT') ?: 3600,
        'cookie_name' => 'ecommerce_session',
        'cookie_path' => '/',
        'cookie_domain' => '',
        'cookie_secure' => false,
        'cookie_http_only' => true,
    ],
    
    // Pagination
    'pagination' => [
        'per_page' => 12,
        'max_per_page' => 100,
    ],
    
    // File Upload
    'uploads' => [
        'max_size' => (int) getenv('MAX_UPLOAD_SIZE') ?: 5242880, // 5MB
        'allowed_extensions' => explode(',', getenv('ALLOWED_EXTENSIONS') ?: 'jpg,jpeg,png,gif'),
        'avatars_path' => 'public/uploads/avatars/',
        'items_path' => 'public/uploads/items/',
    ],
    
    // Security
    'security' => [
        'app_key' => getenv('APP_KEY') ?: 'change-me',
        'password_algorithm' => PASSWORD_ARGON2ID,
        'password_options' => ['memory_cost' => 19456, 'time_cost' => 4, 'threads' => 1],
    ],
    
    // Features
    'features' => [
        'require_email_verification' => false,
        'require_user_approval' => true,
        'allow_comments' => true,
        'allow_rating' => true,
    ],
];
