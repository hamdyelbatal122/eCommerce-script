<?php

namespace ECommerce\App\Services;

class DhlTrackingService
{
    public function buildTrackingUrl(string $trackingNumber): string
    {
        return 'https://www.dhl.com/global-en/home/tracking.html?tracking-id=' . urlencode($trackingNumber);
    }
}
