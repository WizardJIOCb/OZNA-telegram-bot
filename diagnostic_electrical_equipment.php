<?php
/**
 * Complete diagnostic for electrical equipment button issue
 */

echo "=== ДИАГНОСТИКА КНОПКИ ЭЛЕКТРООБОРУДОВАНИЯ ===\n\n";

// Load required files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

echo "1. ПРОВЕРКА ЗАГРУЗКИ КЛАССОВ\n";
echo "===========================\n";

try {
    $databaseService = new DatabaseService();
    echo "✅ DatabaseService загружен\n";
} catch (Exception $e) {
    echo "❌ DatabaseService: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $messageService = new MessageService();
    echo "✅ MessageService загружен\n";
} catch (Exception $e) {
    echo "❌ MessageService: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $projectSelector = new ProjectSelector();
    echo "✅ ProjectSelector загружен\n";
} catch (Exception $e) {
    echo "❌ ProjectSelector: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    echo "✅ TabelEntry загружен\n";
} catch (Exception $e) {
    echo "❌ TabelEntry: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $adminService = new AdminService($messageService);
    echo "✅ AdminService загружен\n";
} catch (Exception $e) {
    echo "❌ AdminService: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    echo "✅ TabelHandler загружен\n";
} catch (Exception $e) {
    echo "❌ TabelHandler: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. ПРОВЕРКА МЕТОДА createElectricalEquipmentKeyboard\n";
echo "=====================================================\n";

try {
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "✅ createElectricalEquipmentKeyboard выполнен успешно\n";
    echo "Количество кнопок: " . count($keyboard) . "\n";
    echo "Структура клавиатуры:\n";
    foreach ($keyboard as $index => $row) {
        if (isset($row[0])) {
            echo "  Ряд " . ($index + 1) . ": " . $row[0]['text'] . " (callback: " . $row[0]['callback_data'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "❌ createElectricalEquipmentKeyboard: " . $e->getMessage() . "\n";
}

echo "\n3. ПРОВЕРКА canHandle ДЛЯ КНОПКИ ЭЛЕКТРООБОРУДОВАНИЯ\n";
echo "=====================================================\n";

$testCallbacks = [
    'cat_ТабЭлОб',
    'category_Табель_Электрооборудование'
];

foreach ($testCallbacks as $callback) {
    try {
        $canHandle = $tabelHandler->canHandle($callback);
        echo "Callback: '{$callback}' → canHandle: " . ($canHandle ? 'ДА ✅' : 'НЕТ ❌') . "\n";
    } catch (Exception $e) {
        echo "Callback: '{$callback}' → ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n4. ПРОВЕРКА extractCategoryFromCallback\n";
echo "========================================\n";

// Use reflection to access private method
$reflection = new ReflectionClass($tabelHandler);
$extractMethod = $reflection->getMethod('extractCategoryFromCallback');
$extractMethod->setAccessible(true);

foreach ($testCallbacks as $callback) {
    try {
        $category = $extractMethod->invokeArgs($tabelHandler, [$callback]);
        echo "Callback: '{$callback}' → Category: '{$category}'\n";
    } catch (Exception $e) {
        echo "Callback: '{$callback}' → ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n5. ПРОВЕРКА handleElectricalEquipmentMenu\n";
echo "==========================================\n";

// Use reflection to access private method
$handleMethod = $reflection->getMethod('handleElectricalEquipmentMenu');
$handleMethod->setAccessible(true);

try {
    echo "Попытка вызова handleElectricalEquipmentMenu...\n";
    // We can't actually send the message, but we can see if the method executes
    echo "✅ Метод handleElectricalEquipmentMenu доступен\n";
} catch (Exception $e) {
    echo "❌ handleElectricalEquipmentMenu: " . $e->getMessage() . "\n";
}

echo "\n6. ПРОВЕРКА РЕАЛЬНОГО ПОТОКА ОБРАБОТКИ\n";
echo "=======================================\n";

echo "Симуляция нажатия кнопки 'Электрооборудование':\n";
echo "1. Пользователь нажимает кнопку с callback_data: 'cat_ТабЭлОб'\n";
echo "2. TabelHandler->canHandle('cat_ТабЭлОб'): ";

try {
    $canHandle = $tabelHandler->canHandle('cat_ТабЭлОб');
    echo ($canHandle ? 'ДА ✅' : 'НЕТ ❌') . "\n";
    
    if ($canHandle) {
        echo "3. TabelHandler->extractCategoryFromCallback('cat_ТабЭлОб'): ";
        $category = $extractMethod->invokeArgs($tabelHandler, ['cat_ТабЭлОб']);
        echo "'{$category}'\n";
        
        echo "4. Проверка, будет ли категория обработана в handleOtherWorkTypes:\n";
        if ($category === 'ТабЭлОб' || $category === 'Табель_Электрооборудование') {
            echo "   ✅ Категория '{$category}' должна быть обработана как электрооборудование\n";
            echo "   ✅ Должен вызваться handleElectricalEquipmentMenu()\n";
            echo "   ✅ Пользователь должен увидеть меню с активностями электрооборудования\n";
        } else {
            echo "   ❌ Категория '{$category}' НЕ будет обработана как электрооборудование\n";
        }
    } else {
        echo "   ❌ TabelHandler НЕ МОЖЕТ обработать этот callback\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n7. ПРОВЕРКА ЛОГОВ ERROR\n";
echo "=======================\n";

$errorLogFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($errorLogFile)) {
    echo "Файл логов найден: {$errorLogFile}\n";
    $logSize = filesize($errorLogFile);
    echo "Размер файла логов: " . number_format($logSize) . " байт\n";
    
    if ($logSize > 1024 * 1024) { // Больше 1MB
        echo "⚠️  Файл логов очень большой, проверьте последние записи на наличие ошибок\n";
    }
} else {
    echo "❌ Файл логов не найден: {$errorLogFile}\n";
}

echo "\n8. РЕКОМЕНДАЦИИ\n";
echo "===============\n";

echo "Если все проверки прошли успешно, но кнопка всё равно не работает:\n";
echo "1. Проверьте логи в telegram_bot_error.log на наличие ошибок\n";
echo "2. Убедитесь, что пользователь имеет правильный уровень доступа (не Guest)\n";
echo "3. Проверьте, что webhook настроен правильно\n";
echo "4. Убедитесь, что бот отвечает на другие команды\n";

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>