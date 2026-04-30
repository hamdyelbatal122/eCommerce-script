<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;

class Notification extends BaseModel
{
    protected string $table = 'notifications';
    protected array $fillable = ['user_id', 'type', 'title', 'message', 'data', 'read_at', 'action_url'];

    const TYPE_ORDER = 'order';
    const TYPE_RATING = 'rating';
    const TYPE_COMMENT = 'comment';
    const TYPE_MESSAGE = 'message';
    const TYPE_SYSTEM = 'system';
    const TYPE_PROMOTION = 'promotion';

    /**
     * Send notification
     */
    public static function send(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null
    ): self|false {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data ? json_encode($data) : null,
            'action_url' => $actionUrl
        ]);
    }

    /**
     * Get user's notifications
     */
    public static function getUserNotifications(int $userId, int $limit = 20): array
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get() ?? [];
    }

    /**
     * Get unread notifications
     */
    public static function getUnread(int $userId): array
    {
        return self::where(['user_id' => $userId, 'read_at' => null])
            ->orderBy('created_at', 'DESC')
            ->get() ?? [];
    }

    /**
     * Get unread count
     */
    public static function getUnreadCount(int $userId): int
    {
        return self::where(['user_id' => $userId, 'read_at' => null])->count() ?? 0;
    }

    /**
     * Mark as read
     */
    public static function markAsRead(int $notificationId): bool
    {
        return self::where('id', $notificationId)
            ->update(['read_at' => 'NOW()']);
    }

    /**
     * Mark all as read
     */
    public static function markAllAsRead(int $userId): bool
    {
        return self::where(['user_id' => $userId, 'read_at' => null])
            ->update(['read_at' => 'NOW()']);
    }

    /**
     * Notify all admins
     */
    public static function notifyAdmins(string $title, string $message, array $data = []): void
    {
        $admins = User::where('GroupID', 1)->get() ?? [];

        foreach ($admins as $admin) {
            self::send($admin['UserID'], self::TYPE_SYSTEM, $title, $message, $data);
        }
    }

    /**
     * Send bulk notification
     */
    public static function sendBulk(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null
    ): bool {
        foreach ($userIds as $userId) {
            self::send($userId, $type, $title, $message, $data, $actionUrl);
        }

        return true;
    }
}
