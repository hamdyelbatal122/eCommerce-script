<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\App\Models\Notification;

class NotificationController extends BaseController
{
    /**
     * Get user's notifications
     */
    public function index()
    {
        $this->checkAuth();

        $limit = (int) $_GET['limit'] ?? 20;
        $notifications = Notification::getUserNotifications($this->auth->id, $limit);

        return $this->json([
            'notifications' => $notifications,
            'unread_count' => Notification::getUnreadCount($this->auth->id)
        ]);
    }

    /**
     * Get unread notifications
     */
    public function unread()
    {
        $this->checkAuth();

        $unread = Notification::getUnread($this->auth->id);

        return $this->json([
            'unread' => $unread,
            'count' => count($unread)
        ]);
    }

    /**
     * Mark as read
     */
    public function read()
    {
        $this->checkAuth();

        $notificationId = (int) $_POST['id'] ?? 0;

        if (!$notificationId) {
            return $this->json(['error' => 'Invalid notification'], 400);
        }

        Notification::markAsRead($notificationId);

        return $this->json(['success' => true]);
    }

    /**
     * Mark all as read
     */
    public function readAll()
    {
        $this->checkAuth();

        Notification::markAllAsRead($this->auth->id);

        return $this->json(['success' => true]);
    }

    /**
     * Delete notification
     */
    public function delete()
    {
        $this->checkAuth();

        $notificationId = (int) $_POST['id'] ?? 0;

        if (!$notificationId) {
            return $this->json(['error' => 'Invalid notification'], 400);
        }

        Notification::where('id', $notificationId)
            ->where('user_id', $this->auth->id)
            ->delete();

        return $this->json(['success' => true]);
    }

    /**
     * Clear all notifications
     */
    public function clear()
    {
        $this->checkAuth();

        Notification::where('user_id', $this->auth->id)->delete();

        return $this->json(['success' => true]);
    }
}
