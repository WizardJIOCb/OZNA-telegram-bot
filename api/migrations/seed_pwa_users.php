<?php

/**
 * One-time migration: Seed pwa_users from existing ozna_data Telegram users.
 *
 * Usage:  php seed_pwa_users.php
 * Output: CSV with username, password for admin to distribute.
 *
 * IMPORTANT: Run this only once! It will skip users that already have a pwa_users row.
 */

require_once __DIR__ . '/../services/PasswordService.php';

$config = require __DIR__ . '/../config.php';
$c = $config['db'];

$db = new mysqli($c['host'], $c['username'], $c['password'], $c['database']);
if ($db->connect_error) {
    die("DB connection failed: " . $db->connect_error . "\n");
}
$db->set_charset('utf8mb4');

$passwordService = new OznaApi\Services\PasswordService();

// Ensure pwa_users table exists
$db->query("
    CREATE TABLE IF NOT EXISTS pwa_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        linked_telegram_id BIGINT NULL UNIQUE,
        access_level TINYINT DEFAULT 0,
        fio VARCHAR(255),
        functional_direction VARCHAR(100),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_telegram_id (linked_telegram_id),
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// Fetch all Telegram users
$result = $db->query("SELECT user_id, data FROM ozna_data");
if (!$result) {
    die("Failed to query ozna_data: " . $db->error . "\n");
}

$created = 0;
$skipped = 0;
$credentials = [];

// Track usernames for uniqueness
$usedUsernames = [];
$existingStmt = $db->prepare("SELECT id FROM pwa_users WHERE linked_telegram_id = ?");

while ($row = $result->fetch_assoc()) {
    $telegramId = (int) $row['user_id'];
    $data = json_decode($row['data'], true) ?? [];

    $fio = $data['fio'] ?? '';
    $fn  = $data['functional_direction'] ?? 'Не указано';
    $accessLevel = (int) ($data['access_level'] ?? 0);

    // Skip users without FIO
    if (trim($fio) === '') {
        $skipped++;
        continue;
    }

    // Check if already migrated
    $existingStmt->bind_param('i', $telegramId);
    $existingStmt->execute();
    if ($existingStmt->get_result()->fetch_assoc()) {
        $skipped++;
        continue;
    }

    // Generate username from FIO (transliterate or use as-is)
    $username = $fio;

    // Handle duplicate usernames by appending a number
    if (in_array(mb_strtolower($username), $usedUsernames)) {
        $suffix = 2;
        while (in_array(mb_strtolower($username . '_' . $suffix), $usedUsernames)) {
            $suffix++;
        }
        $username = $username . '_' . $suffix;
    }
    $usedUsernames[] = mb_strtolower($username);

    // Generate a strong password
    $plainPassword = $passwordService->generateStrongPassword(10);
    $hash = $passwordService->hash($plainPassword);

    // Insert into pwa_users
    $stmt = $db->prepare(
        "INSERT INTO pwa_users (username, password_hash, linked_telegram_id, access_level, fio, functional_direction, is_active)
         VALUES (?, ?, ?, ?, ?, ?, 1)"
    );
    $stmt->bind_param('ssisis', $username, $hash, $telegramId, $accessLevel, $fio, $fn);

    if ($stmt->execute()) {
        $credentials[] = [
            'telegram_id' => $telegramId,
            'username'    => $username,
            'password'    => $plainPassword,
            'fio'         => $fio,
            'fn'          => $fn,
            'access_level'=> $accessLevel,
        ];
        $created++;
    } else {
        echo "ERROR inserting user {$fio} (TG: {$telegramId}): " . $stmt->error . "\n";
    }
}

echo "\n=== Migration Complete ===\n";
echo "Created: {$created}\n";
echo "Skipped: {$skipped}\n\n";

// Output CSV
if (count($credentials) > 0) {
    $csvFile = __DIR__ . '/pwa_credentials_' . date('Y-m-d_H-i-s') . '.csv';
    $fp = fopen($csvFile, 'w');
    // UTF-8 BOM for Excel
    fwrite($fp, "\xEF\xBB\xBF");
    fputcsv($fp, ['Telegram ID', 'Логин', 'Пароль', 'ФИО', 'ФН', 'Уровень']);
    foreach ($credentials as $c) {
        fputcsv($fp, [
            $c['telegram_id'],
            $c['username'],
            $c['password'],
            $c['fio'],
            $c['fn'],
            $c['access_level'],
        ]);
    }
    fclose($fp);
    echo "Credentials saved to: {$csvFile}\n";

    // Also print to stdout
    echo "\n--- Credentials ---\n";
    foreach ($credentials as $c) {
        echo sprintf("  %-35s | login: %-35s | pass: %s\n", $c['fio'], $c['username'], $c['password']);
    }
}

$db->close();
