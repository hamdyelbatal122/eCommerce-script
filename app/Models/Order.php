<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;

class Order extends BaseModel
{
    protected string $table = 'orders';
    protected array $fillable = [
        'user_id', 'order_number', 'status', 'payment_method',
        'payment_status', 'total_price', 'shipping_address', 'shipping_phone', 'notes'
    ];

    /**
     * Create order from cart
     */
    public static function createFromCart(
        int $userId,
        string $shippingAddress,
        string $shippingPhone,
        string $paymentMethod = 'cash_on_delivery',
        ?string $notes = null
    ): self|false {
        $cart = Cart::getUserCart($userId);

        if (empty($cart)) {
            return false;
        }

        $totalPrice = Cart::getCartTotal($userId);
        $orderNumber = 'ORD-' . date('YmdHis') . '-' . $userId;

        $order = self::create([
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'payment_status' => 'unpaid',
            'total_price' => $totalPrice,
            'shipping_address' => $shippingAddress,
            'shipping_phone' => $shippingPhone,
            'notes' => $notes
        ]);

        if (!$order) {
            return false;
        }

        // Create order items
        foreach ($cart as $cartItem) {
            OrderItem::create([
                'order_id' => $order['id'],
                'item_id' => $cartItem['item_id'],
                'seller_id' => $cartItem['product']['Member_ID'],
                'quantity' => $cartItem['quantity'],
                'unit_price' => $cartItem['product']['Price'],
                'total_price' => $cartItem['subtotal']
            ]);
        }

        // Clear cart
        Cart::clear($userId);

        return $order;
    }

    /**
     * Get user's orders
     */
    public static function getUserOrders(int $userId, string $status = null): array
    {
        $query = self::where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'DESC')->get() ?? [];
    }

    /**
     * Get single order with items
     */
    public static function getWithItems(int $orderId): ?array
    {
        $order = self::find($orderId);

        if (!$order) {
            return null;
        }

        $order['items'] = OrderItem::where('order_id', $orderId)->get() ?? [];

        return $order;
    }

    /**
     * Update order status
     */
    public static function updateStatus(int $orderId, string $status): bool
    {
        $updates = ['status' => $status];

        if ($status === 'shipped') {
            $updates['shipped_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'delivered') {
            $updates['delivered_at'] = date('Y-m-d H:i:s');
        }

        return self::where('id', $orderId)->update($updates);
    }

    /**
     * Get seller's orders
     */
    public static function getSellerOrders(int $sellerId, string $status = null): array
    {
        $table = (new self())->table;
        $query = "
            SELECT o.*, 
                   COUNT(oi.id) as item_count,
                   SUM(oi.quantity) as total_items
            FROM {$table} o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE oi.seller_id = :seller_id
        ";

        if ($status) {
            $query .= " AND o.status = :status";
        }

        $query .= " GROUP BY o.id ORDER BY o.created_at DESC";

        $stmt = self::connection()->prepare($query);
        $stmt->bindParam(':seller_id', $sellerId, \PDO::PARAM_INT);

        if ($status) {
            $stmt->bindParam(':status', $status);
        }

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?? [];
    }

    /**
     * Get order statistics
     */
    public static function getStats(int $userId): array
    {
        $orders = self::getUserOrders($userId);

        return [
            'total_orders' => count($orders),
            'pending' => count(array_filter($orders, fn($o) => $o['status'] === 'pending')),
            'shipped' => count(array_filter($orders, fn($o) => $o['status'] === 'shipped')),
            'delivered' => count(array_filter($orders, fn($o) => $o['status'] === 'delivered')),
            'cancelled' => count(array_filter($orders, fn($o) => $o['status'] === 'cancelled')),
            'total_spent' => array_sum(array_map(fn($o) => $o['total_price'], $orders))
        ];
    }
}
