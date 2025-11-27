<?php
/**
 * Скрипт для синхронизации данных с сервером
 * Используйте этот скрипт перед каждым деплоем
 */

echo "=== СИНХРОНИЗАЦИЯ ДАННЫХ С СЕРВЕРОМ ===\n\n";

// Проверяем локальные файлы данных
$dataFiles = [
    'data/projects.json' => 'Проекты',
    'data/access_requests.json' => 'Заявки на доступ'
];

echo "1. ПРОВЕРКА ЛОКАЛЬНЫХ ФАЙЛОВ:\n";
echo "-----------------------------\n";

foreach ($dataFiles as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        $modified = date('Y-m-d H:i:s', filemtime($file));
        echo "✅ {$description} ({$file}): {$size} байт, изменён {$modified}\n";
        
        // Показываем содержимое для projects.json
        if ($file === 'data/projects.json') {
            $content = file_get_contents($file);
            $projects = json_decode($content, true);
            if (is_array($projects)) {
                echo "   Проектов в файле: " . count($projects) . "\n";
                echo "   Последние 5 проектов: " . implode(', ', array_slice($projects, -5)) . "\n";
            }
        }
    } else {
        echo "❌ {$description} ({$file}): ФАЙЛ НЕ НАЙДЕН!\n";
    }
}

echo "\n2. КОМАНДЫ ДЛЯ КОПИРОВАНИЯ НА СЕРВЕР:\n";
echo "-------------------------------------\n";
echo "# Скопируйте эти команды и выполните их:\n\n";

// Генерируем команды для копирования (замените на ваши данные сервера)
$serverUser = "your_user";
$serverHost = "your_server.com";
$serverPath = "/path/to/your/bot";

echo "# 1. Создайте папку data на сервере (если не существует):\n";
echo "ssh {$serverUser}@{$serverHost} 'mkdir -p {$serverPath}/data'\n\n";

echo "# 2. Скопируйте файлы данных:\n";
foreach ($dataFiles as $file => $description) {
    if (file_exists($file)) {
        echo "scp {$file} {$serverUser}@{$serverHost}:{$serverPath}/{$file}\n";
    }
}

echo "\n# 3. Установите правильные права доступа:\n";
echo "ssh {$serverUser}@{$serverHost} 'chmod 755 {$serverPath}/data && chmod 644 {$serverPath}/data/*.json'\n\n";

echo "3. ПРОВЕРКА ПОСЛЕ КОПИРОВАНИЯ:\n";
echo "-------------------------------\n";
echo "После копирования выполните на сервере:\n";
echo "php -r \"echo 'Проектов: ' . count(json_decode(file_get_contents('data/projects.json'), true)) . PHP_EOL;\"\n\n";

echo "4. РЕЗЕРВНОЕ КОПИРОВАНИЕ:\n";
echo "-------------------------\n";
$backupName = 'backup_' . date('Y-m-d_H-i-s');
echo "# Создайте резервную копию перед деплоем:\n";
echo "ssh {$serverUser}@{$serverHost} 'cp -r {$serverPath}/data {$serverPath}/{$backupName}_data'\n\n";

echo "=== ИНСТРУКЦИИ ЗАВЕРШЕНЫ ===\n";
echo "После выполнения этих команд ваши проекты не должны пропадать при деплое.\n";
?>