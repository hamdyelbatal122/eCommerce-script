<?php

namespace ECommerce\App\Middleware;

use ECommerce\App\Models\ApiToken;

class ApiAuthMiddleware
{
    /**
     * Handle API authentication
     */
    public static function handle(): bool
    {
        // Check for API token in header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? '';

        // Remove 'Bearer ' prefix if present
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        if (!$token) {
            return false;
        }

        // Verify token
        $apiToken = ApiToken::verify($token);

        if (!$apiToken) {
            return false;
        }

        // Store user info in request
        $_REQUEST['api_user_id'] = $apiToken['user_id'];
        $_REQUEST['api_token_id'] = $apiToken['id'];

        return true;
    }
}
