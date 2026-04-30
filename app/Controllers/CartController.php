<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Database;

class CartController extends BaseController
{
    public function index()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->redirect('/login');
        }

        $cart = $this->getCartRows(Authenticator::userId());
        $subtotal = $this->calculateSubtotal($cart);
        $deliveryFee = $this->calculateDeliveryFee($subtotal);
        $total = $subtotal + $deliveryFee;

        return $this->render('cart.index', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
        ]);
    }

    public function add()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->requireCsrf();

        $itemId = (int) $this->post('item_id', 0);
        $quantity = max(1, (int) $this->post('quantity', 1));
        if ($itemId <= 0) {
            $this->json(['error' => 'Invalid item'], 422);
        }

        $db = Database::getInstance();
        $exists = $db->queryOne('SELECT id, quantity FROM cart_items WHERE user_id = ? AND item_id = ?', [Authenticator::userId(), $itemId]);

        if ($exists) {
            $db->execute('UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?', [(int) $exists['quantity'] + $quantity, $exists['id']]);
        } else {
            $db->execute(
                'INSERT INTO cart_items (user_id, item_id, quantity, added_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())',
                [Authenticator::userId(), $itemId, $quantity]
            );
        }

        $this->json(['success' => true]);
    }

    public function update()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->requireCsrf();

        $itemId = (int) $this->post('item_id', 0);
        $quantity = max(0, (int) $this->post('quantity', 0));
        if ($itemId <= 0) {
            $this->json(['error' => 'Invalid item'], 422);
        }

        $db = Database::getInstance();
        if ($quantity === 0) {
            $db->execute('DELETE FROM cart_items WHERE user_id = ? AND item_id = ?', [Authenticator::userId(), $itemId]);
        } else {
            $db->execute('UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND item_id = ?', [$quantity, Authenticator::userId(), $itemId]);
        }

        $this->json(['success' => true]);
    }

    public function remove()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->requireCsrf();

        $itemId = (int) $this->post('item_id', 0);
        Database::getInstance()->execute('DELETE FROM cart_items WHERE user_id = ? AND item_id = ?', [Authenticator::userId(), $itemId]);
        $this->json(['success' => true]);
    }

    public function clear()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->requireCsrf();

        Database::getInstance()->execute('DELETE FROM cart_items WHERE user_id = ?', [Authenticator::userId()]);
        $this->json(['success' => true]);
    }

    public function count()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['count' => 0]);
        }

        $row = Database::getInstance()->queryOne('SELECT COALESCE(SUM(quantity), 0) AS count FROM cart_items WHERE user_id = ?', [Authenticator::userId()]);
        $this->json(['count' => (int) ($row['count'] ?? 0)]);
    }

    private function getCartRows(int $userId): array
    {
        $db = Database::getInstance();
        return $db->query(
            'SELECT c.item_id, c.quantity, i.name, i.price
             FROM cart_items c
             INNER JOIN items i ON i.item_id = c.item_id
             WHERE c.user_id = ?
             ORDER BY c.id DESC',
            [$userId]
        );
    }

    private function calculateSubtotal(array $cart): float
    {
        $sum = 0.0;
        foreach ($cart as $row) {
            $sum += ((float) $row['price']) * ((int) $row['quantity']);
        }
        return $sum;
    }

    private function calculateDeliveryFee(float $subtotal): float
    {
        if ($subtotal >= 200) {
            return 0.0;
        }
        return $subtotal > 0 ? 10.0 : 0.0;
    }
}
