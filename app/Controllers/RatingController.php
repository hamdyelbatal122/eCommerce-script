<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\App\Models\Rating;
use ECommerce\App\Models\Item;
use ECommerce\App\Models\ActivityLog;
use ECommerce\App\Models\Notification;

class RatingController extends BaseController
{
    /**
     * Display ratings for item
     */
    public function index()
    {
        $itemId = (int) $_GET['item_id'] ?? 0;
        $item = Item::find($itemId);

        if (!$item) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        $ratings = Rating::getApprovedForItem($itemId);
        $avgRating = Rating::getAverageRating($itemId);

        return $this->json([
            'item' => $item,
            'ratings' => $ratings,
            'average_rating' => $avgRating,
            'total_reviews' => count($ratings)
        ]);
    }

    /**
     * Create rating
     */
    public function store()
    {
        $this->checkAuth();

        $itemId = (int) $_POST['item_id'] ?? 0;
        $rating = (int) $_POST['rating'] ?? 0;
        $title = trim($_POST['title'] ?? '');
        $review = trim($_POST['review'] ?? '');

        if (!$itemId) {
            return $this->json(['error' => 'Invalid item'], 400);
        }

        $ratingRecord = Rating::createRating(
            $itemId,
            $this->auth->id,
            $rating,
            $title ?: null,
            $review ?: null
        );

        if (!$ratingRecord) {
            return $this->json(
                ['error' => 'You must purchase this item to leave a review'],
                403
            );
        }

        ActivityLog::log(
            $this->auth->id,
            'create',
            'rating',
            $ratingRecord['id'],
            "Rated item {$itemId} with {$rating} stars"
        );

        // Notify seller
        $item = Item::find($itemId);

        if ($item) {
            Notification::send(
                $item['Member_ID'],
                Notification::TYPE_RATING,
                'New Review',
                "User {$this->auth->username} left a {$rating} star review on your item",
                ['item_id' => $itemId, 'rating_id' => $ratingRecord['id']],
                "/item/{$itemId}"
            );
        }

        return $this->json([
            'success' => true,
            'message' => 'Review submitted for approval'
        ]);
    }

    /**
     * Update rating
     */
    public function update()
    {
        $this->checkAuth();

        $ratingId = (int) $_POST['id'] ?? 0;
        $rating = Rating::find($ratingId);

        if (!$rating || $rating['user_id'] !== $this->auth->id) {
            return $this->json(['error' => 'Rating not found'], 404);
        }

        $newRating = (int) $_POST['rating'] ?? 0;
        $title = trim($_POST['title'] ?? '');
        $review = trim($_POST['review'] ?? '');

        if ($newRating < 1 || $newRating > 5) {
            return $this->json(['error' => 'Invalid rating'], 400);
        }

        Rating::where('id', $ratingId)->update([
            'rating' => $newRating,
            'title' => $title ?: null,
            'review_text' => $review ?: null,
            'status' => Rating::STATUS_PENDING
        ]);

        ActivityLog::log(
            $this->auth->id,
            'update',
            'rating',
            $ratingId,
            "Updated review to {$newRating} stars"
        );

        return $this->json(['success' => true]);
    }

    /**
     * Delete rating
     */
    public function delete()
    {
        $this->checkAuth();

        $ratingId = (int) $_POST['id'] ?? 0;
        $rating = Rating::find($ratingId);

        if (!$rating || $rating['user_id'] !== $this->auth->id) {
            return $this->json(['error' => 'Rating not found'], 404);
        }

        Rating::where('id', $ratingId)->delete();

        ActivityLog::log(
            $this->auth->id,
            'delete',
            'rating',
            $ratingId,
            'Deleted review'
        );

        return $this->json(['success' => true]);
    }

    /**
     * Mark as helpful
     */
    public function helpful()
    {
        $ratingId = (int) $_POST['id'] ?? 0;

        if (!$ratingId) {
            return $this->json(['error' => 'Invalid rating'], 400);
        }

        Rating::markHelpful($ratingId);

        return $this->json(['success' => true]);
    }

    /**
     * Admin: Get pending reviews
     */
    public function adminPending()
    {
        $this->checkAdmin();

        $pending = Rating::getPending();

        return $this->render('admin/ratings/pending', [
            'ratings' => $pending
        ]);
    }

    /**
     * Admin: Approve review
     */
    public function adminApprove()
    {
        $this->checkAdmin();

        $ratingId = (int) $_POST['id'] ?? 0;

        if (!Rating::approve($ratingId)) {
            return $this->json(['error' => 'Rating not found'], 404);
        }

        ActivityLog::log($this->auth->id, 'approve', 'rating', $ratingId, 'Approved review');

        return $this->json(['success' => true]);
    }

    /**
     * Admin: Reject review
     */
    public function adminReject()
    {
        $this->checkAdmin();

        $ratingId = (int) $_POST['id'] ?? 0;

        if (!Rating::reject($ratingId)) {
            return $this->json(['error' => 'Rating not found'], 404);
        }

        ActivityLog::log($this->auth->id, 'reject', 'rating', $ratingId, 'Rejected review');

        return $this->json(['success' => true]);
    }
}
