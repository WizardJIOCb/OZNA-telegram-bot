<?php
/**
 * Проверка реальных данных из базы данных для "готовность заказчика"
 */

echo "====================================\n";
echo "ПРОВЕРКА РЕАЛЬНЫХ ДАННЫХ ИЗ БАЗЫ\n";
echo "====================================\n\n";

// Подключаемся к базе данных
try {
    $dsn = "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4";
    $pdo = new PDO($dsn, 'WizardJIOCb', '7qQ#28051961');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Подключение к базе данных успешно\n\n";
    
    // Ищем записи с "Простой" в сообщениях
    $sql = "SELECT user_id, dt, message FROM ozna_tabel WHERE message LIKE '%Простой%' ORDER BY dt DESC LIMIT 5";
    $stmt = $pdo->query($sql);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Найдено записей с 'Простой': " . count($entries) . "\n\n";
    
    if (empty($entries)) {
        echo "❌ Не найдено записей с 'Простой'\n";
        exit;
    }
    
    // Показываем найденные записи
    foreach ($entries as $index => $entry) {
        echo "Запись " . ($index + 1) . ":\n";
        echo "  User ID: " . $entry['user_id'] . "\n";
        echo "  Дата: " . $entry['dt'] . "\n";
        echo "  Сообщение: " . $entry['message'] . "\n\n";
        
        // Проверяем, есть ли в сообщении структура с "готовность"
        if (strpos($entry['message'], 'готовность') !== false) {
            echo "  ✅ Найдено упоминание 'готовность' в сообщении!\n";
            
            // Извлекаем часть с "готовность"
            if (preg_match('/готовность[^)]*/u', $entry['message'], $matches)) {
                echo "  Извлечено: '" . $matches[0] . "'\n";
            }
        }
        echo "\n";
    }
    
    // Проверим конкретную структуру, которая нас интересует
    echo "Поиск записей с вложенной структурой:\n";
    $sql = "SELECT user_id, dt, message FROM ozna_tabel WHERE message LIKE '%Простой%(%)%(%)%' ORDER BY dt DESC LIMIT 3";
    $stmt = $pdo->query($sql);
    $nestedEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Найдено записей с вложенной структурой: " . count($nestedEntries) . "\n\n";
    
    foreach ($nestedEntries as $index => $entry) {
        echo "Вложенная запись " . ($index + 1) . ":\n";
        echo "  User ID: " . $entry['user_id'] . "\n";
        echo "  Дата: " . $entry['dt'] . "\n";
        echo "  Сообщение: " . $entry['message'] . "\n";
        
        // Попробуем извлечь структуру
        if (preg_match('/Простой\s*(\(.*\)\s*){2}/u', $entry['message'], $matches)) {
            echo "  Найдена структура: " . $matches[0] . "\n";
        }
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Ошибка подключения к базе данных: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "====================================\n";
echo "ПРОВЕРКА ЗАВЕРШЕНА\n";
echo "====================================\n";