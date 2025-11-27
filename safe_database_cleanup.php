<?php
/**
 * Safe database cleanup script
 * This script requires manual confirmation before performing any destructive operations
 */

echo "=== Безопасная очистка базы данных ===\n\n";

// Include necessary files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

try {
    // Initialize database service
    echo "Инициализация подключения к базе данных...\n";
    $databaseService = new DatabaseService();
    
    // Get database connection
    $connection = $databaseService->getConnection();
    
    echo "Подключение к базе данных установлено успешно.\n\n";
    
    // Show current database status
    echo "Текущее состояние базы данных:\n";
    echo "------------------------------\n";
    
    // Check if ozna_data table exists
    $result = $connection->query("SHOW TABLES LIKE 'ozna_data'");
    if ($result->num_rows == 0) {
        echo "Таблица ozna_data не найдена!\n";
        exit(1);
    }
    
    echo "Таблица ozna_data: НАЙДЕНА\n";
    
    // Get record count
    $result = $connection->query("SELECT COUNT(*) as count FROM ozna_data");
    $row = $result->fetch_assoc();
    $recordCount = $row['count'];
    
    echo "Количество записей в таблице ozna_data: {$recordCount}\n\n";
    
    // Show sample records (first 3)
    echo "Примеры записей (первые 3):\n";
    $result = $connection->query("SELECT user_id, data, create_date FROM ozna_data LIMIT 3");
    while ($row = $result->fetch_assoc()) {
        echo "  User ID: " . $row['user_id'] . "\n";
        echo "  Data: " . substr($row['data'], 0, 100) . (strlen($row['data']) > 100 ? "..." : "") . "\n";
        echo "  Create Date: " . $row['create_date'] . "\n\n";
    }
    
    // Ask for confirmation
    echo "ВНИМАНИЕ: Эта операция удалит ВСЕ данные из таблицы ozna_data!\n";
    echo "Это действие НЕОБРАТИМО и приведет к потере всех пользовательских данных.\n\n";
    
    echo "Вы действительно хотите очистить таблицу ozna_data? (yes/no): ";
    
    // Read user input
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($response) !== 'yes') {
        echo "Очистка отменена. Никакие данные не были удалены.\n";
        exit(0);
    }
    
    // Ask for final confirmation
    echo "\nПОСЛЕДНЕЕ ПРЕДУПРЕЖДЕНИЕ!\n";
    echo "Вы собираетесь удалить {$recordCount} записей из таблицы ozna_data.\n";
    echo "Пожалуйста, введите 'CONFIRM' для подтверждения: ";
    
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    
    if ($response !== 'CONFIRM') {
        echo "Очистка отменена. Никакие данные не были удалены.\n";
        exit(0);
    }
    
    // Perform cleanup
    echo "\nВыполнение очистки...\n";
    
    // Delete all records from ozna_data table
    $result = $connection->query("DELETE FROM ozna_data");
    
    if ($result === false) {
        echo "Ошибка при очистке таблицы: " . $connection->error . "\n";
        exit(1);
    }
    
    $deletedRows = $connection->affected_rows;
    
    // Reset auto increment
    $connection->query("ALTER TABLE ozna_data AUTO_INCREMENT = 1");
    
    echo "Очистка завершена успешно!\n";
    echo "Удалено записей: {$deletedRows}\n\n";
    
    // Show final status
    $result = $connection->query("SELECT COUNT(*) as count FROM ozna_data");
    $row = $result->fetch_assoc();
    $finalCount = $row['count'];
    
    echo "Текущее количество записей в таблице ozna_data: {$finalCount}\n";
    echo "Статус: " . ($finalCount == 0 ? "ТАБЛИЦА ПОЛНОСТЬЮ ОЧИЩЕНА" : "ОШИБКА: остались записи") . "\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Очистка базы данных завершена ===\n";
?>