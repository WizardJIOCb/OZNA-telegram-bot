<?php
$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

echo "=== ПОИСК МАРКЕРОВ В БАЗЕ ДАННЫХ ===\n\n";

$searchTerms = ['Дорога', 'Инструктаж', 'Сдача авансового'];

foreach ($searchTerms as $term) {
    echo "Поиск: {$term}\n";
    echo str_repeat('-', 70) . "\n";
    
    $query = "SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(message, 'табель: <b>', -1), '</b>', 1) as activity 
              FROM ozna_tabel 
              WHERE message LIKE ?
              LIMIT 10";
    
    $searchPattern = "%{$term}%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $activity = $row['activity'];
        echo "  '{$activity}'\n";
    }
    
    echo "\n";
}

$conn->close();
