<?php

namespace ECommerce\Core;

/**
 * Authenticator Class
 * 
 * Handles user authentication
 */
class Authenticator
{
    /**
     * Hash password
     * 
     * @param string $password
     * @return string
     */
    public static function hash($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify password
     * 
     * @param string $password plain text password
     * @param string $hash hashed password
     * @return bool
     */
    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Login user
     * 
     * @param array $user user data from database
     * @return void
     */
    public static function login($user)
    {
        $_SESSION['user_id'] = $user['id'] ?? $user['user_id'];
        $_SESSION['user'] = [
            'id' => $user['id'] ?? $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'] ?? null,
            'full_name' => $user['full_name'] ?? null,
            'group_id' => $user['group_id'] ?? 0,
            'avatar' => $user['avatar'] ?? null,
        ];
    }

    /**
     * Logout user
     * 
     * @return void
     */
    public static function logout()
    {
        session_destroy();
    }

    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Check if user is admin
     * 
     * @return bool
     */
    public static function isAdmin()
    {
        return self::isLoggedIn() && ($_SESSION['user']['group_id'] ?? 0) == 1;
    }

    /**
     * Get current user
     * 
     * @return array|null
     */
    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get current user ID
     * 
     * @return int|null
     */
    public static function userId()
    {
        return $_SESSION['user_id'] ?? null;
    }
}
