<?php

namespace OznaApi\Controllers;

use OznaApi\Services\PasswordService;

class ProfileController
{
    /**
     * GET /profile
     * Returns the authenticated user's profile from ozna_data (linked via telegram_id).
     */
    public function get(): void
    {
        $auth = $GLOBALS['auth_user'];
        $telegramId = $auth['telegram_id'] ?? null;

        if (!$telegramId) {
            // No linked Telegram account — return data from pwa_users only
            echo json_encode([
                'fio'                  => $auth['fio'] ?? '',
                'functional_direction' => $auth['fn'] ?? 'Не указано',
                'access_level'         => (int) ($auth['access_level'] ?? 0),
                'access_level_name'    => self::getAccessLevelName((int) ($auth['access_level'] ?? 0)),
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Fetch fresh data from ozna_data (same source the TG bot uses)
        $db   = getDb();
        $stmt = $db->prepare("SELECT data FROM ozna_data WHERE user_id = ?");
        $stmt->bind_param('i', $telegramId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $userData = $row ? json_decode($row['data'], true) : [];

        $fio   = $userData['fio'] ?? $auth['fio'] ?? '';
        $fn    = $userData['functional_direction'] ?? $auth['fn'] ?? 'Не указано';
        $level = (int) ($userData['access_level'] ?? $auth['access_level'] ?? 0);

        echo json_encode([
            'fio'                  => $fio,
            'functional_direction' => $fn,
            'access_level'         => $level,
            'access_level_name'    => self::getAccessLevelName($level),
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * PUT /profile/password
     * Body: { "old_password": "...", "new_password": "..." }
     */
    public function changePassword(array $body, PasswordService $passwordService): void
    {
        $oldPwd = $body['old_password'] ?? '';
        $newPwd = $body['new_password'] ?? '';

        if ($oldPwd === '' || $newPwd === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Укажите старый и новый пароль'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (mb_strlen($newPwd) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'Новый пароль должен быть не менее 6 символов'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $pwaUserId = (int) ($GLOBALS['auth_user']['pwa_user_id'] ?? 0);

        $db   = getDb();
        $stmt = $db->prepare("SELECT password_hash FROM pwa_users WHERE id = ?");
        $stmt->bind_param('i', $pwaUserId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Пользователь не найден'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$passwordService->verify($oldPwd, $row['password_hash'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Неверный текущий пароль'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $newHash = $passwordService->hash($newPwd);
        $stmt2 = $db->prepare("UPDATE pwa_users SET password_hash = ? WHERE id = ?");
        $stmt2->bind_param('si', $newHash, $pwaUserId);
        $stmt2->execute();

        echo json_encode(['success' => true, 'message' => 'Пароль успешно изменен'], JSON_UNESCAPED_UNICODE);
    }

    private static function getAccessLevelName(int $level): string
    {
        $names = [
            0 => 'Гость',
            1 => 'Пользователь',
            2 => 'Супервайзер',
            3 => 'Администратор',
            4 => 'Суперадминистратор',
        ];
        return $names[$level] ?? 'Неизвестно';
    }
}
