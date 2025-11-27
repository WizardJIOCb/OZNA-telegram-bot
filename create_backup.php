<?php
/**
 * Автоматическое создание бэкапа данных перед деплоем
 */

echo "=== СОЗДАНИЕ БЭКАПА ДАННЫХ ===\n\n";

$backupDir = __DIR__ . '/backups';
$timestamp = date('Y-m-d_H-i-s');

// Создаём папку для бэкапов
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "✅ Создана папка для бэкапов: {$backupDir}\n";
}

// Файлы для бэкапа
$filesToBackup = [
    'data/projects.json',
    'data/access_requests.json'
];

echo "Создание бэкапов:\n";
foreach ($filesToBackup as $file) {
    if (file_exists($file)) {
        $backupFile = $backupDir . '/' . basename($file, '.json') . '_' . $timestamp . '.json';
        if (copy($file, $backupFile)) {
            echo "✅ {$file} → {$backupFile}\n";
        } else {
            echo "❌ Ошибка копирования {$file}\n";
        }
    } else {
        echo "⚠️ Файл {$file} не найден\n";
    }
}

echo "\n=== СПИСОК СОЗДАННЫХ БЭКАПОВ ===\n";
$backups = glob($backupDir . '/*_' . $timestamp . '.json');
foreach ($backups as $backup) {
    $size = filesize($backup);
    echo "📁 " . basename($backup) . " ({$size} байт)\n";
}

echo "\n✅ Бэкап завершён! Теперь можно безопасно делать деплой.\n";
?>