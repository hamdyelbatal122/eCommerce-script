<?php

namespace ECommerce\App\Controllers\Admin;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Database;

class AnalyticsController extends BaseController
{
    public function wishlist()
    {
        if (!Authenticator::isAdmin()) {
            $this->abort(403, 'Access denied');
        }

        $topWishlisted = Database::getInstance()->query(
            'SELECT i.item_id, i.name, COUNT(w.id) AS wishes
             FROM wishlists w
             INNER JOIN items i ON i.item_id = w.item_id
             GROUP BY i.item_id, i.name
             ORDER BY wishes DESC
             LIMIT 20'
        );

        $usersWithWishlist = Database::getInstance()->queryOne('SELECT COUNT(DISTINCT user_id) AS total FROM wishlists');
        $totalEntries = Database::getInstance()->queryOne('SELECT COUNT(*) AS total FROM wishlists');

        return $this->render('admin.analytics.wishlist', [
            'top_wishlisted' => $topWishlisted,
            'users_with_wishlist' => (int) ($usersWithWishlist['total'] ?? 0),
            'total_entries' => (int) ($totalEntries['total'] ?? 0),
        ]);
    }
}
