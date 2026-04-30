<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\App\Models\ApiToken;

class ApiTokenController extends BaseController
{
    /**
     * List user's API tokens
     */
    public function index()
    {
        $this->checkAuth();

        $tokens = ApiToken::getUserTokens($this->auth->id);

        return $this->render('api-tokens/index', [
            'tokens' => $tokens
        ]);
    }

    /**
     * Create new API token
     */
    public function create()
    {
        $this->checkAuth();

        $name = trim($_POST['name'] ?? '');
        $expiryDays = (int) $_POST['expiry_days'] ?? 30;

        if (!$name || strlen($name) < 3) {
            return $this->json(['error' => 'Token name must be at least 3 characters'], 400);
        }

        if ($expiryDays < 1 || $expiryDays > 365) {
            return $this->json(['error' => 'Expiry must be between 1 and 365 days'], 400);
        }

        $token = ApiToken::createForUser($this->auth->id, $name, $expiryDays);

        if (!$token) {
            return $this->json(['error' => 'Failed to create token'], 500);
        }

        return $this->json([
            'success' => true,
            'token' => $token['token'],
            'message' => 'Save this token somewhere safe. You won\'t be able to see it again!'
        ]);
    }

    /**
     * Revoke API token
     */
    public function revoke()
    {
        $this->checkAuth();

        $tokenId = (int) $_POST['id'] ?? 0;

        if (!$tokenId) {
            return $this->json(['error' => 'Invalid token'], 400);
        }

        if (!ApiToken::revokeToken($tokenId, $this->auth->id)) {
            return $this->json(['error' => 'Token not found'], 404);
        }

        return $this->json(['success' => true]);
    }

    /**
     * Test API token
     */
    public function test()
    {
        // This endpoint requires API token authentication
        return $this->json([
            'success' => true,
            'message' => 'API token is valid',
            'user' => [
                'id' => $this->auth->id,
                'username' => $this->auth->username,
                'email' => $this->auth->email
            ]
        ]);
    }
}
