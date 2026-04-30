<?php

namespace ECommerce\App\Controllers\Admin;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Database;

class CouponController extends BaseController
{
    public function index()
    {
        if (!Authenticator::isAdmin()) {
            $this->abort(403, 'Access denied');
        }

        $coupons = Database::getInstance()->query('SELECT * FROM coupons ORDER BY created_at DESC');
        return $this->render('admin.coupons.index', ['coupons' => $coupons]);
    }

    public function store()
    {
        if (!Authenticator::isAdmin()) {
            $this->json(['error' => 'Forbidden'], 403);
        }
        $this->requireCsrf();

        $code = strtoupper(trim((string) $this->post('code', '')));
        $discountPercent = (float) $this->post('discount_percent', 0);
        $expiresAt = trim((string) $this->post('expires_at', ''));
        if ($code === '' || $discountPercent <= 0 || $discountPercent > 100) {
            $this->json(['error' => 'Invalid coupon data'], 422);
        }

        Database::getInstance()->execute(
            'INSERT INTO coupons (code, discount_percent, is_active, expires_at, created_at) VALUES (?, ?, 1, ?, NOW())',
            [$code, $discountPercent, $expiresAt !== '' ? $expiresAt : null]
        );
        $this->json(['success' => true]);
    }
}
