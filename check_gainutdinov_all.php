<?php
/**
 * Check ALL entries for Gainutdinov to find session markers
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$userId = 1429455763;

$dbService = new DatabaseService();
$conn = $dbService->getConnection();

echo "=== ALL ENTRIES FOR GAINUTDINOV ===\n\n";

$query = "SELECT dt, SUBSTRING(message, 1, 200) as msg FROM ozna_tabel 
          WHERE user_id = ? 
          ORDER BY dt DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    $msg = $row['msg'];
    $dt = $row['dt'];
    
    // Check for markers
    $markers = [];
    if (strpos($msg, '🚗 Дорога') !== false) $markers[] = '🚗ДОРОГА';
    if (strpos($msg, '👷 Инструктаж') !== false) $markers[] = '👷ИНСТРУКТАЖ';
    if (strpos($msg, '💰 Сдача авансового отчета') !== false) $markers[] = '💰АВАНС';
    
    if (!empty($markers)) {
        echo "$count. $dt [" . implode(', ', $markers) . "]\n";
        echo "   Message: $msg\n\n";
    }
}

echo "\nTotal entries checked: $count\n";
