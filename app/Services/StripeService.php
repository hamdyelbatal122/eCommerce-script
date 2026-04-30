<?php

namespace ECommerce\App\Services;

class StripeService
{
    private string $secretKey;
    private string $publishableKey;
    private string $webhookSecret;
    private string $apiBase = 'https://api.stripe.com/v1';

    public function __construct()
    {
        $this->secretKey = getenv('STRIPE_SECRET_KEY') ?: '';
        $this->publishableKey = getenv('STRIPE_PUBLISHABLE_KEY') ?: '';
        $this->webhookSecret = getenv('STRIPE_WEBHOOK_SECRET') ?: '';
    }

    public function isConfigured(): bool
    {
        return $this->secretKey !== '' && $this->publishableKey !== '';
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    public function createPaymentIntent(int $amountInCents, string $currency = 'usd', array $metadata = []): array
    {
        $payload = [
            'amount' => $amountInCents,
            'currency' => strtolower($currency),
            'automatic_payment_methods[enabled]' => 'true',
        ];

        foreach ($metadata as $key => $value) {
            $payload["metadata[{$key}]"] = (string) $value;
        }

        return $this->request('POST', '/payment_intents', $payload);
    }

    public function verifyWebhookSignature(string $payload, string $signatureHeader): bool
    {
        if ($this->webhookSecret === '' || $signatureHeader === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($k !== null && $v !== null) {
                $parts[$k] = $v;
            }
        }

        if (!isset($parts['t'], $parts['v1'])) {
            return false;
        }

        $signedPayload = $parts['t'] . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        return hash_equals($expected, $parts['v1']);
    }

    private function request(string $method, string $endpoint, array $payload = []): array
    {
        if ($this->secretKey === '') {
            return ['ok' => false, 'error' => 'Stripe keys are not configured'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiBase . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        if (!empty($payload)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'error' => $curlError ?: 'Stripe request failed'];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400) {
            $message = $decoded['error']['message'] ?? 'Stripe API error';
            return ['ok' => false, 'error' => $message, 'status' => $httpCode];
        }

        return ['ok' => true, 'data' => is_array($decoded) ? $decoded : []];
    }
}
