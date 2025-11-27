<?php
/**
 * Script to check access requests in the database
 */

// Include the legacy database class
require_once __DIR__ . '/Custome/DB.php';

echo "=== Проверка заявок на доступ в базе данных ===\n\n";

try {
    // Check if we can connect to the database
    echo "Попытка подключения к базе данных...\n";
    
    // Test database connection by getting a sample user
    $testUser = pDB::select("SELECT COUNT(*) as count FROM ozna_data");
    if ($testUser && isset($testUser[0]['count'])) {
        echo "Подключение к базе данных успешно установлено\n";
        echo "Всего записей в таблице ozna_data: " . $testUser[0]['count'] . "\n\n";
    } else {
        echo "Не удалось подключиться к базе данных\n\n";
        exit(1);
    }
    
    // Check for users with different access levels
    echo "Проверка пользователей по уровням доступа:\n";
    
    // Guests (level 0)
    $guests = pDB::select("SELECT user_id, data FROM ozna_data WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.access_level')) = '0' OR JSON_EXTRACT(data, '$.access_level') IS NULL LIMIT 10");
    echo "  Гости (уровень 0): " . count($guests) . " пользователей\n";
    foreach ($guests as $user) {
        $userData = json_decode($user['data'], true);
        $fio = $userData['fio'] ?? 'Не указано';
        echo "    User ID: " . $user['user_id'] . ", ФИО: $fio\n";
    }
    
    // Users (level 1)
    $users = pDB::select("SELECT user_id, data FROM ozna_data WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.access_level')) = '1' LIMIT 10");
    echo "  Пользователи (уровень 1): " . count($users) . " пользователей\n";
    foreach ($users as $user) {
        $userData = json_decode($user['data'], true);
        $fio = $userData['fio'] ?? 'Не указано';
        echo "    User ID: " . $user['user_id'] . ", ФИО: $fio\n";
    }
    
    // Supervisors (level 2) - new level
    $supervisors = pDB::select("SELECT user_id, data FROM ozna_data WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.access_level')) = '2' LIMIT 10");
    echo "  Супервайзеры (уровень 2): " . count($supervisors) . " пользователей\n";
    foreach ($supervisors as $user) {
        $userData = json_decode($user['data'], true);
        $fio = $userData['fio'] ?? 'Не указано';
        echo "    User ID: " . $user['user_id'] . ", ФИО: $fio\n";
    }
    
    // Admins (level 3)
    $admins = pDB::select("SELECT user_id, data FROM ozna_data WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.access_level')) = '3' LIMIT 10");
    echo "  Администраторы (уровень 3): " . count($admins) . " пользователей\n";
    foreach ($admins as $user) {
        $userData = json_decode($user['data'], true);
        $fio = $userData['fio'] ?? 'Не указано';
        echo "    User ID: " . $user['user_id'] . ", ФИО: $fio\n";
    }
    
    // Super admins (level 4)
    $superAdmins = pDB::select("SELECT user_id, data FROM ozna_data WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.access_level')) = '4' LIMIT 10");
    echo "  Суперадминистраторы (уровень 4): " . count($superAdmins) . " пользователей\n";
    foreach ($superAdmins as $user) {
        $userData = json_decode($user['data'], true);
        $fio = $userData['fio'] ?? 'Не указано';
        echo "    User ID: " . $user['user_id'] . ", ФИО: $fio\n";
    }
    
    echo "\n";
    
    // Search for specific users mentioned by you
    echo "Поиск упомянутых пользователей:\n";
    
    // Try to find users by name patterns
    $searchTerms = ['Близнецов', 'Гилязтдинов'];
    foreach ($searchTerms as $term) {
        $users = pDB::select("SELECT user_id, data FROM ozna_data WHERE data LIKE '%$term%' LIMIT 5");
        echo "  Поиск по фамилии '$term': " . count($users) . " пользователей\n";
        foreach ($users as $user) {
            $userData = json_decode($user['data'], true);
            $fio = $userData['fio'] ?? 'Не указано';
            echo "    User ID: " . $user['user_id'] . ", ФИО: $fio\n";
        }
    }
    
    echo "\n";
    
    // Check for users with no access level set (potential new requests)
    echo "Проверка пользователей без установленного уровня доступа:\n";
    $noAccessLevel = pDB::select("SELECT user_id, data FROM ozna_data WHERE data NOT LIKE '%access_level%' LIMIT 10");
    echo "  Пользователей без уровня доступа: " . count($noAccessLevel) . "\n";
    foreach ($noAccessLevel as $user) {
        $userData = json_decode($user['data'], true);
        $fio = $userData['fio'] ?? 'Не указано';
        echo "    User ID: " . $user['user_id'] . ", ФИО: $fio\n";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Проверка завершена ===\n";
?>