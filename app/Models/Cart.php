<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;

class Cart extends BaseModel
{
    protected string $table = 'cart_items';
    protected array $fillable = ['user_id', 'item_id', 'quantity'];

    /**
     * Add item to cart
     */
    public static function addItem(int $userId, int $itemId, int $quantity = 1): bool
    {
        $existing = self::where(['user_id' => $userId, 'item_id' => $itemId])->first();

        if ($existing) {
            return self::where('id', $existing['id'])
                ->update(['quantity' => $existing['quantity'] + $quantity]);
        }

        return (bool) self::create([
            'user_id' => $userId,
            'item_id' => $itemId,
            'quantity' => $quantity
        ]);
    }

    /**
     * Update item quantity
     */
    public static function updateQuantity(int $userId, int $itemId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return self::removeItem($userId, $itemId);
        }

        return self::where(['user_id' => $userId, 'item_id' => $itemId])
            ->update(['quantity' => $quantity]);
    }

    /**
     * Remove item from cart
     */
    public static function removeItem(int $userId, int $itemId): bool
    {
        return self::where(['user_id' => $userId, 'item_id' => $itemId])->delete();
    }

    /**
     * Get user's cart
     */
    public static function getUserCart(int $userId): array
    {
        $cart = self::where('user_id', $userId)->get() ?? [];

        // Enhance with item details
        foreach ($cart as &$item) {
            $product = Item::find($item['item_id']);
            if ($product) {
                $item['product'] = $product;
                $item['subtotal'] = $product['Price'] * $item['quantity'];
            }
        }

        return $cart;
    }

    /**
     * Get cart total
     */
    public static function getCartTotal(int $userId): float
    {
        $cart = self::getUserCart($userId);
        $total = 0;

        foreach ($cart as $item) {
            if (isset($item['subtotal'])) {
                $total += $item['subtotal'];
            }
        }

        return $total;
    }

    /**
     * Get cart count
     */
    public static function getCartCount(int $userId): int
    {
        return self::where('user_id', $userId)->count() ?? 0;
    }

    /**
     * Clear cart
     */
    public static function clear(int $userId): bool
    {
        return self::where('user_id', $userId)->delete();
    }
}
