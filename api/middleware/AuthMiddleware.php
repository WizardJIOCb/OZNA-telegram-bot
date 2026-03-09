<?php

namespace OznaApi\Middleware;

use OznaApi\Services\JwtService;

/**
 * Extracts and validates JWT from the Authorization header.
 * On success, populates $GLOBALS['auth_user'] with the token payload.
 */
class AuthMiddleware
{
    private JwtService $jwt;

    public function __construct(JwtService $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Run the middleware. Returns true if authenticated, false otherwise.
     * On failure, sends a 401 JSON response.
     */
    public function handle(): bool
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (empty($header) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            $this->deny('Missing or invalid Authorization header');
            return false;
        }

        $payload = $this->jwt->decode($m[1]);

        if ($payload === null) {
            $this->deny('Invalid or expired token');
            return false;
        }

        if (($payload['type'] ?? '') !== 'access') {
            $this->deny('Invalid token type');
            return false;
        }

        $GLOBALS['auth_user'] = $payload;
        return true;
    }

    /**
     * Require a minimum access level. Call AFTER handle().
     */
    public function requireLevel(int $minLevel): bool
    {
        $level = $GLOBALS['auth_user']['access_level'] ?? 0;
        if ($level < $minLevel) {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient access level'], JSON_UNESCAPED_UNICODE);
            return false;
        }
        return true;
    }

    private function deny(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    }
}
