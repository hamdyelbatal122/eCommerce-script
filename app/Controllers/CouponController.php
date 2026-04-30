<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Database;

class CouponController extends BaseController
{
    public function validate()
    {
        $code = strtoupper(trim((string) $this->getParam('code', '')));
        $subtotal = (float) $this->getParam('subtotal', 0);
        if ($code === '' || $subtotal <= 0) {
            $this->json(['valid' => false, 'message' => 'Invalid coupon request'], 422);
        }

        $coupon = Database::getInstance()->queryOne(
            'SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1',
            [$code]
        );
        if (!$coupon) {
            $this->json(['valid' => false, 'message' => 'Coupon not found or expired']);
        }

        $discount = min($subtotal, $subtotal * ((float) $coupon['discount_percent'] / 100));
        $this->json([
            'valid' => true,
            'code' => $coupon['code'],
            'discount_percent' => (float) $coupon['discount_percent'],
            'discount_amount' => round($discount, 2),
        ]);
    }
}
