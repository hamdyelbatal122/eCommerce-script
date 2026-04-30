<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;

class EmailVerification extends BaseModel
{
    protected string $table = 'email_verifications';
    protected array $fillable = ['user_id', 'email', 'token', 'verified_at', 'expires_at'];

    /**
     * Generate a verification token
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Create verification for user
     */
    public static function createForUser(int $userId, string $email): self|false
    {
        $token = self::generateToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Delete existing unverified tokens
        return self::query()
            ->delete()
            ->where(['user_id' => $userId, 'verified_at' => null])
            ->execute() 
            ? self::create([
                'user_id' => $userId,
                'email' => $email,
                'token' => $token,
                'expires_at' => $expiresAt
            ])
            : false;
    }

    /**
     * Find by token
     */
    public static function findByToken(string $token): ?array
    {
        return self::where('token', $token)->first();
    }

    /**
     * Verify email token
     */
    public static function verify(string $token): bool
    {
        $verification = self::findByToken($token);

        if (!$verification) {
            return false;
        }

        // Check if token expired
        if (strtotime($verification['expires_at']) < time()) {
            return false;
        }

        // Update verification status
        return self::where('id', $verification['id'])
            ->update(['verified_at' => 'NOW()']);
    }

    /**
     * Check if email is verified
     */
    public static function isEmailVerified(int $userId): bool
    {
        $verification = self::where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->first();

        return $verification && !is_null($verification['verified_at']);
    }

    /**
     * Get pending verifications
     */
    public static function getPending(): array
    {
        return self::where('verified_at', null)
            ->orderBy('created_at', 'DESC')
            ->get() ?? [];
    }

    /**
     * Clean expired tokens
     */
    public static function cleanExpired(): int
    {
        $stmt = self::connection()->prepare(
            "DELETE FROM {$this->table} WHERE expires_at < NOW()"
        );
        return $stmt->rowCount();
    }
}
