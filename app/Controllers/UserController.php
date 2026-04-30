<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Validator;
use ECommerce\App\Models\User;
use ECommerce\App\Models\Item;
use ECommerce\App\Models\Comment;

/**
 * User Controller
 * 
 * Handles user profile and management
 */
class UserController extends BaseController
{
    /**
     * Display user profile
     */
    public function profile($id)
    {
        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user || $user['reg_status'] !== 1) {
            $this->abort(404, 'User not found');
        }

        $itemModel = new Item();
        $userItems = $itemModel->getByMember($id, true);

        $commentModel = new Comment();
        $userComments = $commentModel->getUserComments($id, true);

        return $this->render('user.profile', [
            'user' => $user,
            'items' => $userItems,
            'comments' => $userComments,
            'is_owner' => Authenticator::userId() === $id,
        ]);
    }

    /**
     * Display dashboard
     */
    public function dashboard()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->flash('error', 'Please login first', 'error');
            $this->redirect('/login');
        }

        $userId = Authenticator::userId();
        $userModel = new User();
        $user = $userModel->find($userId);

        $itemModel = new Item();
        $userItems = $itemModel->getByMember($userId, false);

        $commentModel = new Comment();
        $userComments = $commentModel->getUserComments($userId, true);

        return $this->render('user.dashboard', [
            'user' => $user,
            'items' => $userItems,
            'comments' => $userComments,
            'flash' => $this->getFlash(),
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
        }

        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::validate($this->post());
        
        $validator->required('full_name', 'Full name is required');

        if ($validator->fails()) {
            $this->json(['errors' => $validator->errors()], 422);
        }

        $userId = Authenticator::userId();
        $userModel = new User();
        
        $result = $userModel->update($userId, [
            'full_name' => htmlspecialchars($this->post('full_name')),
        ]);

        if ($result) {
            // Update session
            $user = $userModel->find($userId);
            Authenticator::login($user);
            
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to update profile'], 500);
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
        }

        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = $this->post();
        $validator = Validator::validate($data);
        
        $validator->required('current_password', 'Current password is required')
                  ->required('password', 'New password is required')
                  ->minLength('password', 6, 'Password must be at least 6 characters')
                  ->required('password_confirm', 'Password confirmation is required');

        if ($validator->fails()) {
            $this->json(['errors' => $validator->errors()], 422);
        }

        if ($data['password'] !== $data['password_confirm']) {
            $this->json(['error' => 'Passwords do not match'], 422);
        }

        $userId = Authenticator::userId();
        $userModel = new User();
        $user = $userModel->find($userId);

        // Verify current password
        if (!Authenticator::verify($data['current_password'], $user['password'])) {
            $this->json(['error' => 'Current password is incorrect'], 401);
        }

        // Update password
        $result = $userModel->update($userId, [
            'password' => Authenticator::hash($data['password']),
        ]);

        if ($result) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to change password'], 500);
    }
}
