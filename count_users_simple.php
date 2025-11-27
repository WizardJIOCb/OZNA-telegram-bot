<?php
/**
 * Simple script to count users with access level 1 (Пользователь)
 */

// Database configuration
$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

echo "=== Подсчет пользователей с уровнем доступа 1 ===\n\n";

try {
    // Create database connection
    $mysqli = new mysqli($host, $username, $password, $database);
    
    // Check connection
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "Подключение к базе данных успешно установлено.\n\n";
    
    // Query to get all users
    $query = "SELECT user_id, data FROM ozna_data";
    $result = $mysqli->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $mysqli->error);
    }
    
    $totalUsers = 0;
    $usersWithLevel1 = 0;
    $usersList = [];
    
    // Process each user
    while ($row = $result->fetch_assoc()) {
        $totalUsers++;
        $userId = $row['user_id'];
        $jsonData = $row['data'];
        
        // Decode JSON data
        $userData = json_decode($jsonData, true);
        
        if ($userData) {
            // Get access level from user data
            $accessLevel = $userData['access_level'] ?? 0;
            
            // Count users with access level 1
            if ($accessLevel == 1) {
                $usersWithLevel1++;
                $fio = $userData['fio'] ?? 'Без ФИО';
                $usersList[] = [
                    'id' => $userId,
                    'fio' => $fio,
                    'access_level' => $accessLevel
                ];
            }
        }
    }
    
    echo "Всего пользователей в системе: {$totalUsers}\n";
    echo "Количество пользователей с уровнем доступа 1 (Пользователь): {$usersWithLevel1}\n\n";
    
    // Show list of users with level 1
    if (!empty($usersList)) {
        echo "Список пользователей с уровнем доступа 1:\n";
        echo str_repeat("-", 50) . "\n";
        foreach ($usersList as $user) {
            echo "ID: {$user['id']}, ФИО: {$user['fio']}\n";
        }
        echo str_repeat("-", 50) . "\n";
    }
    
    // Close connection
    $mysqli->close();
    
    echo "\n=== Подсчет завершен ===\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
?>