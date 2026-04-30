<?php

namespace ECommerce\App\Controllers\Admin;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Validator;
use ECommerce\App\Models\Comment;

/**
 * Admin Comment Controller
 */
class CommentController extends BaseController
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
     * List comments
     */
    public function index()
    {
        $commentModel = new Comment();
        $status = $this->getParam('status', 'all');

        if ($status === 'pending') {
            $comments = $commentModel->getPending();
        } else {
            $comments = $commentModel->all();
        }

        return $this->render('admin.comments.index', [
            'comments' => $comments,
            'status' => $status,
        ]);
    }

    /**
     * Edit comment
     */
    public function edit($id)
    {
        $commentModel = new Comment();
        $comment = $commentModel->getFullDetails($id);

        if (!$comment) {
            $this->abort(404, 'Comment not found');
        }

        return $this->render('admin.comments.edit', [
            'comment' => $comment,
        ]);
    }

    /**
     * Update comment
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/admin/comments/{$id}/edit");
        }

        $commentModel = new Comment();
        $comment = $commentModel->find($id);

        if (!$comment) {
            $this->json(['error' => 'Comment not found'], 404);
        }

        $data = $this->post();
        $validator = Validator::validate($data);

        $validator->required('comment', 'Comment is required')
                  ->minLength('comment', 3, 'Comment must be at least 3 characters');

        if ($validator->fails()) {
            $this->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'comment' => htmlspecialchars($data['comment']),
        ];

        if ($commentModel->update($id, $updateData)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to update comment'], 500);
    }

    /**
     * Approve comment
     */
    public function approve($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid method'], 405);
        }

        $commentModel = new Comment();
        $comment = $commentModel->find($id);

        if (!$comment) {
            $this->json(['error' => 'Comment not found'], 404);
        }

        if ($commentModel->approve($id)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to approve comment'], 500);
    }

    /**
     * Delete comment
     */
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid method'], 405);
        }

        $commentModel = new Comment();
        $comment = $commentModel->find($id);

        if (!$comment) {
            $this->json(['error' => 'Comment not found'], 404);
        }

        if ($commentModel->delete($id)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete comment'], 500);
    }
}
