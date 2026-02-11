<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * JWT Helper (HS256)
 * --------------------------------------------------
 * - Generates access & refresh tokens
 * - Validates JWT tokens
 * - No external libraries
 */

class JWT
{
    /**
     * Base64 URL-safe encoding
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(
            strtr(base64_encode($data), '+/', '-_'),
            '='
        );
    }

    /**
     * Base64 URL-safe decoding
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(
            strtr($data, '-_', '+/')
        );
    }

    /**
     * Generate HMAC SHA256 signature
     */
    private static function sign(string $header, string $payload): string
    {
        return self::base64UrlEncode(
            hash_hmac(
                'sha256',
                "{$header}.{$payload}",
                JWT_SECRET,
                true
            )
        );
    }

    /**
     * Generate Access Token
     */
    public static function generateAccessToken(array $user): string
    {
        return self::generateToken(
            $user,
            'access',
            ACCESS_TOKEN_EXP
        );
    }

    /**
     * Generate Refresh Token
     */
    public static function generateRefreshToken(array $user): string
    {
        return self::generateToken(
            $user,
            'refresh',
            REFRESH_TOKEN_EXP
        );
    }

    /**
     * Core token generator
     */
    private static function generateToken(
        array $user,
        string $type,
        int $ttl
    ): string {
        $header = self::base64UrlEncode(
            json_encode([
                'typ' => 'JWT',
                'alg' => 'HS256',
            ])
        );

        $payload = self::base64UrlEncode(
            json_encode([
                'user_id' => $user['user_id'],
                'email'   => $user['email'],
                'type'    => $type,
                'iat'     => time(),
                'exp'     => time() + $ttl,
            ])
        );

        $signature = self::sign($header, $payload);

        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * Validate JWT Token
     */
    public static function validate(
        string $token,
        string $expectedType = 'access'
    ): array|false {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        // Verify signature
        if (!hash_equals(self::sign($header, $payload), $signature)) {
            return false;
        }

        $payloadData = json_decode(
            self::base64UrlDecode($payload),
            true
        );

        if (!$payloadData) {
            return false;
        }

        // Validate token type
        if (($payloadData['type'] ?? '') !== $expectedType) {
            return false;
        }

        // Validate expiration
        if (($payloadData['exp'] ?? 0) < time()) {
            return false;
        }

        return $payloadData;
    }
}
