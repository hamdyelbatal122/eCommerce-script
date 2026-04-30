<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;

class Rating extends BaseModel
{
    protected string $table = 'ratings';
    protected array $fillable = ['item_id', 'user_id', 'rating', 'title', 'review_text', 'status'];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Create rating with validation
     */
    public static function createRating(
        int $itemId,
        int $userId,
        int $rating,
        ?string $title = null,
        ?string $review = null
    ): self|false {
        // Validate rating is 1-5
        if ($rating < 1 || $rating > 5) {
            return false;
        }

        // Check if user purchased this item
        $purchased = OrderItem::where(['item_id' => $itemId])
            ->join('orders', 'orders.id', 'order_items.order_id')
            ->where(['orders.user_id' => $userId, 'order_items.item_id' => $itemId])
            ->first();

        if (!$purchased) {
            return false; // User must purchase to review
        }

        // Delete existing rating if any
        self::where(['item_id' => $itemId, 'user_id' => $userId])->delete();

        return self::create([
            'item_id' => $itemId,
            'user_id' => $userId,
            'rating' => $rating,
            'title' => $title,
            'review_text' => $review,
            'status' => self::STATUS_PENDING
        ]);
    }

    /**
     * Get approved ratings for item
     */
    public static function getApprovedForItem(int $itemId): array
    {
        return self::where(['item_id' => $itemId, 'status' => self::STATUS_APPROVED])
            ->orderBy('created_at', 'DESC')
            ->get() ?? [];
    }

    /**
     * Get item average rating
     */
    public static function getAverageRating(int $itemId): float
    {
        $result = self::connection()->prepare(
            "SELECT AVG(rating) as avg_rating FROM {$this->table} 
             WHERE item_id = ? AND status = ?"
        );
        $result->execute([$itemId, self::STATUS_APPROVED]);
        $row = $result->fetch(\PDO::FETCH_ASSOC);

        return $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
    }

    /**
     * Get pending reviews for approval
     */
    public static function getPending(): array
    {
        return self::where('status', self::STATUS_PENDING)
            ->orderBy('created_at', 'ASC')
            ->get() ?? [];
    }

    /**
     * Approve review
     */
    public static function approve(int $ratingId): bool
    {
        return self::where('id', $ratingId)
            ->update(['status' => self::STATUS_APPROVED]);
    }

    /**
     * Reject review
     */
    public static function reject(int $ratingId): bool
    {
        return self::where('id', $ratingId)
            ->delete();
    }

    /**
     * Mark as helpful
     */
    public static function markHelpful(int $ratingId): bool
    {
        return self::where('id', $ratingId)
            ->update(['helpful_count' => \PDO::PARAM_INT + 1]);
    }

    /**
     * Get user's ratings
     */
    public static function getUserRatings(int $userId): array
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get() ?? [];
    }
}
