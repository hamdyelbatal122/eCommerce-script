<?php
/**
 * PHPUnit Bootstrap File
 * Setup environment for testing
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');

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

// Register autoloader
Autoloader::register();

// Define testing flag
define('TESTING', true);
