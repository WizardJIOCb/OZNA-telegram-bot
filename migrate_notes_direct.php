<?php
/**
 * Direct Notes System Migration
 */

echo "📝 Creating project_notes table...\n";

$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Connected to database\n\n";
    
    // Create table directly
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS project_notes (
        id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Уникальный идентификатор заметки',
        
        project_id INT NOT NULL COMMENT 'ID проекта из таблицы project',
        
        user_id BIGINT NOT NULL COMMENT 'Telegram ID автора заметки',
        user_fio VARCHAR(255) NOT NULL COMMENT 'ФИО автора заметки',
        
        department VARCHAR(50) NOT NULL COMMENT 'Отдел: ШМР/ПНР/Проект',
        context_menu VARCHAR(50) NOT NULL COMMENT 'Меню откуда создана: ШМР/ПНР/Проект',
        
        stage VARCHAR(100) NOT NULL COMMENT 'Этап работ',
        note_text TEXT NOT NULL COMMENT 'Текст заметки (до 4096 символов)',
        
        status ENUM('active', 'closed') DEFAULT 'active' COMMENT 'Статус заметки',
        
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время создания',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата последнего изменения',
        
        closed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Дата и время закрытия',
        closed_by_user_id BIGINT NULL DEFAULT NULL COMMENT 'Telegram ID закрывшего',
        closed_by_fio VARCHAR(255) NULL DEFAULT NULL COMMENT 'ФИО закрывшего',
        
        INDEX idx_project_status (project_id, status) COMMENT 'Быстрый поиск активных заметок по проекту',
        INDEX idx_project_stage (project_id, stage) COMMENT 'Группировка по этапам',
        INDEX idx_user (user_id) COMMENT 'Поиск заметок автора',
        INDEX idx_created (created_at) COMMENT 'Сортировка по дате создания',
        INDEX idx_status (status) COMMENT 'Фильтрация по статусу'
        
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица заметок по проектам'
    ";
    
    echo "Creating table...\n";
    $pdo->exec($createTableSQL);
    echo "✅ Table created successfully\n\n";
    
    // Verify table
    $stmt = $pdo->query("DESCRIBE project_notes");
    $columns = $stmt->fetchAll();
    
    echo "📊 Table structure:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    
    // Show indexes
    $stmt = $pdo->query("SHOW INDEX FROM project_notes");
    $indexes = $stmt->fetchAll();
    
    echo "\n📊 Table indexes:\n";
    $printed = [];
    foreach ($indexes as $idx) {
        if (!isset($printed[$idx['Key_name']])) {
            echo "  - {$idx['Key_name']}\n";
            $printed[$idx['Key_name']] = true;
        }
    }
    
    echo "\n🎉 Migration completed successfully!\n";
    echo "The notes system is ready to use.\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    if ($e->getCode() == '42S01') {
        echo "ℹ️  Table already exists - this is OK!\n";
        
        // Still show table info
        try {
            $stmt = $pdo->query("DESCRIBE project_notes");
            $columns = $stmt->fetchAll();
            
            echo "\n📊 Existing table structure:\n";
            foreach ($columns as $col) {
                echo "  - {$col['Field']} ({$col['Type']})\n";
            }
        } catch (Exception $e2) {
            // Ignore
        }
    }
    exit(0);
}
