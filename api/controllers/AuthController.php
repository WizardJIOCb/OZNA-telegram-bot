<?php

namespace OznaApi\Controllers;

use OznaApi\Services\JwtService;
use OznaApi\Services\PasswordService;

class AuthController
{
    private JwtService $jwt;
    private PasswordService $password;

    /**
     * Hardcoded super admin Telegram IDs — must match AdminService::isSuperAdmin() in the TG bot.
     */
    private const SUPER_ADMIN_TELEGRAM_IDS = [5261465966];

    public function __construct(JwtService $jwt, PasswordService $password)
    {
        $this->jwt      = $jwt;
        $this->password = $password;
    }

    /**
     * POST /auth/login
     * Body: { "username": "...", "password": "..." }
     */
    public function login(array $body): void
    {
        $username = trim($body['username'] ?? '');
        $pwd      = $body['password'] ?? '';

        if ($username === '' || $pwd === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Укажите логин и пароль'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $db = getDb();

        // Find user by username (case-insensitive)
        $stmt = $db->prepare(
            "SELECT id, username, password_hash, linked_telegram_id, access_level, fio, functional_direction, is_active
             FROM pwa_users
             WHERE LOWER(username) = LOWER(?) 
             LIMIT 1"
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Неверный логин или пароль'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$user['is_active']) {
            http_response_code(403);
            echo json_encode(['error' => 'Аккаунт неактивен. Обратитесь к администратору'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$this->password->verify($pwd, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Неверный логин или пароль'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Build tokens
        // Resolve effective access_level: hardcoded superadmins > ozna_data > pwa_users
        $accessLevel = $this->resolveAccessLevel($db, $user);

        $tokenPayload = [
            'pwa_user_id'  => (int) $user['id'],
            'telegram_id'  => $user['linked_telegram_id'] ? (int) $user['linked_telegram_id'] : null,
            'access_level' => $accessLevel,
            'fio'          => $user['fio'] ?? '',
            'fn'           => $user['functional_direction'] ?? '',
        ];

        $accessToken  = $this->jwt->createAccessToken($tokenPayload);
        $refreshToken = $this->jwt->createRefreshToken((int) $user['id']);

        echo json_encode([
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'user' => [
                'fio'                  => $user['fio'],
                'functional_direction' => $user['functional_direction'],
                'access_level'         => $accessLevel,
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /auth/refresh
     * Body: { "refresh_token": "..." }
     */
    public function refresh(array $body): void
    {
        $token = $body['refresh_token'] ?? '';
        if ($token === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Refresh token required'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $payload = $this->jwt->decode($token);
        if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired refresh token'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $pwaUserId = (int) ($payload['pwa_user_id'] ?? 0);
        if ($pwaUserId === 0) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid refresh token payload'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Reload user data (access_level might have changed)
        $db   = getDb();
        $stmt = $db->prepare(
            "SELECT id, linked_telegram_id, access_level, fio, functional_direction, is_active
             FROM pwa_users WHERE id = ?"
        );
        $stmt->bind_param('i', $pwaUserId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !$user['is_active']) {
            http_response_code(401);
            echo json_encode(['error' => 'User not found or inactive'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Resolve effective access_level: hardcoded superadmins > ozna_data > pwa_users
        $accessLevel = $this->resolveAccessLevel($db, $user);

        $tokenPayload = [
            'pwa_user_id'  => (int) $user['id'],
            'telegram_id'  => $user['linked_telegram_id'] ? (int) $user['linked_telegram_id'] : null,
            'access_level' => $accessLevel,
            'fio'          => $user['fio'] ?? '',
            'fn'           => $user['functional_direction'] ?? '',
        ];

        $accessToken  = $this->jwt->createAccessToken($tokenPayload);
        $refreshToken = $this->jwt->createRefreshToken($pwaUserId);

        echo json_encode([
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'user' => [
                'fio'                  => $user['fio'],
                'functional_direction' => $user['functional_direction'],
                'access_level'         => $accessLevel,
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Resolve effective access_level for a pwa_users row.
     * Priority: hardcoded superadmin IDs > ozna_data > pwa_users.
     * Also syncs the resolved level back to pwa_users when it differs.
     */
    private function resolveAccessLevel(\mysqli $db, array $user): int
    {
        $tgId = $user['linked_telegram_id'] ? (int) $user['linked_telegram_id'] : 0;

        // 1. Hardcoded superadmin check (matches AdminService::isSuperAdmin in TG bot)
        if ($tgId && in_array($tgId, self::SUPER_ADMIN_TELEGRAM_IDS, true)) {
            $this->syncPwaAccessLevel($db, (int) $user['id'], 4);
            return 4;
        }

        $accessLevel = (int) $user['access_level'];

        // 2. Check ozna_data (TG bot source of truth)
        if ($tgId) {
            $stmt = $db->prepare("SELECT data FROM ozna_data WHERE user_id = ?");
            $stmt->bind_param('i', $tgId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row) {
                $oznaData = json_decode($row['data'], true);
                $oznaLevel = (int) ($oznaData['access_level'] ?? 0);
                if ($oznaLevel > $accessLevel) {
                    $accessLevel = $oznaLevel;
                }
            }
        }

        // Sync back to pwa_users if changed
        if ($accessLevel !== (int) $user['access_level']) {
            $this->syncPwaAccessLevel($db, (int) $user['id'], $accessLevel);
        }

        return $accessLevel;
    }

    private function syncPwaAccessLevel(\mysqli $db, int $pwaUserId, int $level): void
    {
        $stmt = $db->prepare("UPDATE pwa_users SET access_level = ? WHERE id = ?");
        $stmt->bind_param('ii', $level, $pwaUserId);
        $stmt->execute();
        $stmt->close();
    }
}
