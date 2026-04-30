<?php

/**
 * ECommerce Marketplace - Entry Point
 * 
 * All requests are routed through this file.
 * Configure your web server to route all requests to this index.php
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic production-grade security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

// Define base path
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', __DIR__);

// Load environment configuration
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    foreach ($env as $key => $value) {
        putenv($key . '=' . $value);
    }
}

// Require autoloader
require_once BASE_PATH . '/core/Autoloader.php';

use ECommerce\Core\Autoloader;
use ECommerce\Core\Router;
use ECommerce\Core\Authenticator;

// Register autoloader
Autoloader::register();

// Create router instance
$router = new Router();

// Register middleware
$router->middleware('auth', function () {
    if (!Authenticator::isLoggedIn()) {
        header('Location: /login');
        exit;
    }
});

$router->middleware('admin', function () {
    if (!Authenticator::isAdmin()) {
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }
});

// ========================================
// PUBLIC ROUTES
// ========================================

// Home
$router->get('/', 'HomeController@index');
$router->get('/search', 'HomeController@search');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'HomeController@contact');
$router->get('/about-us', 'PolicyController@about');
$router->get('/privacy-policy', 'PolicyController@privacy');
$router->get('/terms-of-use', 'PolicyController@terms');

// Authentication
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');
$router->get('/password/forgot', 'PasswordController@requestForm');
$router->post('/password/forgot', 'PasswordController@sendResetLink');
$router->get('/password/reset/{token}', 'PasswordController@resetForm');
$router->post('/password/reset/{token}', 'PasswordController@reset');
$router->get('/auth/google/redirect', 'SocialAuthController@redirectGoogle');
$router->get('/auth/google/callback', 'SocialAuthController@callbackGoogle');

// Items
$router->get('/items/{id}', 'ItemController@show');
$router->get('/items/category/{id}', 'ItemController@category');
$router->get('/items/create', 'ItemController@createForm', ['auth']);
$router->post('/items/create', 'ItemController@create', ['auth']);

// User Profile
$router->get('/user/{id}', 'UserController@profile');
$router->get('/dashboard', 'UserController@dashboard', ['auth']);
$router->post('/dashboard/update', 'UserController@updateProfile', ['auth']);
$router->post('/dashboard/change-password', 'UserController@changePassword', ['auth']);

// Comments
$router->post('/comments/add', 'CommentController@add', ['auth']);
$router->post('/comments/{id}/delete', 'CommentController@delete', ['auth']);

// ========================================
// SHOPPING CART & ORDERS
// ========================================

// Shopping Cart
$router->get('/cart', 'CartController@index', ['auth']);
$router->post('/cart/add', 'CartController@add', ['auth']);
$router->post('/cart/update', 'CartController@update', ['auth']);
$router->post('/cart/remove', 'CartController@remove', ['auth']);
$router->post('/cart/clear', 'CartController@clear', ['auth']);
$router->get('/cart/count', 'CartController@count');

// Orders & Checkout
$router->get('/orders', 'OrderController@index', ['auth']);
$router->get('/orders/{id}', 'OrderController@show', ['auth']);
$router->get('/orders/{id}/track', 'ShippingController@track', ['auth']);
$router->get('/checkout', 'OrderController@checkout', ['auth']);
$router->post('/orders', 'OrderController@store', ['auth']);
$router->post('/orders/{id}/cancel', 'OrderController@cancel', ['auth']);
$router->get('/orders/stats', 'OrderController@stats', ['auth']);
$router->get('/payments/config', 'PaymentController@config', ['auth']);
$router->post('/payments/stripe/intent', 'PaymentController@createIntent', ['auth']);
$router->post('/payments/stripe/webhook', 'PaymentController@webhook');
$router->get('/wishlist', 'WishlistController@index', ['auth']);
$router->post('/wishlist/add', 'WishlistController@add', ['auth']);
$router->post('/wishlist/remove', 'WishlistController@remove', ['auth']);
$router->get('/coupons/validate', 'CouponController@validate', ['auth']);

// ========================================
// RATINGS & REVIEWS
// ========================================

$router->get('/ratings/item/{item_id}', 'RatingController@index');
$router->post('/ratings/store', 'RatingController@store', ['auth']);
$router->post('/ratings/{id}/update', 'RatingController@update', ['auth']);
$router->post('/ratings/{id}/delete', 'RatingController@delete', ['auth']);
$router->post('/ratings/{id}/helpful', 'RatingController@helpful');

// ========================================
// ADVANCED SEARCH
// ========================================

$router->get('/advanced-search', 'SearchController@index');
$router->get('/search/autocomplete', 'SearchController@autocomplete');
$router->get('/search/filters', 'SearchController@filters');
$router->get('/search/trending', 'SearchController@trending');

// ========================================
// NOTIFICATIONS
// ========================================

$router->get('/notifications', 'NotificationController@index', ['auth']);
$router->get('/notifications/unread', 'NotificationController@unread', ['auth']);
$router->post('/notifications/read', 'NotificationController@read', ['auth']);
$router->post('/notifications/read-all', 'NotificationController@readAll', ['auth']);
$router->post('/notifications/delete', 'NotificationController@delete', ['auth']);
$router->post('/notifications/clear', 'NotificationController@clear', ['auth']);

// ========================================
// API TOKENS
// ========================================

$router->get('/api-tokens', 'ApiTokenController@index', ['auth']);
$router->post('/api-tokens/create', 'ApiTokenController@create', ['auth']);
$router->post('/api-tokens/revoke', 'ApiTokenController@revoke', ['auth']);
$router->get('/api-tokens/test', 'ApiTokenController@test', ['auth']);

// ========================================
// ADMIN ROUTES
// ========================================

// Admin Dashboard
$router->get('/admin', 'Admin\DashboardController@index', ['admin']);

// Admin Users
$router->get('/admin/users', 'Admin\UserController@index', ['admin']);
$router->get('/admin/users/{id}/edit', 'Admin\UserController@edit', ['admin']);
$router->post('/admin/users/{id}', 'Admin\UserController@update', ['admin']);
$router->post('/admin/users/{id}/approve', 'Admin\UserController@approve', ['admin']);
$router->post('/admin/users/{id}/delete', 'Admin\UserController@delete', ['admin']);

// Admin Items
$router->get('/admin/items', 'Admin\ItemController@index', ['admin']);
$router->get('/admin/items/{id}/edit', 'Admin\ItemController@edit', ['admin']);
$router->post('/admin/items/{id}', 'Admin\ItemController@update', ['admin']);
$router->post('/admin/items/{id}/approve', 'Admin\ItemController@approve', ['admin']);
$router->post('/admin/items/{id}/delete', 'Admin\ItemController@delete', ['admin']);

// Admin Comments
$router->get('/admin/comments', 'Admin\CommentController@index', ['admin']);
$router->get('/admin/comments/{id}/edit', 'Admin\CommentController@edit', ['admin']);
$router->post('/admin/comments/{id}', 'Admin\CommentController@update', ['admin']);
$router->post('/admin/comments/{id}/approve', 'Admin\CommentController@approve', ['admin']);
$router->post('/admin/comments/{id}/delete', 'Admin\CommentController@delete', ['admin']);

// Admin Categories
$router->get('/admin/categories', 'Admin\CategoryController@index', ['admin']);
$router->get('/admin/categories/create', 'Admin\CategoryController@createForm', ['admin']);
$router->post('/admin/categories', 'Admin\CategoryController@create', ['admin']);
$router->get('/admin/categories/{id}/edit', 'Admin\CategoryController@edit', ['admin']);
$router->post('/admin/categories/{id}', 'Admin\CategoryController@update', ['admin']);
$router->post('/admin/categories/{id}/delete', 'Admin\CategoryController@delete', ['admin']);

// Admin Orders
$router->get('/admin/orders', 'OrderController@adminList', ['admin']);
$router->post('/admin/orders/{id}/status', 'OrderController@updateStatus', ['admin']);

// Admin Ratings & Reviews
$router->get('/admin/ratings/pending', 'RatingController@adminPending', ['admin']);
$router->post('/admin/ratings/{id}/approve', 'RatingController@adminApprove', ['admin']);
$router->post('/admin/ratings/{id}/reject', 'RatingController@adminReject', ['admin']);

// Admin Activity Logs
$router->get('/admin/logs', 'Admin\LogController@index', ['admin']);
$router->get('/admin/logs/user/{user_id}', 'Admin\LogController@userActivity', ['admin']);
$router->post('/admin/logs/cleanup', 'Admin\LogController@cleanup', ['admin']);
$router->get('/admin/payments', 'PaymentController@adminIndex', ['admin']);
$router->post('/admin/payments/{id}/status', 'PaymentController@adminUpdateStatus', ['admin']);
$router->get('/admin/coupons', 'Admin\CouponController@index', ['admin']);
$router->post('/admin/coupons', 'Admin\CouponController@store', ['admin']);
$router->get('/admin/analytics/wishlist', 'Admin\AnalyticsController@wishlist', ['admin']);


// 404 Handler
$router->notFound(function () {
    http_response_code(404);
    echo 'Page not found';
});

// Dispatch request
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $router->dispatch($method, $path);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo 'Error: ' . ($e->getMessage() ?? 'Internal Server Error');
}
