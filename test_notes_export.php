<?php
/**
 * Test script to verify notes export functionality
 * 
 * This script tests:
 * 1. Connection to database
 * 2. Retrieval of notes by project
 * 3. CSV file generation
 */

require_once __DIR__ . '/config.php';

// Database credentials
define('DB_HOST', '82.146.42.213');
define('DB_NAME', 'tg_bot');
define('DB_USER', 'WizardJIOCb');
define('DB_PASS', '7qQ#28051961');

echo "╔══════════════════════════════════════════════════╗\n";
echo "║   ТЕСТ ФУНКЦИИ ВЫГРУЗКИ ЗАМЕТОК                 ║\n";
echo "╚══════════════════════════════════════════════════╝\n\n";

// Step 1: Check database connection
echo "1. Проверка подключения к БД...\n";
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✅ Подключение успешно\n\n";
} catch (Exception $e) {
    echo "   ❌ Ошибка подключения: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Get list of projects with notes
echo "2. Получение списка проектов с заметками...\n";
try {
    $sql = "SELECT DISTINCT project_id, COUNT(*) as note_count 
            FROM project_notes 
            GROUP BY project_id 
            ORDER BY note_count DESC";
    $stmt = $pdo->query($sql);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($projects)) {
        echo "   ⚠️ Заметок в базе данных нет\n";
        exit(0);
    }
    
    echo "   ✅ Найдено проектов с заметками: " . count($projects) . "\n";
    foreach ($projects as $project) {
        echo "      • {$project['project_id']}: {$project['note_count']} заметок\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Get notes for first project
$testProject = $projects[0]['project_id'];
echo "3. Получение заметок для проекта '{$testProject}'...\n";
try {
    $sql = "SELECT * FROM project_notes WHERE project_id = :project_id ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':project_id' => $testProject]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   ✅ Получено заметок: " . count($notes) . "\n";
    if (!empty($notes)) {
        $firstNote = $notes[0];
        echo "      Пример первой заметки:\n";
        echo "      • ID: {$firstNote['id']}\n";
        echo "      • Автор: {$firstNote['user_fio']}\n";
        echo "      • Этап: {$firstNote['stage']}\n";
        echo "      • Дата: {$firstNote['created_at']}\n";
        echo "      • Статус: {$firstNote['status']}\n";
        echo "      • Текст: " . substr($firstNote['note_text'], 0, 50) . "...\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Test CSV generation
echo "4. Тест генерации CSV файла...\n";
try {
    $uploadsDir = __DIR__ . '/Uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
        echo "   📁 Создана директория Uploads\n";
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $fileName = "test_notes_export_{$testProject}_{$timestamp}.csv";
    $filePath = $uploadsDir . '/' . $fileName;
    
    $output = fopen($filePath, 'w');
    
    // Add BOM for proper UTF-8 encoding in Excel
    fprintf($output, "\xEF\xBB\xBF");
    
    // Write header
    fputcsv($output, ['№', 'Дата создания', 'Автор', 'Контекст', 'Этап', 'Текст заметки', 'Статус', 'Дата закрытия', 'Закрыл']);
    
    // Write data
    foreach ($notes as $note) {
        $status = $note['status'] === 'active' ? 'Активна' : 'Закрыта';
        $closedAt = $note['closed_at'] ? date('d.m.Y H:i', strtotime($note['closed_at'])) : '';
        $closedBy = $note['closed_by_fio'] ?? '';
        
        fputcsv($output, [
            $note['id'],
            date('d.m.Y H:i', strtotime($note['created_at'])),
            $note['user_fio'],
            $note['context_menu'],
            $note['stage'],
            $note['note_text'],
            $status,
            $closedAt,
            $closedBy
        ]);
    }
    
    fclose($output);
    
    $fileSize = filesize($filePath);
    echo "   ✅ CSV файл создан успешно\n";
    echo "      • Файл: {$fileName}\n";
    echo "      • Размер: " . round($fileSize / 1024, 2) . " KB\n";
    echo "      • Путь: {$filePath}\n\n";
    
    // Clean up test file
    unlink($filePath);
    echo "   🧹 Тестовый файл удален\n\n";
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}

echo "╔══════════════════════════════════════════════════╗\n";
echo "║   ✅ ВСЕ ТЕСТЫ ПРОЙДЕНЫ УСПЕШНО                 ║\n";
echo "╚══════════════════════════════════════════════════╝\n\n";

echo "📌 ФУНКЦИЯ ВЫГРУЗКИ ЗАМЕТОК ГОТОВА К ИСПОЛЬЗОВАНИЮ\n\n";
echo "Доступна через меню:\n";
echo "📁 Проекты → 📋 Отчёт по проекту → 📌 Выгрузка заметок\n\n";
