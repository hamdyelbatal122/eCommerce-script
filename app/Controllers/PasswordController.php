<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Database;
use ECommerce\App\Models\User;

class PasswordController extends BaseController
{
    public function requestForm()
    {
        return $this->render('auth.forgot-password');
    }

    public function sendResetLink()
    {
        $this->requireCsrf();
        $email = strtolower(trim((string) $this->post('email', '')));
        if ($email === '') {
            $this->flash('error', 'Email is required.', 'error');
            $this->redirect('/password/forgot');
        }

        $user = (new User())->findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            Database::getInstance()->execute(
                'INSERT INTO password_resets (user_id, email, token, expires_at, created_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())',
                [$user['user_id'], $email, $token]
            );
            // In production, send email. For now keep token in flash for setup testing.
            $this->flash('success', 'Password reset link generated: /password/reset/' . $token, 'success');
        } else {
            $this->flash('success', 'If your email exists, a reset link has been generated.', 'success');
        }

        $this->redirect('/password/forgot');
    }

    public function resetForm($token)
    {
        $row = Database::getInstance()->queryOne(
            'SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1',
            [$token]
        );
        if (!$row) {
            $this->abort(404, 'Invalid or expired reset token');
        }
        return $this->render('auth.reset-password', ['token' => $token]);
    }

    public function reset($token)
    {
        $this->requireCsrf();
        $password = (string) $this->post('password', '');
        $confirm = (string) $this->post('password_confirm', '');
        if (strlen($password) < 8 || $password !== $confirm) {
            $this->flash('error', 'Password must be at least 8 chars and match confirmation.', 'error');
            $this->redirect('/password/reset/' . $token);
        }

        $row = Database::getInstance()->queryOne(
            'SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1',
            [$token]
        );
        if (!$row) {
            $this->abort(404, 'Invalid or expired reset token');
        }

        (new User())->update((int) $row['user_id'], [
            'password' => Authenticator::hash($password),
        ]);
        Database::getInstance()->execute('DELETE FROM password_resets WHERE token = ?', [$token]);

        $this->flash('success', 'Password updated successfully. Please login.', 'success');
        $this->redirect('/login');
    }
}
