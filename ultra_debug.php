<?php
/**
 * Ultra detailed debug for Назаров
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

$userId = 2143801829; // Назаров

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// Get all entries
$query = "SELECT dt, message FROM ozna_tabel 
          WHERE user_id = ? 
          AND dt >= DATE_SUB(NOW(), INTERVAL 180 DAY)
          ORDER BY dt ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    if (preg_match('/табель: <b>(.*?)<\/b>/', $row['message'], $matches)) {
        $entries[] = [
            'dt' => $row['dt'],
            'activity' => $matches[1],
            'message' => $row['message']
        ];
    }
}

echo "=== ДЕТАЛЬНАЯ ОТЛАДКА ===\n\n";
echo "Записей: " . count($entries) . "\n\n";

foreach ($entries as $i => $entry) {
    echo "[{$i}] {$entry['dt']}: {$entry['activity']}\n";
}

echo "\n";

$lastEntry = $entries[count($entries) - 1];
echo "Последняя запись:\n";
echo "  Дата: {$lastEntry['dt']}\n";
echo "  Активность: {$lastEntry['activity']}\n";
echo "  Message: {$lastEntry['message']}\n\n";

// Check for closing marker
$isDoroga = (strpos($lastEntry['message'], '🚗 Дорога') !== false);
$isAvans = (strpos($lastEntry['message'], '💰 Сдача авансового отчета') !== false);

echo "Проверка закрывающего маркера:\n";
echo "  strpos(message, '🚗 Дорога') = " . var_export(strpos($lastEntry['message'], '🚗 Дорога'), true) . "\n";
echo "  strpos(message, '💰 Сдача авансового отчета') = " . var_export(strpos($lastEntry['message'], '💰 Сдача авансового отчета'), true) . "\n";
echo "  isDoroga = " . var_export($isDoroga, true) . "\n";
echo "  isAvans = " . var_export($isAvans, true) . "\n";
echo "  lastIsClosingMarker = " . var_export($isDoroga || $isAvans, true) . "\n";

$conn->close();
