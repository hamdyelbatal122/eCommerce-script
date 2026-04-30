<?php

namespace ECommerce\App\Controllers\Admin;

use ECommerce\Core\BaseController;
use ECommerce\App\Models\ActivityLog;

class LogController extends BaseController
{
    /**
     * Display activity logs
     */
    public function index()
    {
        $this->checkAdmin();

        $action = $_GET['action'] ?? null;
        $entityType = $_GET['type'] ?? null;
        $userId = (int) $_GET['user_id'] ?? 0;

        $logs = ActivityLog::getRecent(100);

        // Filter if needed
        if ($action) {
            $logs = array_filter($logs, fn($l) => $l['action'] === $action);
        }

        if ($entityType) {
            $logs = array_filter($logs, fn($l) => $l['entity_type'] === $entityType);
        }

        if ($userId) {
            $logs = array_filter($logs, fn($l) => $l['user_id'] === $userId);
        }

        return $this->render('admin/logs/index', [
            'logs' => $logs,
            'action' => $action,
            'entity_type' => $entityType,
            'user_id' => $userId
        ]);
    }

    /**
     * Get user's activity logs
     */
    public function userActivity()
    {
        $this->checkAdmin();

        $userId = (int) $_GET['user_id'] ?? 0;

        if (!$userId) {
            return $this->json(['error' => 'User ID required'], 400);
        }

        $logs = ActivityLog::getUserActivity($userId);

        return $this->json([
            'logs' => $logs,
            'total' => count($logs)
        ]);
    }

    /**
     * Cleanup old logs
     */
    public function cleanup()
    {
        $this->checkAdmin();

        $daysOld = (int) $_POST['days'] ?? 90;

        if ($daysOld < 7) {
            return $this->json(['error' => 'Minimum 7 days'], 400);
        }

        $deleted = ActivityLog::cleanOldLogs($daysOld);

        ActivityLog::log(
            $this->auth->id,
            'cleanup',
            'system',
            null,
            "Cleaned up {$deleted} activity logs older than {$daysOld} days"
        );

        return $this->json([
            'success' => true,
            'deleted' => $deleted
        ]);
    }
}
