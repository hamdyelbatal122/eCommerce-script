<?php

namespace ECommerce\Core;

/**
 * Autoloader Class
 * 
 * PSR-4 Autoloader for ECommerce application
 */
class Autoloader
{
    private static $namespaces = [
        'ECommerce\\' => __DIR__ . '/../',
    ];

    /**
     * Register autoloader
     * 
     * @return void
     */
    public static function register()
    {
        spl_autoload_register([self::class, 'load']);
    }

    /**
     * Load class file
     * 
     * @param string $class
     * @return void
     */
    public static function load($class)
    {
        foreach (self::$namespaces as $namespace => $prefix) {
            if (strpos($class, $namespace) === 0) {
                $relativeClass = substr($class, strlen($namespace));
                $file = $prefix . str_replace('\\', '/', $relativeClass) . '.php';

                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        }
    }
}
