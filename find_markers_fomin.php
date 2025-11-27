<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$db = new DatabaseService();
$conn = $db->getConnection();

$userId = 1620139119; // Fomin

echo "=== ALL MARKERS FOR FOMIN ===\n\n";

$query = "SELECT dt, message FROM ozna_tabel 
          WHERE user_id = ?
          ORDER BY dt DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$foundMarkers = [];

while ($row = $result->fetch_assoc()) {
    $msg = $row['message'];
    $dt = $row['dt'];
    
    if (strpos($msg, '🚗 Дорога') !== false || 
        strpos($msg, '👷 Инструктаж') !== false || 
        strpos($msg, '💰 Сдача авансового отчета') !== false) {
        
        $type = '';
        if (strpos($msg, '🚗 Дорога') !== false) $type .= '🚗ДОРОГА ';
        if (strpos($msg, '👷 Инструктаж') !== false) $type .= '👷ИНСТРУКТАЖ ';
        if (strpos($msg, '💰 Сдача авансового отчета') !== false) $type .= '💰АВАНС ';
        
        $foundMarkers[] = "$dt: $type";
    }
}

if (empty($foundMarkers)) {
    echo "NO MARKERS FOUND!\n";
    echo "Fomin has NO Дорога/Инструктаж/Аванс records.\n";
    echo "This means session is NOT properly marked.\n\n";
    echo "Getting first and last entry:\n";
    
    $query = "SELECT MIN(dt) as first_entry, MAX(dt) as last_entry FROM ozna_tabel WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo "First entry: " . $row['first_entry'] . "\n";
    echo "Last entry: " . $row['last_entry'] . "\n";
    echo "\nFomin is ACTIVELY WORKING (last entry is recent) but has NO session markers!\n";
} else {
    echo "Found " . count($foundMarkers) . " markers:\n\n";
    foreach ($foundMarkers as $marker) {
        echo $marker . "\n";
    }
}
