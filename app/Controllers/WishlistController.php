<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Database;

class WishlistController extends BaseController
{
    public function index()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->redirect('/login');
        }

        $items = Database::getInstance()->query(
            'SELECT w.item_id, i.name, i.price, i.add_date
             FROM wishlists w
             INNER JOIN items i ON i.item_id = w.item_id
             WHERE w.user_id = ?
             ORDER BY w.created_at DESC',
            [Authenticator::userId()]
        );

        return $this->render('wishlist.index', ['items' => $items]);
    }

    public function add()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->requireCsrf();

        $itemId = (int) $this->post('item_id', 0);
        if ($itemId <= 0) {
            $this->json(['error' => 'Invalid item'], 422);
        }

        Database::getInstance()->execute(
            'INSERT IGNORE INTO wishlists (user_id, item_id, created_at) VALUES (?, ?, NOW())',
            [Authenticator::userId(), $itemId]
        );

        $this->json(['success' => true]);
    }

    public function remove()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->requireCsrf();

        $itemId = (int) $this->post('item_id', 0);
        Database::getInstance()->execute(
            'DELETE FROM wishlists WHERE user_id = ? AND item_id = ?',
            [Authenticator::userId(), $itemId]
        );

        $this->json(['success' => true]);
    }
}
