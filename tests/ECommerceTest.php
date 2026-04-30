<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use ECommerce\App\Controllers\PasswordController;
use ECommerce\App\Controllers\PaymentController;
use ECommerce\App\Controllers\OrderController;
use ECommerce\App\Models\ApiToken;
use ECommerce\App\Services\FileCacheService;

class ECommerceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testCheckoutControllerExists(): void
    {
        $this->assertTrue(class_exists(OrderController::class));
    }

    public function testPaymentControllerExists(): void
    {
        $this->assertTrue(class_exists(PaymentController::class));
    }

    public function testPasswordResetControllerExists(): void
    {
        $this->assertTrue(class_exists(PasswordController::class));
    }

    public function testGenerateToken(): void
    {
        $token = ApiToken::generateToken();
        $this->assertStringStartsWith('api_', $token);
    }

    public function testTokenFormat(): void
    {
        $token = ApiToken::generateToken();
        $this->assertGreaterThan(10, strlen($token));
    }

    public function testFileCacheServiceExists(): void
    {
        $this->assertTrue(class_exists(FileCacheService::class));
    }
}
