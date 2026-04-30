<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Database;
use ECommerce\App\Services\StripeService;

class PaymentController extends BaseController
{
    public function config()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $stripe = new StripeService();
        $this->json([
            'enabled' => $stripe->isConfigured(),
            'publishableKey' => $stripe->getPublishableKey(),
        ]);
    }

    public function createIntent()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $amount = (float) $this->post('amount', 0);
        $orderId = (int) $this->post('order_id', 0);
        if ($amount <= 0) {
            $this->json(['error' => 'Invalid amount'], 422);
        }

        $stripe = new StripeService();
        $result = $stripe->createPaymentIntent(
            (int) round($amount * 100),
            'usd',
            [
                'user_id' => (string) Authenticator::userId(),
                'order_id' => (string) $orderId,
            ]
        );

        if (!$result['ok']) {
            $this->json(['error' => $result['error']], 400);
        }

        $intent = $result['data'];
        $this->savePaymentIntent($orderId, $intent, $amount);

        $this->json([
            'success' => true,
            'clientSecret' => $intent['client_secret'] ?? null,
            'paymentIntentId' => $intent['id'] ?? null,
        ]);
    }

    public function webhook()
    {
        $payload = file_get_contents('php://input') ?: '';
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        $stripe = new StripeService();
        if (!$stripe->verifyWebhookSignature($payload, $signature)) {
            $this->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        if (!is_array($event)) {
            $this->json(['error' => 'Invalid payload'], 400);
        }

        $eventType = $event['type'] ?? 'unknown';
        $object = $event['data']['object'] ?? [];
        $paymentIntentId = $object['id'] ?? null;
        if ($paymentIntentId) {
            $this->storeWebhookEvent($paymentIntentId, $eventType, $payload);
            if ($eventType === 'payment_intent.succeeded') {
                $this->markPaymentAsPaid($paymentIntentId);
            }
            if ($eventType === 'payment_intent.payment_failed') {
                $this->markPaymentAsFailed($paymentIntentId);
            }
        }

        $this->json(['received' => true]);
    }

    public function adminIndex()
    {
        if (!Authenticator::isAdmin()) {
            $this->abort(403, 'Access denied');
        }

        $payments = Database::getInstance()->query(
            'SELECT p.*, u.username, o.order_number
             FROM payments p
             LEFT JOIN users u ON u.user_id = p.user_id
             LEFT JOIN orders o ON o.id = p.order_id
             ORDER BY p.created_at DESC
             LIMIT 300'
        );

        return $this->render('admin.payments.index', ['payments' => $payments]);
    }

    public function adminUpdateStatus($id)
    {
        if (!Authenticator::isAdmin()) {
            $this->json(['error' => 'Forbidden'], 403);
        }
        $this->requireCsrf();

        $status = trim((string) $this->post('status', ''));
        if ($status === '') {
            $this->json(['error' => 'Status is required'], 422);
        }

        Database::getInstance()->execute(
            'UPDATE payments SET status = ?, updated_at = NOW() WHERE id = ?',
            [$status, (int) $id]
        );

        $this->json(['success' => true]);
    }

    private function savePaymentIntent(int $orderId, array $intent, float $amount): void
    {
        $db = Database::getInstance();
        $db->execute(
            'INSERT INTO payments (order_id, user_id, provider, provider_payment_id, amount, currency, status, raw_payload, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $orderId ?: null,
                Authenticator::userId(),
                'stripe',
                $intent['id'] ?? '',
                $amount,
                strtoupper($intent['currency'] ?? 'USD'),
                $intent['status'] ?? 'requires_payment_method',
                json_encode($intent),
            ]
        );
    }

    private function storeWebhookEvent(string $paymentIntentId, string $eventType, string $payload): void
    {
        $db = Database::getInstance();
        $db->execute(
            'INSERT INTO payment_events (provider, provider_payment_id, event_type, payload, created_at)
             VALUES (?, ?, ?, ?, NOW())',
            ['stripe', $paymentIntentId, $eventType, $payload]
        );
    }

    private function markPaymentAsPaid(string $paymentIntentId): void
    {
        $db = Database::getInstance();
        $db->execute(
            'UPDATE payments SET status = ?, updated_at = NOW() WHERE provider = ? AND provider_payment_id = ?',
            ['succeeded', 'stripe', $paymentIntentId]
        );
    }

    private function markPaymentAsFailed(string $paymentIntentId): void
    {
        $db = Database::getInstance();
        $db->execute(
            'UPDATE payments SET status = ?, updated_at = NOW() WHERE provider = ? AND provider_payment_id = ?',
            ['failed', 'stripe', $paymentIntentId]
        );
    }
}
