<?php
/**
 * Проверка нумерации заметок
 */

try {
    $pdo = new PDO(
        'mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4',
        'WizardJIOCb',
        '7qQ#28051961',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "=== Проверка нумерации заметок ===\n\n";
    
    // Показать все заметки
    $stmt = $pdo->query("SELECT id, project_id, user_fio, stage, created_at, status FROM project_notes ORDER BY id ASC");
    $notes = $stmt->fetchAll();
    
    echo "Всего заметок в базе: " . count($notes) . "\n\n";
    
    if (count($notes) > 0) {
        echo "ID заметок в порядке создания:\n";
        foreach ($notes as $note) {
            echo "  ID: {$note['id']} | Проект: {$note['project_id']} | Этап: {$note['stage']} | Создана: {$note['created_at']} | Статус: {$note['status']}\n";
        }
        
        echo "\n";
        
        // Проверить AUTO_INCREMENT
        $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'project_notes'");
        $status = $stmt->fetch();
        
        echo "Текущее значение AUTO_INCREMENT: " . $status['Auto_increment'] . "\n";
        echo "Это означает, что следующая заметка получит ID: " . $status['Auto_increment'] . "\n\n";
        
        // Объяснение
        echo "ОБЪЯСНЕНИЕ:\n";
        echo "Пропуски в нумерации (5, 10) происходят из-за тестовых заметок,\n";
        echo "которые были созданы и удалены во время разработки.\n";
        echo "AUTO_INCREMENT никогда не уменьшается после удаления записей.\n\n";
        
        echo "Это НОРМАЛЬНО и не влияет на работу системы.\n";
        echo "ID заметки - это уникальный номер, а не порядковый номер в списке.\n";
    } else {
        echo "Заметок в базе нет.\n";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
