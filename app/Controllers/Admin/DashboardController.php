<?php

namespace ECommerce\App\Controllers\Admin;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\App\Models\User;
use ECommerce\App\Models\Item;
use ECommerce\App\Models\Comment;

/**
 * Admin Dashboard Controller
 */
class DashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    /**
     * Require admin access
     */
    private function requireAdmin()
    {
        if (!Authenticator::isAdmin()) {
            $this->abort(403, 'Access Denied');
        }
    }

    /**
     * Display dashboard
     */
    public function index()
    {
        $userModel = new User();
        $itemModel = new Item();
        $commentModel = new Comment();

        $stats = [
            'total_users' => $userModel->getTotalCount(),
            'pending_users' => $userModel->getPendingCount(),
            'total_items' => $itemModel->getTotalCount(),
            'pending_items' => $itemModel->getPendingCount(),
            'total_comments' => $commentModel->getTotalCount(),
            'pending_comments' => $commentModel->getPendingCount(),
        ];

        $latestUsers = $userModel->getLatest(5);
        $latestItems = $itemModel->getLatest(5, false);
        $latestComments = $commentModel->getLatest(5, false);

        return $this->render('admin.dashboard', [
            'stats' => $stats,
            'latest_users' => $latestUsers,
            'latest_items' => $latestItems,
            'latest_comments' => $latestComments,
        ]);
    }
}
