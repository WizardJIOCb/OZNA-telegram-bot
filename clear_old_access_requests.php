<?php
/**
 * Script to clear old access requests data
 */

// Include necessary files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== Очистка старых данных по заявкам ===\n\n";

try {
    // Initialize database service
    $databaseService = new DatabaseService();
    
    echo "Подключение к базе данных...\n";
    
    // Get database connection
    $connection = $databaseService->getConnection();
    
    // Проверяем наличие таблицы ozna_data
    echo "Проверка наличия таблицы ozna_data...\n";
    $result = $connection->query("SHOW TABLES LIKE 'ozna_data'");
    
    if ($result->num_rows == 0) {
        echo "Таблица ozna_data не найдена!\n";
        exit(1);
    }
    
    echo "Таблица ozna_data найдена.\n";
    
    // Проверяем структуру таблицы
    echo "Проверка структуры таблицы...\n";
    $result = $connection->query("DESCRIBE ozna_data");
    
    if (!$result) {
        echo "Ошибка при получении структуры таблицы: " . $connection->error . "\n";
        exit(1);
    }
    
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }
    
    echo "Структура таблицы ozna_data:\n";
    foreach ($columns as $column) {
        echo "  - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Проверяем наличие поля data
    $hasDataField = false;
    foreach ($columns as $column) {
        if ($column['Field'] == 'data') {
            $hasDataField = true;
            break;
        }
    }
    
    if (!$hasDataField) {
        echo "Поле 'data' не найдено в таблице ozna_data!\n";
        exit(1);
    }
    
    echo "\nПоле 'data' найдено.\n";
    
    // Получаем общее количество записей
    $result = $connection->query("SELECT COUNT(*) as count FROM ozna_data");
    $row = $result->fetch_assoc();
    $totalRecords = $row['count'];
    
    echo "Общее количество записей в таблице: {$totalRecords}\n";
    
    // Получаем количество записей с пустым или некорректным полем data
    $result = $connection->query("SELECT COUNT(*) as count FROM ozna_data WHERE data IS NULL OR data = '' OR data = 'null'");
    $row = $result->fetch_assoc();
    $emptyRecords = $row['count'];
    
    echo "Количество записей с пустым полем data: {$emptyRecords}\n";
    
    // Получаем количество записей с корректным JSON в поле data
    $result = $connection->query("SELECT COUNT(*) as count FROM ozna_data WHERE JSON_VALID(data)");
    $row = $result->fetch_assoc();
    $validJsonRecords = $row['count'];
    
    echo "Количество записей с корректным JSON в поле data: {$validJsonRecords}\n";
    
    // Получаем количество записей с некорректным JSON в поле data
    $result = $connection->query("SELECT COUNT(*) as count FROM ozna_data WHERE data IS NOT NULL AND data != '' AND data != 'null' AND NOT JSON_VALID(data)");
    $row = $result->fetch_assoc();
    $invalidJsonRecords = $row['count'];
    
    echo "Количество записей с некорректным JSON в поле data: {$invalidJsonRecords}\n";
    
    // Показываем примеры некорректных записей (если есть)
    if ($invalidJsonRecords > 0) {
        echo "\nПримеры некорректных записей:\n";
        $result = $connection->query("SELECT user_id, data FROM ozna_data WHERE data IS NOT NULL AND data != '' AND data != 'null' AND NOT JSON_VALID(data) LIMIT 5");
        
        while ($row = $result->fetch_assoc()) {
            echo "  User ID: " . $row['user_id'] . ", Data: " . $row['data'] . "\n";
        }
    }
    
    // Спрашиваем пользователя, хочет ли он удалить некорректные записи
    echo "\nХотите удалить записи с некорректным JSON в поле data? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim($line) == 'y' || trim($line) == 'Y') {
        echo "Удаление записей с некорректным JSON...\n";
        
        // Удаляем записи с некорректным JSON
        $result = $connection->query("DELETE FROM ozna_data WHERE data IS NOT NULL AND data != '' AND data != 'null' AND NOT JSON_VALID(data)");
        
        if ($result === false) {
            echo "Ошибка при удалении записей: " . $connection->error . "\n";
        } else {
            $deletedRows = $connection->affected_rows;
            echo "Удалено {$deletedRows} записей с некорректным JSON.\n";
        }
    } else {
        echo "Удаление отменено.\n";
    }
    
    // Спрашиваем пользователя, хочет ли он очистить пустые записи
    echo "\nХотите удалить записи с пустым полем data? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim($line) == 'y' || trim($line) == 'Y') {
        echo "Удаление записей с пустым полем data...\n";
        
        // Удаляем записи с пустым полем data
        $result = $connection->query("DELETE FROM ozna_data WHERE data IS NULL OR data = '' OR data = 'null'");
        
        if ($result === false) {
            echo "Ошибка при удалении записей: " . $connection->error . "\n";
        } else {
            $deletedRows = $connection->affected_rows;
            echo "Удалено {$deletedRows} записей с пустым полем data.\n";
        }
    } else {
        echo "Удаление отменено.\n";
    }
    
    // Получаем обновленное количество записей
    $result = $connection->query("SELECT COUNT(*) as count FROM ozna_data");
    $row = $result->fetch_assoc();
    $totalRecordsAfter = $row['count'];
    
    echo "\nОбщее количество записей после очистки: {$totalRecordsAfter}\n";
    echo "Удалено записей: " . ($totalRecords - $totalRecordsAfter) . "\n";
    
    echo "\n=== Очистка завершена ===\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}

?>