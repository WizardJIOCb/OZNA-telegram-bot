<?php
/**
 * Script to count users with access level 1 (Пользователь) using PDO
 */

// Database configuration
$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

echo "=== Подсчет пользователей с уровнем доступа 1 ===\n\n";

try {
    // Create PDO connection
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "Подключение к базе данных успешно установлено.\n\n";
    
    // Query to get all users
    $query = "SELECT user_id, data FROM ozna_data";
    $stmt = $pdo->query($query);
    
    $totalUsers = 0;
    $usersWithLevel1 = 0;
    $usersList = [];
    
    // Process each user
    while ($row = $stmt->fetch()) {
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
    
    echo "\n=== Подсчет завершен ===\n";
    
} catch (PDOException $e) {
    echo "Ошибка базы данных: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
?>