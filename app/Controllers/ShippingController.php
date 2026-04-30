<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Database;
use ECommerce\App\Services\DhlTrackingService;

class ShippingController extends BaseController
{
    public function track($orderId)
    {
        if (!Authenticator::isLoggedIn()) {
            $this->redirect('/login');
        }

        $order = Database::getInstance()->queryOne(
            'SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1',
            [(int) $orderId, Authenticator::userId()]
        );
        if (!$order) {
            $this->abort(404, 'Order not found');
        }

        $trackingNumber = (string) ($order['tracking_number'] ?? '');
        if ($trackingNumber === '') {
            $this->abort(404, 'Tracking number is not available yet');
        }

        $url = (new DhlTrackingService())->buildTrackingUrl($trackingNumber);
        $this->redirect($url);
    }
}
