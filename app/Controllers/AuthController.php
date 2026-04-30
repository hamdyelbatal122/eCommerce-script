<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Validator;
use ECommerce\App\Models\User;

/**
 * Auth Controller
 * 
 * Handles user authentication (login/signup)
 */
class AuthController extends BaseController
{
    /**
     * Display login form
     */
    public function loginForm()
    {
        if (Authenticator::isLoggedIn()) {
            $this->redirect('/');
        }

        return $this->render('auth.login', [
            'flash' => $this->getFlash(),
        ]);
    }

    /**
     * Handle login
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }
        $this->requireCsrf();

        $username = $this->post('username');
        $password = $this->post('password');

        // Validate input
        $validator = Validator::validate([
            'username' => $username,
            'password' => $password,
        ]);

        $validator->required('username', 'Username is required')
                  ->required('password', 'Password is required');

        if ($validator->fails()) {
            $this->flash('error', 'All fields are required', 'error');
            $this->redirect('/login');
        }

        // Find user
        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if (!$user) {
            $this->flash('error', 'Invalid username or password', 'error');
            $this->redirect('/login');
        }

        // Verify password
        if (!Authenticator::verify($password, $user['password'])) {
            $this->flash('error', 'Invalid username or password', 'error');
            $this->redirect('/login');
        }

        // Check if user is approved
        if ($user['reg_status'] !== 1) {
            $this->flash('error', 'Your account is pending approval. Please wait for admin confirmation.', 'warning');
            $this->redirect('/login');
        }

        // Login user
        Authenticator::login($user);
        $this->flash('success', 'Welcome back!', 'success');
        $this->redirect('/');
    }

    /**
     * Display signup form
     */
    public function registerForm()
    {
        if (Authenticator::isLoggedIn()) {
            $this->redirect('/');
        }

        return $this->render('auth.register', [
            'flash' => $this->getFlash(),
        ]);
    }

    /**
     * Handle signup
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
        }
        $this->requireCsrf();

        $username = trim($this->post('username', ''));
        $email = trim($this->post('email', ''));
        $password = $this->post('password', '');
        $passwordConfirm = $this->post('password_confirm', '');
        $fullName = trim($this->post('full_name', ''));

        // Validate input
        $validator = Validator::validate([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirm' => $passwordConfirm,
            'full_name' => $fullName,
        ]);

        $validator->required('username', 'Username is required')
                  ->minLength('username', 3, 'Username must be at least 3 characters')
                  ->required('email', 'Email is required')
                  ->email('email', 'Invalid email format')
                  ->required('password', 'Password is required')
                  ->minLength('password', 6, 'Password must be at least 6 characters')
                  ->required('full_name', 'Full name is required');

        // Check if passwords match
        if ($password !== $passwordConfirm) {
            $validator->addError('password', 'Passwords do not match');
        }

        if ($validator->fails()) {
            $errors = $validator->errors();
            $this->flash('error', reset($errors)[0], 'error');
            $this->redirect('/register');
        }

        // Check if username/email exist
        $userModel = new User();
        if ($userModel->usernameExists($username)) {
            $this->flash('error', 'Username already exists', 'error');
            $this->redirect('/register');
        }

        if ($userModel->emailExists($email)) {
            $this->flash('error', 'Email already exists', 'error');
            $this->redirect('/register');
        }

        // Create user
        $userId = $userModel->insert([
            'username' => $username,
            'email' => $email,
            'password' => Authenticator::hash($password),
            'full_name' => $fullName,
            'group_id' => User::GROUP_USER,
            'reg_status' => User::STATUS_PENDING,
            'date' => date('Y-m-d H:i:s'),
        ]);

        if ($userId) {
            $this->flash('success', 'Account created! Please wait for admin approval to start using the platform.', 'success');
            $this->redirect('/login');
        }

        $this->flash('error', 'Failed to create account. Please try again.', 'error');
        $this->redirect('/register');
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $this->requireCsrf();
        Authenticator::logout();
        $this->flash('success', 'You have been logged out.', 'success');
        $this->redirect('/');
    }
}
