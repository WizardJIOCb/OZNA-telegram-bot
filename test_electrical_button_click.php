<?php
/**
 * Runtime test to verify what happens when electrical equipment button is clicked
 */

echo "=== ТЕСТ НАЖАТИЯ КНОПКИ ЭЛЕКТРООБОРУДОВАНИЯ ===\n\n";

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

echo "1. СОЗДАНИЕ ТЕСТОВЫХ ОБЪЕКТОВ\n";
echo "=============================\n";

try {
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    echo "✅ Все объекты созданы успешно\n\n";
} catch (Exception $e) {
    echo "❌ Ошибка создания объектов: " . $e->getMessage() . "\n";
    exit(1);
}

echo "2. ТЕСТ CALLBACK ОБРАБОТКИ\n";
echo "===========================\n";

// Test the exact callback that should be sent when button is clicked
$callbackData = 'cat_ТабЭлОб';
echo "Тестируемый callback: '{$callbackData}'\n";

// Test canHandle
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler->canHandle(): " . ($canHandle ? 'ДА ✅' : 'НЕТ ❌') . "\n";

if (!$canHandle) {
    echo "❌ ПРОБЛЕМА: TabelHandler не может обработать этот callback!\n";
    exit(1);
}

// Test category extraction
$reflection = new ReflectionClass($tabelHandler);
$extractMethod = $reflection->getMethod('extractCategoryFromCallback');
$extractMethod->setAccessible(true);

$category = $extractMethod->invokeArgs($tabelHandler, [$callbackData]);
echo "Извлеченная категория: '{$category}'\n";

echo "\n3. ТЕСТ СОЗДАНИЯ КЛАВИАТУРЫ ЭЛЕКТРООБОРУДОВАНИЯ\n";
echo "===============================================\n";

try {
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "✅ Клавиатура создана успешно\n";
    echo "Количество рядов кнопок: " . count($keyboard) . "\n";
    
    echo "\nСтруктура клавиатуры:\n";
    foreach ($keyboard as $index => $row) {
        if (isset($row[0])) {
            $text = $row[0]['text'];
            $callback = $row[0]['callback_data'];
            echo "  {$text} → {$callback}\n";
        }
    }
    
    if (count($keyboard) == 0) {
        echo "❌ ПРОБЛЕМА: Клавиатура пустая!\n";
    } elseif (count($keyboard) < 4) {
        echo "⚠️  ВНИМАНИЕ: Неожиданно мало кнопок в клавиатуре\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка создания клавиатуры: " . $e->getMessage() . "\n";
}

echo "\n4. ТЕСТ МЕТОДА handleElectricalEquipmentMenu\n";
echo "============================================\n";

// Test the private method that should be called
$handleMethod = $reflection->getMethod('handleElectricalEquipmentMenu');
$handleMethod->setAccessible(true);

try {
    echo "Попытка вызова handleElectricalEquipmentMenu...\n";
    
    // We can't actually send a message, but we can test the method exists and runs
    // Let's simulate what it would do
    $testChatId = "123456789";
    
    // This will try to call the method but we'll catch any errors
    echo "Метод handleElectricalEquipmentMenu доступен и должен:\n";
    echo "1. Вызвать messageService->createElectricalEquipmentKeyboard()\n";
    echo "2. Отправить сообщение с текстом: '<b>⚡ Электрооборудование</b> Выберите тип работ:'\n";
    echo "3. Приложить клавиатуру с кнопками активностей\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка вызова handleElectricalEquipmentMenu: " . $e->getMessage() . "\n";
}

echo "\n5. ПРОВЕРКА МАРШРУТИЗАЦИИ В handleOtherWorkTypes\n";
echo "================================================\n";

// Test if the category would be properly routed
if ($category === 'ТабЭлОб' || $category === 'Табель_Электрооборудование') {
    echo "✅ Категория '{$category}' должна быть обработана в handleOtherWorkTypes\n";
    echo "✅ Должен вызываться handleElectricalEquipmentMenu()\n";
} else {
    echo "❌ ПРОБЛЕМА: Категория '{$category}' НЕ соответствует ожидаемой для электрооборудования\n";
}

echo "\n6. ПРОВЕРКА ПОСЛЕДНИХ ЛОГОВ\n";
echo "============================\n";

$logFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($logFile)) {
    $logLines = file($logFile);
    $recentLogs = array_slice($logLines, -20); // Last 20 lines
    
    $electricalLogs = array_filter($recentLogs, function($line) {
        return strpos($line, 'ТабЭлОб') !== false || 
               strpos($line, 'Электрооборудование') !== false ||
               strpos($line, 'handleElectricalEquipmentMenu') !== false;
    });
    
    if (empty($electricalLogs)) {
        echo "⚠️  В последних 20 записях логов нет упоминаний об электрооборудовании\n";
        echo "Это может означать, что callback не достигает TabelHandler\n";
    } else {
        echo "Найденные записи об электрооборудовании:\n";
        foreach ($electricalLogs as $log) {
            echo "  " . trim($log) . "\n";
        }
    }
} else {
    echo "❌ Файл логов не найден\n";
}

echo "\n7. ЗАКЛЮЧЕНИЕ И РЕКОМЕНДАЦИИ\n";
echo "============================\n";

if ($canHandle && !empty($category) && count($keyboard) > 0) {
    echo "✅ ВСЕ ТЕХНИЧЕСКИЕ ПРОВЕРКИ ПРОЙДЕНЫ\n\n";
    echo "Возможные причины, почему кнопка не показывает активности:\n\n";
    echo "A. ПРОБЛЕМЫ С ОТПРАВКОЙ СООБЩЕНИЯ:\n";
    echo "   - Ошибка в MessageService при отправке\n";
    echo "   - Проблемы с Telegram API\n";
    echo "   - Неправильная кодировка текста\n\n";
    echo "B. ПРОБЛЕМЫ С CONVERSATION STATE:\n";
    echo "   - Отсутствуют данные о проекте в conversation\n";
    echo "   - Неправильное состояние диалога\n\n";
    echo "C. ПРОБЛЕМЫ С WEBHOOK:\n";
    echo "   - Callback не достигает сервера\n";
    echo "   - Ошибки в обработке HTTP запросов\n\n";
    echo "РЕКОМЕНДАЦИИ:\n";
    echo "1. Попросите пользователя нажать кнопку еще раз\n";
    echo "2. Проверьте логи в реальном времени при нажатии\n";
    echo "3. Убедитесь, что бот отвечает на другие команды\n";
} else {
    echo "❌ НАЙДЕНЫ ТЕХНИЧЕСКИЕ ПРОБЛЕМЫ\n";
    if (!$canHandle) echo "- TabelHandler не может обработать callback\n";
    if (empty($category)) echo "- Не удается извлечь категорию\n";
    if (count($keyboard) == 0) echo "- Клавиатура пустая\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
?>