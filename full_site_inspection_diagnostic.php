<?php

echo "=== ПОЛНАЯ ДИАГНОСТИКА ПРОВЕРКИ МОНТАЖНОЙ ПЛОЩАДКИ ===\n";
echo "Время: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Проверим все возможные пути выполнения
echo "1. ПРОВЕРКА ВСЕХ ВОЗМОЖНЫХ МЕТОДОВ createTabelMessage:\n";
echo "===============================================\n";

// Ищем все файлы, содержащие createTabelMessage
$files = [
    'CustomCommands/Services/TabelEntry.php',
    'CustomCommands/Handlers/TabelHandler.php',
    'CustomCommands/CallbackqueryCommand.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'createTabelMessage') !== false) {
            echo "   ✅ Найден createTabelMessage в: $file\n";
            
            // Проверяем, есть ли старый формат в этом файле
            if (strpos($content, 'Запись в табель от') !== false) {
                echo "   ⚠️  НАЙДЕН СТАРЫЙ ФОРМАТ в файле: $file\n";
                
                // Найдем строки со старым форматом
                $lines = explode("\n", $content);
                foreach ($lines as $lineNum => $line) {
                    if (strpos($line, 'Запись в табель от') !== false) {
                        echo "      Строка " . ($lineNum + 1) . ": " . trim($line) . "\n";
                    }
                }
            }
        }
    }
}

echo "\n2. ПОИСК ВСЕХ ФАЙЛОВ СО СТАРЫМ ФОРМАТОМ:\n";
echo "======================================\n";

// Рекурсивно ищем все PHP файлы со старым форматом
function findFilesWithOldFormat($dir) {
    $files = [];
    if (is_dir($dir)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                if (strpos($content, 'Запись в табель от') !== false) {
                    $files[] = $file->getPathname();
                }
            }
        }
    }
    return $files;
}

$suspiciousFiles = findFilesWithOldFormat('CustomCommands');
if (!empty($suspiciousFiles)) {
    echo "   ⚠️  НАЙДЕНЫ ФАЙЛЫ СО СТАРЫМ ФОРМАТОМ:\n";
    foreach ($suspiciousFiles as $file) {
        echo "      - $file\n";
    }
} else {
    echo "   ✅ Старый формат не найден в CustomCommands\n";
}

echo "\n3. ПРОВЕРКА ЛОГОВ РЕАЛЬНОГО ВРЕМЕНИ:\n";
echo "===================================\n";

// Создаем скрипт для мониторинга логов в реальном времени
$monitorScript = '<?php

echo "=== МОНИТОРИНГ ПРОВЕРКИ МОНТАЖНОЙ ПЛОЩАДКИ ===\\n";
echo "Нажмите кнопку в боте и следите за выводом...\\n\\n";

$logFiles = [
    "telegram_bot_error.log",
    "webhook_error.log", 
    "error.log"
];

$lastPositions = [];

// Инициализируем позиции
foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $lastPositions[$logFile] = filesize($logFile);
        echo "Отслеживаю: $logFile\\n";
    }
}

echo "\\nГОТОВ! Нажмите кнопку \'Проверка монтажной площадки\' в боте...\\n";
echo "================================================\\n\\n";

$startTime = time();
while (time() - $startTime < 60) { // Мониторим 60 секунд
    foreach ($logFiles as $logFile) {
        if (file_exists($logFile)) {
            $currentSize = filesize($logFile);
            if ($currentSize > $lastPositions[$logFile]) {
                // Читаем новые строки
                $handle = fopen($logFile, "r");
                fseek($handle, $lastPositions[$logFile]);
                
                echo "\\n--- НОВЫЕ ЗАПИСИ В $logFile ---\\n";
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, "АналФунд") !== false || 
                        strpos($line, "ПроверкаМонтажнойПлощадки") !== false ||
                        strpos($line, "createTabelMessage") !== false ||
                        strpos($line, "SITE INSPECTION") !== false ||
                        strpos($line, "Запись в табель от") !== false) {
                        echo $line;
                    }
                }
                echo "--- КОНЕЦ НОВЫХ ЗАПИСЕЙ ---\\n\\n";
                
                fclose($handle);
                $lastPositions[$logFile] = $currentSize;
            }
        }
    }
    usleep(500000); // 0.5 секунды
}

echo "\\nМониторинг завершен. Если не видите логов, проверьте другие лог-файлы.\\n";
';

file_put_contents('monitor_site_inspection.php', $monitorScript);
echo "   ✅ Создан скрипт monitor_site_inspection.php\n";

echo "\n4. СОЗДАНИЕ ТЕСТОВОГО СКРИПТА ДЛЯ ПРИНУДИТЕЛЬНОЙ ПРОВЕРКИ:\n";
echo "========================================================\n";

// Создаем скрипт, который принудительно вызовет все возможные пути
$testScript = '<?php

echo "=== ПРИНУДИТЕЛЬНОЕ ТЕСТИРОВАНИЕ ВСЕХ ПУТЕЙ ===\\n";

try {
    // Попробуем найти и вызвать все методы createTabelMessage
    
    // 1. Прямой вызов TabelEntry
    require_once "vendor/autoload.php";
    require_once "CustomCommands/Services/DatabaseService.php";
    require_once "CustomCommands/Services/MessageService.php";
    require_once "CustomCommands/Services/TabelEntry.php";
    
    $dbService = new Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\DatabaseService();
    $messageService = new Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\MessageService($dbService);
    $tabelEntry = new Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\TabelEntry($dbService, $messageService);
    
    echo "\\n1. ТЕСТИРОВАНИЕ TabelEntry::createTabelMessage:\\n";
    $result1 = $tabelEntry->createTabelMessage("Тест Пользователь", "05.10.2025", "ХХХ", "ПроверкаМонтажнойПлощадки");
    echo "Результат: $result1\\n\\n";
    
    // 2. Поиск других возможных источников
    echo "2. ПОИСК ДРУГИХ МЕТОДОВ createTabelMessage:\\n";
    
    $files = glob("CustomCommands/**/*.php", GLOB_BRACE);
    foreach ($files as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (strpos($content, "function createTabelMessage") !== false || 
                strpos($content, "public function createTabelMessage") !== false ||
                strpos($content, "private function createTabelMessage") !== false) {
                echo "   Найден createTabelMessage в: $file\\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\\n";
}
';

file_put_contents('test_all_paths.php', $testScript);
echo "   ✅ Создан скрипт test_all_paths.php\n";

echo "\n=== ПЛАН ДЕЙСТВИЙ ===\n";
echo "1. Запустите: php monitor_site_inspection.php\n";
echo "2. Сразу после запуска нажмите кнопку 'Проверка монтажной площадки' в боте\n";
echo "3. Посмотрите на логи в реальном времени\n";
echo "4. Запустите: php test_all_paths.php для проверки всех путей\n";
echo "5. Если нужно, мы найдем и исправим ВСЕ места с неправильным форматом\n\n";

echo "Это поможет нам точно определить, откуда идет неправильное сообщение!\n";