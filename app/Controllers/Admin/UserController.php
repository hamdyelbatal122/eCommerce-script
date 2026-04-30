<?php

namespace ECommerce\App\Controllers\Admin;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Validator;
use ECommerce\App\Models\User;

/**
 * Admin User Controller
 */
class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    private function requireAdmin()
    {
        if (!Authenticator::isAdmin()) {
            $this->abort(403, 'Access Denied');
        }
    }

    /**
     * List users
     */
    public function index()
    {
        $userModel = new User();
        $status = $this->getParam('status', 'all');

        if ($status === 'pending') {
            $users = $userModel->getPending();
        } else {
            $users = $userModel->all();
        }

        return $this->render('admin.users.index', [
            'users' => $users,
            'status' => $status,
        ]);
    }

    /**
     * Edit user
     */
    public function edit($id)
    {
        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            $this->abort(404, 'User not found');
        }

        return $this->render('admin.users.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/admin/users/{$id}/edit");
        }

        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            $this->json(['error' => 'User not found'], 404);
        }

        $data = $this->post();
        $validator = Validator::validate($data);

        $validator->required('username', 'Username is required')
                  ->required('email', 'Email is required')
                  ->email('email', 'Invalid email format')
                  ->required('full_name', 'Full name is required');

        if ($validator->fails()) {
            $this->json(['errors' => $validator->errors()], 422);
        }

        // Check unique username
        if ($data['username'] !== $user['username'] && $userModel->usernameExists($data['username'], $id)) {
            $this->json(['error' => 'Username already exists'], 422);
        }

        // Check unique email
        if ($data['email'] !== $user['email'] && $userModel->emailExists($data['email'], $id)) {
            $this->json(['error' => 'Email already exists'], 422);
        }

        $updateData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'full_name' => $data['full_name'],
            'reg_status' => isset($data['reg_status']) ? 1 : 0,
            'group_id' => isset($data['is_admin']) ? 1 : 0,
        ];

        if ($userModel->update($id, $updateData)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to update user'], 500);
    }

    /**
     * Approve user
     */
    public function approve($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid method'], 405);
        }

        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            $this->json(['error' => 'User not found'], 404);
        }

        if ($userModel->update($id, ['reg_status' => User::STATUS_APPROVED])) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to approve user'], 500);
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid method'], 405);
        }

        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            $this->json(['error' => 'User not found'], 404);
        }

        if ($userModel->delete($id)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete user'], 500);
    }
}
