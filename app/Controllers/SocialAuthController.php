<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Database;
use ECommerce\App\Models\User;
use ECommerce\App\Services\GoogleOAuthService;

class SocialAuthController extends BaseController
{
    public function redirectGoogle()
    {
        $service = new GoogleOAuthService();
        if (!$service->isConfigured()) {
            $this->flash('error', 'Google login is not configured.', 'error');
            $this->redirect('/login');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state'] = $state;
        $_SESSION['google_oauth_state_expires'] = time() + 600;

        $this->redirect($service->buildAuthUrl($state));
    }

    public function callbackGoogle()
    {
        $service = new GoogleOAuthService();
        $state = $_GET['state'] ?? '';
        $code = $_GET['code'] ?? '';

        if (!$this->isValidState($state)) {
            $this->flash('error', 'Invalid OAuth state. Please retry.', 'error');
            $this->redirect('/login');
        }

        if ($code === '') {
            $this->flash('error', 'Missing Google authorization code.', 'error');
            $this->redirect('/login');
        }

        $tokenResult = $service->getToken($code);
        if (!$tokenResult['ok']) {
            $this->flash('error', 'Google token exchange failed.', 'error');
            $this->redirect('/login');
        }

        $accessToken = $tokenResult['data']['access_token'] ?? '';
        $profileResult = $service->getUserInfo($accessToken);
        if (!$profileResult['ok']) {
            $this->flash('error', 'Failed to fetch Google profile.', 'error');
            $this->redirect('/login');
        }

        $profile = $profileResult['data'];
        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        $googleId = (string) ($profile['id'] ?? '');
        $fullName = trim((string) ($profile['name'] ?? 'Google User'));

        if ($email === '' || $googleId === '') {
            $this->flash('error', 'Google did not return required account data.', 'error');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $this->findByGoogleOrEmail($googleId, $email);

        if (!$user) {
            $baseUsername = preg_replace('/[^a-z0-9_]/i', '', strstr($email, '@', true) ?: 'google_user');
            $username = $this->buildUniqueUsername($userModel, $baseUsername);

            $userId = $userModel->insert([
                'username' => $username,
                'email' => $email,
                'password' => Authenticator::hash(bin2hex(random_bytes(20))),
                'full_name' => $fullName,
                'group_id' => User::GROUP_USER,
                'reg_status' => User::STATUS_APPROVED,
                'google_id' => $googleId,
                'date' => date('Y-m-d H:i:s'),
            ]);

            if (!$userId) {
                $this->flash('error', 'Failed to create account from Google.', 'error');
                $this->redirect('/login');
            }

            $user = $userModel->find($userId);
        } else {
            $this->linkGoogleIdIfMissing((int) ($user['user_id'] ?? 0), $googleId);
        }

        Authenticator::login($user);
        $this->flash('success', 'Signed in with Google successfully.', 'success');
        $this->redirect('/');
    }

    private function isValidState(string $state): bool
    {
        $stored = $_SESSION['google_oauth_state'] ?? '';
        $expires = (int) ($_SESSION['google_oauth_state_expires'] ?? 0);
        unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_state_expires']);

        if ($stored === '' || $expires < time()) {
            return false;
        }

        return hash_equals($stored, $state);
    }

    private function findByGoogleOrEmail(string $googleId, string $email): ?array
    {
        $db = Database::getInstance();
        $user = $db->queryOne('SELECT * FROM users WHERE google_id = ? LIMIT 1', [$googleId]);
        if ($user) {
            return $user;
        }

        return $db->queryOne('SELECT * FROM users WHERE email = ? LIMIT 1', [$email]);
    }

    private function linkGoogleIdIfMissing(int $userId, string $googleId): void
    {
        if ($userId <= 0) {
            return;
        }

        $db = Database::getInstance();
        $db->execute(
            'UPDATE users SET google_id = COALESCE(google_id, ?) WHERE user_id = ?',
            [$googleId, $userId]
        );
    }

    private function buildUniqueUsername(User $userModel, string $base): string
    {
        $base = strtolower($base ?: 'google_user');
        $candidate = $base;
        $counter = 1;

        while ($userModel->usernameExists($candidate)) {
            $candidate = $base . $counter;
            $counter++;
        }

        return $candidate;
    }
}
