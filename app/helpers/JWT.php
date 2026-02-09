<?php

/**
 * JWT Helper
 * Manual HS256 JWT generation & validation (No libraries)
 */

class JWT
{
    /**
     * Generate JWT Token
     */
    public static function generate(array $payload): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRY;

        $base64Header  = self::base64UrlEncode(json_encode($header));
        $base64Payload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $base64Header . '.' . $base64Payload,
            JWT_SECRET,
            true
        );

        $base64Signature = self::base64UrlEncode($signature);

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    /**
     * Validate JWT Token
     */
    public static function validate(string $token): array|false
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        // Recreate signature
        $expectedSignature = self::base64UrlEncode(
            hash_hmac(
                'sha256',
                $base64Header . '.' . $base64Payload,
                JWT_SECRET,
                true
            )
        );

        // Compare signatures safely
        if (!hash_equals($expectedSignature, $base64Signature)) {
            return false;
        }

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($base64Payload), true);

        if (!$payload || !isset($payload['exp'])) {
            return false;
        }

        // Check expiry
        if ($payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * Base64 URL Encode
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL Decode
     */
    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
