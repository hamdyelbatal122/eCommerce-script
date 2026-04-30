<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;

class ActivityLog extends BaseModel
{
    protected string $table = 'activity_logs';
    protected array $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'description', 'ip_address', 'user_agent', 'changes'];

    /**
     * Log activity
     */
    public static function log(
        ?int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $description = null,
        ?array $changes = null
    ): bool {
        return (bool) self::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'changes' => $changes ? json_encode($changes) : null
        ]);
    }

    /**
     * Get user's activity
     */
    public static function getUserActivity(int $userId, int $limit = 50): array
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get() ?? [];
    }

    /**
     * Get activity for entity
     */
    public static function getEntityActivity(string $entityType, int $entityId): array
    {
        return self::where(['entity_type' => $entityType, 'entity_id' => $entityId])
            ->orderBy('created_at', 'DESC')
            ->get() ?? [];
    }

    /**
     * Get recent activity
     */
    public static function getRecent(int $limit = 50): array
    {
        return self::orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get() ?? [];
    }

    /**
     * Login activity
     */
    public static function logLogin(int $userId): bool
    {
        return self::log($userId, 'login', 'user', $userId, 'User logged in');
    }

    /**
     * Item created activity
     */
    public static function logItemCreated(int $userId, int $itemId, string $itemName): bool
    {
        return self::log($userId, 'create', 'item', $itemId, "Created item: {$itemName}");
    }

    /**
     * Order created activity
     */
    public static function logOrderCreated(int $userId, int $orderId, string $orderNumber): bool
    {
        return self::log($userId, 'create', 'order', $orderId, "Created order: {$orderNumber}");
    }

    /**
     * Comment created activity
     */
    public static function logCommentCreated(int $userId, int $commentId, int $itemId): bool
    {
        return self::log($userId, 'create', 'comment', $commentId, "Commented on item ID: {$itemId}");
    }

    /**
     * Clean old logs
     */
    public static function cleanOldLogs(int $daysOld = 90): int
    {
        $stmt = self::connection()->prepare(
            "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->execute([$daysOld]);
        return $stmt->rowCount();
    }
}
