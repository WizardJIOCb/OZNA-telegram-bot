<?php
/**
 * Script to check database structure and find access requests
 */

echo "=== Проверка структуры базы данных ===\n\n";

// Database connection parameters
$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

try {
    // Create connection
    $mysqli = new mysqli($host, $username, $password, $database);
    
    // Check connection
    if ($mysqli->connect_error) {
        echo "Ошибка подключения к базе данных: " . $mysqli->connect_error . "\n";
        exit(1);
    }
    
    echo "Подключение к базе данных успешно установлено\n\n";
    
    // Get all tables
    echo "Список таблиц в базе данных:\n";
    $result = $mysqli->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_row()) {
            echo "  - " . $row[0] . "\n";
        }
    }
    echo "\n";
    
    // Check ozna_data table structure
    echo "Структура таблицы ozna_data:\n";
    $result = $mysqli->query("DESCRIBE ozna_data");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    }
    echo "\n";
    
    // Check if there's a separate table for access requests
    echo "Поиск таблицы с заявками на доступ:\n";
    $tables_to_check = ['access_requests', 'ozna_access_requests', 'requests', 'user_requests'];
    foreach ($tables_to_check as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "  Найдена таблица: $table\n";
            
            // Show table structure
            echo "  Структура таблицы $table:\n";
            $desc_result = $mysqli->query("DESCRIBE $table");
            if ($desc_result) {
                while ($desc_row = $desc_result->fetch_assoc()) {
                    echo "    - " . $desc_row['Field'] . " (" . $desc_row['Type'] . ")\n";
                }
            }
            
            // Show sample data
            echo "  Пример данных из таблицы $table:\n";
            $data_result = $mysqli->query("SELECT * FROM $table LIMIT 5");
            if ($data_result) {
                while ($data_row = $data_result->fetch_assoc()) {
                    echo "    " . json_encode($data_row, JSON_UNESCAPED_UNICODE) . "\n";
                }
            }
            echo "\n";
        }
    }
    
    // Check for access requests in ozna_data table
    echo "Проверка наличия заявок в таблице ozna_data:\n";
    $result = $mysqli->query("SELECT user_id, data FROM ozna_data WHERE JSON_EXTRACT(data, '$.access_level') = 0 LIMIT 10");
    if ($result) {
        echo "  Пользователи с уровнем доступа Гость (0):\n";
        while ($row = $result->fetch_assoc()) {
            $userData = json_decode($row['data'], true);
            $fio = $userData['fio'] ?? 'Не указано';
            echo "    User ID: " . $row['user_id'] . ", ФИО: $fio\n";
        }
    }
    
    // Close connection
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== Проверка завершена ===\n";
?>