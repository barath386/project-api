<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * JWT Helper (HS256)
 * --------------------------------------------------
 * ✔ Access Token & Refresh Token
 * ✔ Manual JWT (no libraries)
 * ✔ Secure signature validation
 * ✔ Token expiry & type validation
 */

class JWT
{
    /* ---------------------------------------------
     * Base64 URL Encode
     * ------------------------------------------- */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(
            strtr(base64_encode($data), '+/', '-_'),
            '='
        );
    }

    /* ---------------------------------------------
     * Base64 URL Decode
     * ------------------------------------------- */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(
            strtr($data, '-_', '+/')
        );
    }

    /* ---------------------------------------------
     * Generate Signature (HS256)
     * ------------------------------------------- */
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

    /* ---------------------------------------------
     * Generate Access Token
     * ------------------------------------------- */
    public static function generateAccessToken(array $user): string
    {
        return self::generateToken(
            $user,
            'access',
            ACCESS_TOKEN_EXP
        );
    }

    /* ---------------------------------------------
     * Generate Refresh Token
     * ------------------------------------------- */
    public static function generateRefreshToken(array $user): string
    {
        return self::generateToken(
            $user,
            'refresh',
            REFRESH_TOKEN_EXP
        );
    }

    /* ---------------------------------------------
     * Core Token Generator
     * ------------------------------------------- */
    private static function generateToken(
        array $user,
        string $type,
        int $ttl
    ): string {
        $header = self::base64UrlEncode(
            json_encode([
                'typ' => 'JWT',
                'alg' => 'HS256'
            ])
        );

        $payload = self::base64UrlEncode(
            json_encode([
                'user_id' => $user['user_id'],
                'email'   => $user['email'],
                'type'    => $type,
                'iat'     => time(),
                'exp'     => time() + $ttl
            ])
        );

        $signature = self::sign($header, $payload);

        return "{$header}.{$payload}.{$signature}";
    }

    /* ---------------------------------------------
     * Validate Token
     * ------------------------------------------- */
    public static function validate(
        string $token,
        string $expectedType = 'access'
    ): array|false {
        $parts = explode('.', $token);

        // Must have 3 parts
        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        // Signature verification
        $validSignature = self::sign($header, $payload);

        if (!hash_equals($validSignature, $signature)) {
            return false;
        }

        // Decode payload
        $payloadData = json_decode(
            self::base64UrlDecode($payload),
            true
        );

        if (!$payloadData) {
            return false;
        }

        // Token type check
        if (($payloadData['type'] ?? '') !== $expectedType) {
            return false;
        }

        // Expiry check
        if (($payloadData['exp'] ?? 0) < time()) {
            return false;
        }

        return $payloadData;
    }
}
