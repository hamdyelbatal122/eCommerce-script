<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Database;
use ECommerce\App\Services\StripeService;

class OrderController extends BaseController
{
    public function index()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->redirect('/login');
        }

        $orders = Database::getInstance()->query(
            'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC',
            [Authenticator::userId()]
        );

        return $this->render('orders.index', ['orders' => $orders]);
    }

    public function show($id)
    {
        if (!Authenticator::isLoggedIn()) {
            $this->redirect('/login');
        }

        $db = Database::getInstance();
        $order = $db->queryOne('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1', [(int) $id, Authenticator::userId()]);
        if (!$order) {
            $this->abort(404, 'Order not found');
        }

        $items = $db->query(
            'SELECT oi.*, i.name FROM order_items oi INNER JOIN items i ON i.item_id = oi.item_id WHERE oi.order_id = ?',
            [$order['id']]
        );

        return $this->render('orders.show', ['order' => $order, 'items' => $items]);
    }

    public function checkout()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->redirect('/login');
        }

        $db = Database::getInstance();
        $cart = $db->query(
            'SELECT c.item_id, c.quantity, i.name, i.price
             FROM cart_items c
             INNER JOIN items i ON i.item_id = c.item_id
             WHERE c.user_id = ?',
            [Authenticator::userId()]
        );

        if (empty($cart)) {
            $this->flash('error', 'Your cart is empty.', 'warning');
            $this->redirect('/cart');
        }

        $subtotal = 0.0;
        foreach ($cart as $row) {
            $subtotal += ((float) $row['price']) * ((int) $row['quantity']);
        }
        $deliveryFee = $subtotal >= 200 ? 0.0 : 10.0;
        $discount = 0.0;
        $couponCode = trim((string) $this->getParam('coupon', ''));
        if ($couponCode !== '') {
            $coupon = $db->queryOne(
                'SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1',
                [$couponCode]
            );
            if ($coupon) {
                $discount = min($subtotal, ($subtotal * ((float) $coupon['discount_percent']) / 100));
            }
        }

        $total = max(0, $subtotal + $deliveryFee - $discount);
        $stripe = new StripeService();

        return $this->render('orders.checkout', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount' => $discount,
            'total' => $total,
            'coupon_code' => $couponCode,
            'stripe_enabled' => $stripe->isConfigured(),
            'stripe_publishable_key' => $stripe->getPublishableKey(),
        ]);
    }

    public function store()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->requireCsrf();

        $shippingAddress = trim((string) $this->post('shipping_address', ''));
        $shippingPhone = trim((string) $this->post('shipping_phone', ''));
        $paymentMethod = trim((string) $this->post('payment_method', 'stripe'));
        $couponCode = trim((string) $this->post('coupon_code', ''));

        if ($shippingAddress === '' || $shippingPhone === '') {
            $this->json(['error' => 'Shipping information is required'], 422);
        }

        $db = Database::getInstance();
        $cart = $db->query(
            'SELECT c.item_id, c.quantity, i.price, i.member_id
             FROM cart_items c
             INNER JOIN items i ON i.item_id = c.item_id
             WHERE c.user_id = ?',
            [Authenticator::userId()]
        );

        if (empty($cart)) {
            $this->json(['error' => 'Cart is empty'], 422);
        }

        $subtotal = 0.0;
        foreach ($cart as $row) {
            $subtotal += ((float) $row['price']) * ((int) $row['quantity']);
        }
        $deliveryFee = $subtotal >= 200 ? 0.0 : 10.0;
        $discount = 0.0;
        if ($couponCode !== '') {
            $coupon = $db->queryOne(
                'SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1',
                [$couponCode]
            );
            if ($coupon) {
                $discount = min($subtotal, ($subtotal * ((float) $coupon['discount_percent']) / 100));
            }
        }
        $total = max(0, $subtotal + $deliveryFee - $discount);

        $db->beginTransaction();
        try {
            $orderNumber = 'ORD-' . date('YmdHis') . '-' . Authenticator::userId();
            $db->execute(
                'INSERT INTO orders (user_id, order_number, status, payment_method, payment_status, total_price, shipping_address, shipping_phone, notes, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                [Authenticator::userId(), $orderNumber, 'pending', $paymentMethod, 'unpaid', $total, $shippingAddress, $shippingPhone, null]
            );
            $orderId = (int) $db->lastInsertId();

            foreach ($cart as $row) {
                $qty = (int) $row['quantity'];
                $price = (float) $row['price'];
                $db->execute(
                    'INSERT INTO order_items (order_id, item_id, seller_id, quantity, unit_price, total_price, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, NOW())',
                    [$orderId, $row['item_id'], $row['member_id'], $qty, $price, $qty * $price]
                );
            }

            $db->execute('DELETE FROM cart_items WHERE user_id = ?', [Authenticator::userId()]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            $this->json(['error' => 'Failed to create order'], 500);
        }

        $this->json(['success' => true, 'order_id' => $orderId, 'redirect' => '/orders/' . $orderId]);
    }

    public function cancel($id)
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->requireCsrf();

        $db = Database::getInstance();
        $order = $db->queryOne('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1', [(int) $id, Authenticator::userId()]);
        if (!$order) {
            $this->json(['error' => 'Order not found'], 404);
        }

        if (!in_array($order['status'], ['pending', 'confirmed'], true)) {
            $this->json(['error' => 'Order can no longer be cancelled'], 422);
        }

        $db->execute('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?', ['cancelled', $order['id']]);
        $this->json(['success' => true]);
    }

    public function adminList()
    {
        if (!Authenticator::isAdmin()) {
            $this->abort(403, 'Access denied');
        }

        $orders = Database::getInstance()->query(
            'SELECT o.*, u.username FROM orders o LEFT JOIN users u ON u.user_id = o.user_id ORDER BY o.created_at DESC'
        );
        return $this->render('admin.orders.index', ['orders' => $orders]);
    }

    public function updateStatus($id)
    {
        if (!Authenticator::isAdmin()) {
            $this->json(['error' => 'Forbidden'], 403);
        }
        $this->requireCsrf();

        $status = trim((string) $this->post('status', ''));
        $allowed = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            $this->json(['error' => 'Invalid status'], 422);
        }

        Database::getInstance()->execute('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?', [$status, (int) $id]);
        $this->json(['success' => true]);
    }

    public function stats()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $db = Database::getInstance();
        $rows = $db->query('SELECT status, COUNT(*) AS c FROM orders WHERE user_id = ? GROUP BY status', [Authenticator::userId()]);
        $stats = ['total_orders' => 0, 'pending' => 0, 'confirmed' => 0, 'shipped' => 0, 'delivered' => 0, 'cancelled' => 0];
        foreach ($rows as $row) {
            $stats['total_orders'] += (int) $row['c'];
            $stats[$row['status']] = (int) $row['c'];
        }
        $this->json($stats);
    }
}
