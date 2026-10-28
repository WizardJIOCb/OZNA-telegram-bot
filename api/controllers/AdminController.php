<?php

namespace OznaApi\Controllers;

use OznaApi\Services\PasswordService;

/**
 * Admin endpoints for superadmin user management.
 * All methods require access_level >= 4.
 */
class AdminController
{
    private PasswordService $passwordService;

    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }

    /**
     * GET /admin/users
     * Returns list of PWA users (for password reset).
     */
    public function listUsers(): void
    {
        if (!$this->requireSuperAdmin()) {
            return;
        }

        $db = getDb();
        $result = $db->query(
            "SELECT id, username, fio, linked_telegram_id, access_level, functional_direction, is_active
             FROM pwa_users
             WHERE is_active = 1
             ORDER BY fio ASC, username ASC"
        );

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'id'                   => (int) $row['id'],
                'username'             => $row['username'],
                'fio'                  => $row['fio'] ?: $row['username'],
                'linked_telegram_id'   => $row['linked_telegram_id'] ? (int) $row['linked_telegram_id'] : null,
                'access_level'         => (int) $row['access_level'],
                'functional_direction' => $row['functional_direction'] ?: '',
            ];
        }

        echo json_encode(['users' => $users], JSON_UNESCAPED_UNICODE);
    }

    /**
     * PUT /admin/users/{id}/reset-password
     * Body: { "new_password": "..." } (optional — if omitted, a random password is generated)
     */
    public function resetPassword(int $targetUserId, array $body): void
    {
        if (!$this->requireSuperAdmin()) {
            return;
        }

        $db = getDb();

        // Verify target user exists and is active
        $stmt = $db->prepare("SELECT id, username, fio FROM pwa_users WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $targetUserId);
        $stmt->execute();
        $targetUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$targetUser) {
            http_response_code(404);
            echo json_encode(['error' => 'Пользователь не найден'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Prevent resetting own password through this endpoint
        $adminPwaId = (int) ($GLOBALS['auth_user']['pwa_user_id'] ?? 0);
        if ($targetUserId === $adminPwaId) {
            http_response_code(400);
            echo json_encode(['error' => 'Используйте профиль для смены своего пароля'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $customPassword = trim($body['new_password'] ?? '');

        if ($customPassword !== '') {
            if (mb_strlen($customPassword) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'Пароль должен быть не менее 6 символов'], JSON_UNESCAPED_UNICODE);
                return;
            }
            $plainPassword = $customPassword;
        } else {
            $plainPassword = $this->passwordService->generateStrongPassword(10);
        }

        $hash = $this->passwordService->hash($plainPassword);

        $stmt2 = $db->prepare("UPDATE pwa_users SET password_hash = ? WHERE id = ?");
        $stmt2->bind_param('si', $hash, $targetUserId);
        $stmt2->execute();
        $stmt2->close();

        $adminFio = $GLOBALS['auth_user']['fio'] ?? 'unknown';
        error_log("Admin '{$adminFio}' reset password for user #{$targetUserId} ({$targetUser['fio']})");

        echo json_encode([
            'success'  => true,
            'username' => $targetUser['username'],
            'fio'      => $targetUser['fio'] ?: $targetUser['username'],
            'password' => $plainPassword,
            'message'  => 'Пароль успешно сброшен',
        ], JSON_UNESCAPED_UNICODE);
    }

    private function requireSuperAdmin(): bool
    {
        $authMw = $GLOBALS['auth_middleware'] ?? null;
        if ($authMw && !$authMw->requireLevel(4)) {
            return false;
        }
        return true;
    }
}
