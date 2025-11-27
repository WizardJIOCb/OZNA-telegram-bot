<?php
/**
 * Debug Lebedev case
 */

$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// Find Lebedev
$query = "SELECT user_id, data FROM ozna_data WHERE data LIKE '%Лебедев%' OR data LIKE '%Lebedev%'";
$result = $conn->query($query);

echo "=== ПОИСК ЛЕБЕДЕВА ===\n\n";

while ($row = $result->fetch_assoc()) {
    $data = json_decode($row['data'], true);
    $fio = $data['fio'] ?? 'Н/Д';
    $fn = $data['functional_direction'] ?? 'Н/Д';
    
    echo "Найден: {$fio}\n";
    echo "  User ID: {$row['user_id']}\n";
    echo "  Отдел: {$fn}\n\n";
    
    $userId = $row['user_id'];
    
    // Get last 30 entries
    $query2 = "SELECT dt, message FROM ozna_tabel 
               WHERE user_id = ? 
               ORDER BY dt DESC
               LIMIT 30";
    
    $stmt = $conn->prepare($query2);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result2 = $stmt->get_result();
    
    echo "ПОСЛЕДНИЕ 30 ЗАПИСЕЙ:\n";
    echo str_repeat('=', 80) . "\n";
    
    $entries = [];
    while ($row2 = $result2->fetch_assoc()) {
        if (preg_match('/табель: <b>(.*?)<\/b>/', $row2['message'], $matches)) {
            $activity = $matches[1];
            
            // Classify
            $type = '';
            $isWork = true;
            
            if (strpos($activity, 'Дорога') !== false) {
                $type = '[🚗 МАРКЕР: ДОРОГА]';
                $isWork = false;
            } elseif (strpos($activity, 'Инструктаж') !== false) {
                $type = '[👷 МАРКЕР: ИНСТРУКТАЖ]';
                $isWork = false;
            } elseif (strpos($activity, 'Сдача авансового отчета') !== false) {
                $type = '[💰 МАРКЕР: АВАНС]';
                $isWork = false;
            } elseif (strpos($activity, 'Текущая деятельность') !== false) {
                $type = '[💻 ОФИС]';
                $isWork = false;
            } elseif (strpos($activity, 'Участие в ЗПИ') !== false) {
                $type = '[🏢 ОФИС]';
                $isWork = false;
            } elseif (strpos($activity, 'Участие в ОПИ') !== false) {
                $type = '[🤝 ОФИС]';
                $isWork = false;
            } elseif (strpos($activity, 'Разработка программы работ') !== false) {
                $type = '[📋 ОФИС - ПЛАНИРОВАНИЕ]';
                $isWork = false;
            } elseif (strpos($activity, 'Разработка графика работ') !== false) {
                $type = '[📅 ОФИС - ПЛАНИРОВАНИЕ]';
                $isWork = false;
            } else {
                $type = '[✅ РАБОТА]';
            }
            
            $entries[] = [
                'dt' => $row2['dt'],
                'activity' => $activity,
                'type' => $type,
                'is_work' => $isWork
            ];
            
            echo "{$row2['dt']}: {$activity} {$type}\n";
        }
    }
    
    echo "\n";
    echo str_repeat('=', 80) . "\n";
    echo "АНАЛИЗ:\n";
    echo str_repeat('=', 80) . "\n\n";
    
    // Count work activities
    $workCount = 0;
    $lastWorkDate = null;
    $firstWorkDate = null;
    $hasClosingMarker = false;
    
    foreach ($entries as $entry) {
        if ($entry['is_work']) {
            $workCount++;
            if ($lastWorkDate === null) {
                $lastWorkDate = $entry['dt'];
            }
            $firstWorkDate = $entry['dt'];
        }
        
        if (strpos($entry['type'], 'МАРКЕР: ДОРОГА') !== false || 
            strpos($entry['type'], 'МАРКЕР: АВАНС') !== false) {
            if ($lastWorkDate === null) {
                $hasClosingMarker = true;
            }
        }
    }
    
    echo "Всего рабочих активностей: {$workCount}\n";
    if ($lastWorkDate) {
        echo "Последняя работа: {$lastWorkDate}\n";
        $lastWork = new DateTime($lastWorkDate);
        $now = new DateTime();
        $days = floor(($now->getTimestamp() - $lastWork->getTimestamp()) / 86400);
        echo "Дней с последней работы: {$days}\n";
    }
    echo "Первая работа (в списке): {$firstWorkDate}\n";
    echo "Есть закрывающий маркер в начале: " . ($hasClosingMarker ? 'ДА' : 'НЕТ') . "\n";
    
    echo "\n";
}

$conn->close();
