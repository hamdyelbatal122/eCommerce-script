<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;

class ApiToken extends BaseModel
{
    protected string $table = 'api_tokens';
    protected array $fillable = ['user_id', 'name', 'token', 'last_used_at', 'revoked_at', 'expires_at'];

    /**
     * Generate API token
     */
    public static function generateToken(): string
    {
        return 'api_' . bin2hex(random_bytes(32));
    }

    /**
     * Create token for user
     */
    public static function createForUser(int $userId, string $name, ?int $expiryDays = null): self|false
    {
        $token = self::generateToken();
        $expiresAt = $expiryDays ? date('Y-m-d H:i:s', strtotime("+{$expiryDays} days")) : null;

        return self::create([
            'user_id' => $userId,
            'name' => $name,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Verify and get token
     */
    public static function verify(string $token): ?array
    {
        $apiToken = self::where('token', $token)->first();

        if (!$apiToken || $apiToken['revoked_at']) {
            return null;
        }

        if ($apiToken['expires_at'] && strtotime($apiToken['expires_at']) < time()) {
            return null;
        }

        // Update last used time
        self::where('id', $apiToken['id'])
            ->update(['last_used_at' => 'NOW()']);

        return $apiToken;
    }

    /**
     * Find user's tokens
     */
    public static function getUserTokens(int $userId): array
    {
        return self::where('user_id', $userId)
            ->where('revoked_at', null)
            ->orderBy('created_at', 'DESC')
            ->get() ?? [];
    }

    /**
     * Revoke token
     */
    public static function revokeToken(int $tokenId, int $userId): bool
    {
        return self::where(['id' => $tokenId, 'user_id' => $userId])
            ->update(['revoked_at' => 'NOW()']);
    }

    /**
     * Clean expired tokens
     */
    public static function cleanExpired(): int
    {
        $stmt = self::connection()->prepare(
            "DELETE FROM {$this->table} WHERE expires_at < NOW() AND expires_at IS NOT NULL"
        );
        return $stmt->rowCount();
    }
}
