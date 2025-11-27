<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();
$conn = $dbService->getConnection();

$userId = 5124845625; // Муфаззалов

echo "=== ДЕТАЛЬНЫЙ ПРОСМОТР ЗАПИСИ 18.10.2025 ===\n\n";

$query = "SELECT id, dt, message FROM ozna_tabel 
          WHERE user_id = ? 
          AND DATE(dt) = '2025-10-18'
          ORDER BY dt DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}\n";
    echo "Дата: {$row['dt']}\n";
    echo "Полное сообщение:\n";
    echo str_repeat("-", 70) . "\n";
    echo $row['message'] . "\n";
    echo str_repeat("-", 70) . "\n\n";
}
