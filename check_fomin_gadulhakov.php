<?php
/**
 * Check Fomin and Gadulhakov session status
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();
$conn = $dbService->getConnection();

// Find users by partial name match
echo "=== SEARCHING FOR FOMIN AND GADULHAKOV ===\n\n";

$query = "SELECT user_id, data FROM ozna_data WHERE data LIKE '%Фомин%' OR data LIKE '%Гадулхаков%'";
$result = $conn->query($query);

$users = [];
while ($row = $result->fetch_assoc()) {
    $data = json_decode($row['data'], true);
    if (isset($data['fio'])) {
        $users[] = [
            'user_id' => $row['user_id'],
            'fio' => $data['fio']
        ];
        echo "Found: " . $data['fio'] . " (ID: " . $row['user_id'] . ")\n";
    }
}

echo "\n";

foreach ($users as $user) {
    $userId = $user['user_id'];
    $fio = $user['fio'];
    
    echo "=== $fio (ID: $userId) ===\n\n";
    
    // Get last 50 entries
    $query = "SELECT dt, message FROM ozna_tabel 
              WHERE user_id = ? 
              ORDER BY dt DESC
              LIMIT 50";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "Last 20 entries:\n";
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $count++;
        $msg = $row['message'];
        $dt = $row['dt'];
        
        // Check for markers
        $markers = [];
        if (strpos($msg, '🚗 Дорога') !== false) $markers[] = '🚗ДОРОГА';
        if (strpos($msg, '👷 Инструктаж') !== false) $markers[] = '👷ИНСТРУКТАЖ';
        if (strpos($msg, '💰 Сдача авансового отчета') !== false) $markers[] = '💰АВАНС';
        if (strpos($msg, '🏢 Участие в ЗПИ') !== false) $markers[] = '🏢ЗПИ';
        
        $markerStr = empty($markers) ? '' : ' [' . implode(', ', $markers) . ']';
        
        echo "$count. $dt$markerStr\n";
        if (!empty($markers) || $count <= 3) {
            // Extract activity from message
            if (preg_match('/<b>([^<]+)<\/b>\s*$/u', $msg, $matches)) {
                echo "   Activity: " . $matches[1] . "\n";
            }
        }
    }
    
    echo "\n\n";
}
