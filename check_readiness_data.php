<?php
/**
 * Диагностический скрипт для проверки данных "готовность заказчика"
 */

// Подключение к базе данных
$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

try {
    $mysqli = new mysqli($host, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        die("Ошибка подключения: " . $mysqli->connect_error);
    }
    
    // Установка кодировки UTF-8
    $mysqli->set_charset("utf8mb4");
    
    // Поиск записей с "Простой" в сообщениях
    $query = "SELECT user_id, dt + INTERVAL 2 HOUR as dt, message 
              FROM ozna_tabel 
              WHERE message LIKE '%Простой%' 
              ORDER BY dt DESC 
              LIMIT 10";
    
    $result = $mysqli->query($query);
    
    echo "====================================\n";
    echo "ДИАГНОСТИКА ДАННЫХ: ГОТОВНОСТЬ ЗАКАЗЧИКА\n";
    echo "====================================\n\n";
    
    if ($result && $result->num_rows > 0) {
        echo "Найдено записей с 'Простой': " . $result->num_rows . "\n\n";
        
        while ($row = $result->fetch_assoc()) {
            echo "Дата: " . $row['dt'] . "\n";
            echo "Пользователь ID: " . $row['user_id'] . "\n";
            echo "Сообщение: " . $row['message'] . "\n";
            
            // Проверка на наличие "готовность" в сообщении
            if (stripos($row['message'], 'готовн') !== false) {
                echo "✅ Найдено упоминание 'готовн' в сообщении\n";
            }
            
            echo "----------------------------------------\n";
        }
    } else {
        echo "Записи с 'Простой' не найдены\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
?>