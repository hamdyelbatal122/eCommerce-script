<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Validator;
use ECommerce\App\Models\Comment;
use ECommerce\App\Models\Item;

/**
 * Comment Controller
 * 
 * Handles item comments
 */
class CommentController extends BaseController
{
    /**
     * Add comment
     */
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }

        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'You must be logged in to comment'], 401);
        }

        $data = $this->post();
        $validator = Validator::validate($data);
        
        $validator->required('item_id', 'Item is required')
                  ->required('comment', 'Comment is required')
                  ->minLength('comment', 3, 'Comment must be at least 3 characters');

        if ($validator->fails()) {
            $this->json(['errors' => $validator->errors()], 422);
        }

        // Verify item exists
        $itemModel = new Item();
        $item = $itemModel->find($data['item_id']);

        if (!$item) {
            $this->json(['error' => 'Item not found'], 404);
        }

        // Insert comment
        $commentModel = new Comment();
        $commentId = $commentModel->insert([
            'comment' => htmlspecialchars($data['comment']),
            'item_id' => (int) $data['item_id'],
            'user_id' => Authenticator::userId(),
            'comment_date' => date('Y-m-d H:i:s'),
            'status' => 0, // Pending approval
        ]);

        if ($commentId) {
            $this->json([
                'success' => true,
                'id' => $commentId,
                'message' => 'Comment posted! It will be visible after admin approval.',
            ]);
        }

        $this->json(['error' => 'Failed to add comment'], 500);
    }

    /**
     * Delete comment
     */
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid method'], 405);
        }

        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $commentModel = new Comment();
        $comment = $commentModel->find($id);

        if (!$comment) {
            $this->json(['error' => 'Comment not found'], 404);
        }

        // Check if user owns comment
        if ($comment['user_id'] !== Authenticator::userId() && !Authenticator::isAdmin()) {
            $this->json(['error' => 'Forbidden'], 403);
        }

        if ($commentModel->delete($id)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete comment'], 500);
    }
}
