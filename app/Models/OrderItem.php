<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;

class OrderItem extends BaseModel
{
    protected string $table = 'order_items';
    protected array $fillable = ['order_id', 'item_id', 'seller_id', 'quantity', 'unit_price', 'total_price'];

    /**
     * Get item details
     */
    public function getItemDetails(): ?array
    {
        return Item::find($this->item_id ?? null);
    }

    /**
     * Get seller details
     */
    public function getSellerDetails(): ?array
    {
        return User::find($this->seller_id ?? null);
    }
}
