<?php
// Временный скрипт для выполнения миграции
try {
    $pdo = new PDO(
        'mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4',
        'WizardJIOCb',
        '7qQ#28051961',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $sql = file_get_contents(__DIR__ . '/database/migrations/create_claims_table.sql');
    $pdo->exec($sql);
    
    // Проверяем что таблица создана
    $stmt = $pdo->query("SHOW TABLES LIKE 'claims'");
    $table = $stmt->fetch();
    
    if ($table) {
        echo "OK: Таблица 'claims' успешно создана\n";
        
        // Показываем структуру
        $stmt = $pdo->query("DESCRIBE claims");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "\nСтруктура таблицы:\n";
        foreach ($columns as $col) {
            echo "  {$col['Field']} - {$col['Type']}" . ($col['Null'] === 'NO' ? ' NOT NULL' : '') . "\n";
        }
    } else {
        echo "ERROR: Таблица не найдена после выполнения миграции\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
