<?php

namespace OznaApi\Services;

/**
 * Minimal JWT (HS256) implementation — no external dependencies.
 */
class JwtService
{
    private string $secret;
    private string $issuer;
    private int $accessTtl;
    private int $refreshTtl;

    public function __construct(array $config)
    {
        $this->secret     = $config['secret'];
        $this->issuer     = $config['issuer'] ?? 'ozna_pwa';
        $this->accessTtl  = $config['access_token_ttl'] ?? 3600;
        $this->refreshTtl = $config['refresh_token_ttl'] ?? 2592000;
    }

    /**
     * Create an access token.
     */
    public function createAccessToken(array $payload): string
    {
        $payload['iss'] = $this->issuer;
        $payload['iat'] = time();
        $payload['exp'] = time() + $this->accessTtl;
        $payload['type'] = 'access';
        return $this->encode($payload);
    }

    /**
     * Create a refresh token.
     */
    public function createRefreshToken(int $pwaUserId): string
    {
        $payload = [
            'iss'         => $this->issuer,
            'iat'         => time(),
            'exp'         => time() + $this->refreshTtl,
            'type'        => 'refresh',
            'pwa_user_id' => $pwaUserId,
        ];
        return $this->encode($payload);
    }

    /**
     * Decode and validate a JWT. Returns payload array or null on failure.
     */
    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $expectedSig = $this->base64UrlEncode(
            hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $this->secret, true)
        );

        if (!hash_equals($expectedSig, $signatureB64)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadB64), true);
        if (!$payload) {
            return null;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    // ── Internal ────────────────────────────────────────────

    private function encode(array $payload): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body   = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $sig    = $this->base64UrlEncode(hash_hmac('sha256', "{$header}.{$body}", $this->secret, true));
        return "{$header}.{$body}.{$sig}";
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
