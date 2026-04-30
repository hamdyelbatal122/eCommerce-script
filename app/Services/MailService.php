<?php

namespace ECommerce\App\Services;

class MailService
{
    private $smtpHost;
    private $smtpPort;
    private $smtpUser;
    private $smtpPass;
    private $fromEmail;
    private $fromName;

    public function __construct(array $config)
    {
        $this->smtpHost = $config['smtp_host'] ?? 'localhost';
        $this->smtpPort = $config['smtp_port'] ?? 587;
        $this->smtpUser = $config['smtp_user'] ?? '';
        $this->smtpPass = $config['smtp_pass'] ?? '';
        $this->fromEmail = $config['from_email'] ?? 'noreply@example.com';
        $this->fromName = $config['from_name'] ?? 'ECommerce Store';
    }

    /**
     * Send email (using mail() for now, can be enhanced with SMTP)
     */
    public function send(string $to, string $subject, string $body, ?array $headers = null): bool
    {
        // Prepare headers
        $emailHeaders = [
            'From' => "{$this->fromName} <{$this->fromEmail}>",
            'Reply-To' => $this->fromEmail,
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Mailer' => 'ECommerce Store'
        ];

        if ($headers) {
            $emailHeaders = array_merge($emailHeaders, $headers);
        }

        // Build header string
        $headerString = '';
        foreach ($emailHeaders as $key => $value) {
            $headerString .= "{$key}: {$value}\r\n";
        }

        // Send email
        return mail($to, $subject, $body, $headerString);
    }

    /**
     * Queue email for later sending
     */
    public function queue(string $to, string $subject, string $body, ?array $headers = null): bool
    {
        // This would use a database queue in production
        return $this->send($to, $subject, $body, $headers);
    }

    /**
     * Send verification email
     */
    public function sendVerification(string $email, string $token, string $verificationUrl): bool
    {
        $subject = 'Verify Your Email Address';
        $body = "
            <html>
            <body>
                <h2>Email Verification</h2>
                <p>Please click the link below to verify your email:</p>
                <a href='{$verificationUrl}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                    Verify Email
                </a>
                <p>Or copy this link: {$verificationUrl}</p>
                <p>This link will expire in 24 hours.</p>
            </body>
            </html>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset(string $email, string $token, string $resetUrl): bool
    {
        $subject = 'Reset Your Password';
        $body = "
            <html>
            <body>
                <h2>Password Reset Request</h2>
                <p>Click the link below to reset your password:</p>
                <a href='{$resetUrl}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                    Reset Password
                </a>
                <p>Or copy this link: {$resetUrl}</p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, ignore this email.</p>
            </body>
            </html>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Send order confirmation
     */
    public function sendOrderConfirmation(string $email, array $order): bool
    {
        $subject = "Order Confirmation - {$order['order_number']}";
        $itemsHtml = '';

        foreach ($order['items'] as $item) {
            $itemsHtml .= "
                <tr>
                    <td>{$item['name']}</td>
                    <td>{$item['quantity']}</td>
                    <td>${$item['unit_price']}</td>
                    <td>${$item['total_price']}</td>
                </tr>
            ";
        }

        $body = "
            <html>
            <body>
                <h2>Order Confirmation</h2>
                <p>Thank you for your order!</p>
                <p><strong>Order Number:</strong> {$order['order_number']}</p>
                <p><strong>Date:</strong> {$order['created_at']}</p>
                <p><strong>Status:</strong> {$order['status']}</p>
                
                <h3>Items:</h3>
                <table border='1' cellpadding='10'>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                    {$itemsHtml}
                </table>
                
                <p><strong>Total:</strong> ${$order['total_price']}</p>
                <p>We'll notify you when your order ships!</p>
            </body>
            </html>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Send notification email
     */
    public function sendNotification(string $email, string $title, string $message): bool
    {
        $subject = $title;
        $body = "
            <html>
            <body>
                <h2>{$title}</h2>
                <p>{$message}</p>
            </body>
            </html>
        ";

        return $this->send($email, $subject, $body);
    }
}
