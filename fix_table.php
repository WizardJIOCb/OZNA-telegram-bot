<?php
echo "====================================\n";
echo "Fixing project_notes table encoding\n";
echo "====================================\n\n";

try {
    echo "Connecting to database...\n";
    $pdo = new PDO(
        'mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4',
        'WizardJIOCb',
        '7qQ#28051961'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected.\n\n";
    
    echo "Dropping old table...\n";
    $pdo->exec('DROP TABLE IF EXISTS project_notes');
    echo "✓ Old table dropped.\n\n";
    
    echo "Creating new table with UTF-8 encoding...\n";
    $sql = "CREATE TABLE project_notes (
        id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Уникальный идентификатор заметки',
        project_id VARCHAR(50) NOT NULL COMMENT 'Код проекта',
        user_id BIGINT NOT NULL COMMENT 'Telegram ID автора заметки',
        user_fio VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ФИО автора заметки',
        department VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Отдел: ШМР/ПНР/Проект',
        context_menu VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Меню откуда создана: ШМР/ПНР/Проект',
        stage VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Этап работ',
        note_text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Текст заметки (до 4096 символов)',
        status ENUM('active', 'closed') DEFAULT 'active' COMMENT 'Статус заметки',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время создания',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата последнего изменения',
        closed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Дата и время закрытия',
        closed_by_user_id BIGINT NULL DEFAULT NULL COMMENT 'Telegram ID закрывшего',
        closed_by_fio VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'ФИО закрывшего',
        INDEX idx_project_status (project_id, status) COMMENT 'Быстрый поиск активных заметок по проекту',
        INDEX idx_project_stage (project_id, stage) COMMENT 'Группировка по этапам',
        INDEX idx_user (user_id) COMMENT 'Поиск заметок автора',
        INDEX idx_created (created_at) COMMENT 'Сортировка по дате создания',
        INDEX idx_status (status) COMMENT 'Фильтрация по статусу'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица заметок по проектам'";
    
    $pdo->exec($sql);
    echo "✓ New table created with UTF-8 encoding.\n\n";
    
    echo "====================================\n";
    echo "SUCCESS!\n";
    echo "====================================\n";
    echo "Table recreated with proper UTF-8 encoding.\n";
    echo "You can now create new notes - they will be saved correctly!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
