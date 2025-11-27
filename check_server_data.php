<?php
/**
 * Скрипт для проверки состояния данных на сервере
 * Запустите этот скрипт на сервере после деплоя
 */

echo "=== ПРОВЕРКА ДАННЫХ НА СЕРВЕРЕ ===\n\n";

// Проверяем файлы данных
$dataFiles = [
    'data/projects.json' => 'Проекты',
    'data/access_requests.json' => 'Заявки на доступ'
];

echo "1. ПРОВЕРКА ФАЙЛОВ ДАННЫХ:\n";
echo "--------------------------\n";

$allOk = true;

foreach ($dataFiles as $file => $description) {
    echo "{$description} ({$file}): ";
    
    if (!file_exists($file)) {
        echo "❌ ФАЙЛ НЕ НАЙДЕН!\n";
        $allOk = false;
        continue;
    }
    
    if (!is_readable($file)) {
        echo "❌ НЕТ ПРАВ НА ЧТЕНИЕ!\n";
        $allOk = false;
        continue;
    }
    
    $content = file_get_contents($file);
    if ($content === false) {
        echo "❌ ОШИБКА ЧТЕНИЯ ФАЙЛА!\n";
        $allOk = false;
        continue;
    }
    
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ НЕВЕРНЫЙ JSON: " . json_last_error_msg() . "\n";
        $allOk = false;
        continue;
    }
    
    $size = filesize($file);
    $count = is_array($data) ? count($data) : 'не массив';
    $modified = date('Y-m-d H:i:s', filemtime($file));
    
    echo "✅ ОК ({$size} байт, {$count} элементов, изменён {$modified})\n";
}

echo "\n2. ПРОВЕРКА РАБОТЫ AdminService:\n";
echo "--------------------------------\n";

try {
    // Попытаемся загрузить AdminService
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
    
    // Создаём mock MessageService для тестирования
    class TestMessageService extends \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService {
        public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): \Longman\TelegramBot\Entities\ServerResponse {
            return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true, 'result' => true]);
        }
    }
    
    $messageService = new TestMessageService();
    $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
    
    echo "✅ AdminService успешно создан\n";
    
    // Проверяем загрузку проектов
    $projects = $adminService->getAllProjects();
    echo "✅ Проектов загружено: " . count($projects) . "\n";
    
    if (count($projects) > 0) {
        echo "   Первые 5 проектов: " . implode(', ', array_slice($projects, 0, 5)) . "\n";
        echo "   Последние 5 проектов: " . implode(', ', array_slice($projects, -5)) . "\n";
    }
    
    // Проверяем заявки на доступ
    $requests = $adminService->getAllAccessRequests();
    echo "✅ Заявок на доступ: " . count($requests) . "\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка загрузки AdminService: " . $e->getMessage() . "\n";
    $allOk = false;
}

echo "\n3. ПРОВЕРКА ПРАВ ДОСТУПА:\n";
echo "-------------------------\n";

$dataDir = __DIR__ . '/data';
if (is_dir($dataDir)) {
    echo "Папка data: ✅ существует\n";
    echo "Права на запись в data: " . (is_writable($dataDir) ? "✅ есть" : "❌ нет") . "\n";
} else {
    echo "Папка data: ❌ не существует!\n";
    $allOk = false;
}

foreach ($dataFiles as $file => $description) {
    if (file_exists($file)) {
        echo "Права на запись в {$file}: " . (is_writable($file) ? "✅ есть" : "❌ нет") . "\n";
    }
}

// Итоговый результат
echo "\n" . str_repeat("=", 50) . "\n";
if ($allOk) {
    echo "✅ ВСЁ В ПОРЯДКЕ! Данные должны сохраняться корректно.\n";
} else {
    echo "❌ ОБНАРУЖЕНЫ ПРОБЛЕМЫ! Требуется исправление.\n";
    echo "\nВозможные решения:\n";
    echo "1. Скопируйте файлы из локальной папки data/\n";
    echo "2. Проверьте права доступа: chmod 755 data && chmod 644 data/*.json\n";
    echo "3. Убедитесь, что веб-сервер может писать в папку data/\n";
}
echo str_repeat("=", 50) . "\n";
?>