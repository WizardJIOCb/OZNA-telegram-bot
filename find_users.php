<?php
/**
 * Find users by name in database
 */

$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    echo "=== ПОИСК СОТРУДНИКОВ В БАЗЕ ДАННЫХ ===\n\n";
    
    $searchNames = ['Гайнутдинов', 'Шангин', 'Мамаев', 'Ахунов'];
    
    foreach ($searchNames as $name) {
        echo "Поиск: {$name}\n";
        echo str_repeat('-', 60) . "\n";
        
        // Search in ozna_data table
        $query = "SELECT user_id, data FROM ozna_data 
                  WHERE data LIKE ?";
        
        $searchTerm = "%{$name}%";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $found = false;
        while ($row = $result->fetch_assoc()) {
            $found = true;
            $userData = json_decode($row['data'], true);
            $fio = $userData['fio'] ?? 'Н/Д';
            $fn = $userData['functional_direction'] ?? 'Н/Д';
            
            echo "  User ID: {$row['user_id']}\n";
            echo "  ФИО: {$fio}\n";
            echo "  Отдел: {$fn}\n";
            
            // Check if has tabel entries
            $tabelQuery = "SELECT COUNT(*) as cnt FROM ozna_tabel WHERE user_id = ?";
            $tabelStmt = $conn->prepare($tabelQuery);
            $tabelStmt->bind_param("i", $row['user_id']);
            $tabelStmt->execute();
            $tabelResult = $tabelStmt->get_result();
            $tabelRow = $tabelResult->fetch_assoc();
            
            echo "  Записей в табеле: {$tabelRow['cnt']}\n";
            
            // Get latest tabel entry
            if ($tabelRow['cnt'] > 0) {
                $lastQuery = "SELECT dt, message FROM ozna_tabel 
                             WHERE user_id = ? 
                             ORDER BY dt DESC 
                             LIMIT 1";
                $lastStmt = $conn->prepare($lastQuery);
                $lastStmt->bind_param("i", $row['user_id']);
                $lastStmt->execute();
                $lastResult = $lastStmt->get_result();
                $lastEntry = $lastResult->fetch_assoc();
                
                if (preg_match('/табель: <b>(.*?)<\/b>/', $lastEntry['message'], $matches)) {
                    echo "  Последняя запись: {$lastEntry['dt']} - {$matches[1]}\n";
                }
            }
            
            echo "\n";
        }
        
        if (!$found) {
            echo "  ❌ Не найдено в базе данных\n";
        }
        
        echo "\n";
    }
    
    // Also show all users with recent activity from Шеф-монтаж department
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "СОТРУДНИКИ ОТДЕЛА 'ШЕФ-МОНТАЖ' С АКТИВНОСТЬЮ:\n";
    echo str_repeat('=', 60) . "\n\n";
    
    $query = "SELECT user_id, data FROM ozna_data 
              WHERE data LIKE '%Шеф-монтаж%'";
    
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $userData = json_decode($row['data'], true);
        $fio = $userData['fio'] ?? 'Н/Д';
        
        // Check if has tabel entries
        $tabelQuery = "SELECT COUNT(*) as cnt, MAX(dt) as last_dt 
                       FROM ozna_tabel 
                       WHERE user_id = ?";
        $tabelStmt = $conn->prepare($tabelQuery);
        $tabelStmt->bind_param("i", $row['user_id']);
        $tabelStmt->execute();
        $tabelResult = $tabelStmt->get_result();
        $tabelRow = $tabelResult->fetch_assoc();
        
        if ($tabelRow['cnt'] > 0) {
            echo "{$fio} (ID: {$row['user_id']}) - {$tabelRow['cnt']} записей, последняя: {$tabelRow['last_dt']}\n";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
