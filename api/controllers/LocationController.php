<?php

namespace OznaApi\Controllers;

class LocationController
{
    /**
     * POST /location
     * Save (upsert) the authenticated user's GPS position.
     * Body: { "latitude": 55.75, "longitude": 49.19, "accuracy": 12.5 }
     */
    public function save(array $body): void
    {
        $auth = $GLOBALS['auth_user'];
        $pwaUserId = (int) ($auth['pwa_user_id'] ?? 0);

        // Get fresh user data from pwa_users
        $db = getDb();
        $stmt = $db->prepare("SELECT linked_telegram_id, fio, functional_direction FROM pwa_users WHERE id = ?");
        $stmt->bind_param('i', $pwaUserId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            http_response_code(400);
            echo json_encode(['error' => 'Пользователь не найден'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Use telegram_id as user_id for consistency with ozna_tabel
        $userId = $user['linked_telegram_id'] ?? $pwaUserId;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['error' => 'Не привязан Telegram аккаунт'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $latitude  = $body['latitude'] ?? null;
        $longitude = $body['longitude'] ?? null;
        $accuracy  = $body['accuracy'] ?? null;
        $projectCode = $body['project_code'] ?? '';

        if ($latitude === null || $longitude === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Укажите latitude и longitude'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $latitude  = (float) $latitude;
        $longitude = (float) $longitude;

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            http_response_code(400);
            echo json_encode(['error' => 'Некорректные координаты'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $fio = $user['fio'] ?? '';
        $fn  = $user['functional_direction'] ?? '';
        $acc = $accuracy !== null ? (float) $accuracy : null;
        $project = mb_substr(trim($projectCode), 0, 50);

        $stmt = $db->prepare(
            "INSERT INTO user_locations (user_id, fio, functional_direction, latitude, longitude, accuracy, project_code)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                fio = VALUES(fio),
                functional_direction = VALUES(functional_direction),
                latitude = VALUES(latitude),
                longitude = VALUES(longitude),
                accuracy = VALUES(accuracy),
                project_code = VALUES(project_code),
                updated_at = CURRENT_TIMESTAMP"
        );
        $stmt->bind_param('issddds', $userId, $fio, $fn, $latitude, $longitude, $acc, $project);
        $stmt->execute();

        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /locations
     * Returns all employee locations updated within the last 72 hours.
     */
    public function getAll(): void
    {
        $hours = isset($_GET['hours']) ? min((int) $_GET['hours'], 168) : 72;
        if ($hours < 1) {
            $hours = 72;
        }

        $db = getDb();
        $stmt = $db->prepare(
            "SELECT user_id, fio, functional_direction, latitude, longitude, accuracy,
                    updated_at + INTERVAL 2 HOUR as updated_at, project_code
             FROM user_locations
             WHERE updated_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
             ORDER BY updated_at DESC"
        );
        $stmt->bind_param('i', $hours);
        $stmt->execute();
        $result = $stmt->get_result();

        $locations = [];
        while ($row = $result->fetch_assoc()) {
            $locations[] = [
                'user_id'              => (int) $row['user_id'],
                'fio'                  => $row['fio'],
                'functional_direction' => $row['functional_direction'],
                'latitude'             => (float) $row['latitude'],
                'longitude'            => (float) $row['longitude'],
                'accuracy'             => $row['accuracy'] !== null ? (float) $row['accuracy'] : null,
                'updated_at'           => $row['updated_at'],
                'project_code'         => $row['project_code'] ?? '',
            ];
        }

        echo json_encode($locations, JSON_UNESCAPED_UNICODE);
    }
}
