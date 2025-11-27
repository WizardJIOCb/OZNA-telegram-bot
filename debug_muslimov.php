<?php
/**
 * Debug Muслимов activities
 */

$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// Find Muслимов
$query = "SELECT user_id FROM ozna_data WHERE data LIKE '%Муслимов%' LIMIT 1";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$userId = $row['user_id'];

echo "=== АКТИВНОСТИ МУСЛИМОВА (ID: {$userId}) ===\n\n";

// Get last 20 entries
$query = "SELECT dt, message FROM ozna_tabel 
          WHERE user_id = ? 
          ORDER BY dt DESC
          LIMIT 20";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if (preg_match('/табель: <b>(.*?)<\/b>/', $row['message'], $matches)) {
        $activity = $matches[1];
        
        // Classify
        $type = '?';
        if (strpos($activity, 'Дорога') !== false) $type = '[ДОРОГА]';
        elseif (strpos($activity, 'Инструктаж') !== false) $type = '[ИНСТРУКТАЖ]';
        elseif (strpos($activity, 'Текущая деятельность') !== false) $type = '[ОФИС]';
        elseif (strpos($activity, 'Участие в ЗПИ') !== false) $type = '[ОФИС]';
        elseif (strpos($activity, 'Участие в ОПИ') !== false) $type = '[ОФИС]';
        elseif (strpos($activity, 'Разработка программы работ') !== false) $type = '[ОФИС - ПЛАНИРОВАНИЕ]';
        elseif (strpos($activity, 'Разработка графика работ') !== false) $type = '[ОФИС - ПЛАНИРОВАНИЕ]';
        else $type = '[РАБОТА]';
        
        echo "{$row['dt']}: {$activity} {$type}\n";
    }
}

$conn->close();
