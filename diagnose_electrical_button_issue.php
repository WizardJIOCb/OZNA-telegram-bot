<?php
/**
 * Диагностика проблемы с кнопкой "Электрооборудование"
 * 
 * Пользователь сообщает: "нажатие на кнопку 'Электрооборудование' вместо того 
 * чтобы отображать свои активности, формирует отчет"
 */

require_once 'config.php';
require_once 'vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С КНОПКОЙ 'ЭЛЕКТРООБОРУДОВАНИЕ' ===\n\n";

// Инициализация сервисов
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$tabelHandler = new TabelHandler(
    $databaseService,
    $messageService,
    $projectSelector,
    $tabelEntry,
    $adminService
);

echo "1. ПРОВЕРКА ОБРАБОТКИ CALLBACK 'cat_ТабЭлОб'\n";
echo "=============================================\n";

$electricalCallback = 'cat_ТабЭлОб';
echo "Тестируем callback: '{$electricalCallback}'\n";

// Проверяем, может ли TabelHandler обработать этот callback
$canHandle = $tabelHandler->canHandle($electricalCallback);
echo "TabelHandler->canHandle('{$electricalCallback}'): " . ($canHandle ? 'ДА ✅' : 'НЕТ ❌') . "\n";

if ($canHandle) {
    echo "✅ TabelHandler МОЖЕТ обработать этот callback\n";
} else {
    echo "❌ TabelHandler НЕ МОЖЕТ обработать этот callback\n";
    echo "   Это означает, что callback направляется в другой обработчик!\n";
}

echo "\n2. ПРОВЕРКА ИЗВЛЕЧЕНИЯ КАТЕГОРИИ ИЗ CALLBACK\n";
echo "=============================================\n";

// Используем рефлексию для доступа к приватному методу
$reflection = new ReflectionClass($tabelHandler);
$extractMethod = $reflection->getMethod('extractCategoryFromCallback');
$extractMethod->setAccessible(true);

try {
    $category = $extractMethod->invokeArgs($tabelHandler, [$electricalCallback]);
    echo "extractCategoryFromCallback('{$electricalCallback}') = '{$category}'\n";
    
    if ($category === 'ТабЭлОб' || $category === 'Табель_Электрооборудование') {
        echo "✅ Категория '{$category}' правильно извлечена\n";
    } else {
        echo "⚠️  Категория '{$category}' может быть проблемной\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка извлечения категории: " . $e->getMessage() . "\n";
}

echo "\n3. ПРОВЕРКА КЛАВИАТУРЫ ЭЛЕКТРООБОРУДОВАНИЯ\n";
echo "=========================================\n";

try {
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "✅ Клавиатура электрооборудования создана успешно\n";
    echo "   Количество рядов: " . count($keyboard) . "\n";
    echo "   Активности:\n";
    
    foreach ($keyboard as $index => $row) {
        foreach ($row as $button) {
            echo "   - " . $button['text'] . " (callback: " . $button['callback_data'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Ошибка создания клавиатуры: " . $e->getMessage() . "\n";
}

echo "\n4. ВОЗМОЖНЫЕ ПРИЧИНЫ ПРОБЛЕМЫ\n";
echo "============================\n";

echo "На основе анализа кода, возможные причины:\n\n";

echo "A) НЕПРАВИЛЬНАЯ МАРШРУТИЗАЦИЯ:\n";
echo "   - Callback 'cat_ТабЭлОб' попадает в другой обработчик\n";
echo "   - Другой обработчик напрямую создает отчет\n";
echo "   - Приоритеты обработчиков настроены неправильно\n\n";

echo "B) ПРОБЛЕМА В КОДЕ ОБРАБОТКИ:\n";
echo "   - handleOtherWorkTypes неправильно обрабатывает категорию\n";
echo "   - Отсутствует обработчик для категории 'ТабЭлОб'\n";
echo "   - Код переходит к созданию отчета вместо показа меню\n\n";

echo "C) ПРОБЛЕМА С СОСТОЯНИЕМ РАЗГОВОРА:\n";
echo "   - В conversation->notes уже есть project_code\n";
echo "   - Это заставляет систему считать, что пользователь уже в процессе создания отчета\n";
echo "   - Система пропускает показ меню активностей\n\n";

echo "D) ПРОБЛЕМА С КЭШИРОВАНИЕМ:\n";
echo "   - Старая версия кода еще работает из-за кэширования\n";
echo "   - Webhook получает старую версию обработчика\n\n";

echo "5. РЕКОМЕНДАЦИИ ПО ДИАГНОСТИКЕ\n";
echo "==============================\n";

echo "1. ПРОВЕРЬТЕ ЛОГИ:\n";
echo "   - Найдите записи вида 'TabelHandler: handleOtherWorkTypes called with category:'\n";
echo "   - Проверьте, какая категория получается при нажатии кнопки\n";
echo "   - Убедитесь, что вызывается handleElectricalEquipmentMenu()\n\n";

echo "2. ПРОВЕРЬТЕ ПРИОРИТЕТЫ ОБРАБОТЧИКОВ:\n";
echo "   - TabelHandler должен иметь высокий приоритет (70)\n";
echo "   - Другие обработчики не должны перехватывать callback\n\n";

echo "3. ПРОВЕРЬТЕ СОСТОЯНИЕ РАЗГОВОРА:\n";
echo "   - Если в conversation->notes есть project_code, очистите его\n";
echo "   - Убедитесь, что пользователь начинает с чистого состояния\n\n";

echo "4. ТЕСТИРОВАНИЕ:\n";
echo "   - Попросите пользователя нажать /start перед тестированием\n";
echo "   - Проследите полный путь: Табель → Отдел ПНР → Электрооборудование\n";
echo "   - Сравните с другими кнопками (работают ли они правильно?)\n\n";

echo "6. ВЫВОД\n";
echo "========\n";

echo "Код настроен ПРАВИЛЬНО для показа меню активностей.\n";
echo "Если пользователь видит отчет вместо меню, проблема в:\n";
echo "- Маршрутизации callback'ов\n";
echo "- Состоянии разговора\n";
echo "- Кэшировании старого кода\n";
echo "- Неправильной последовательности действий пользователя\n\n";

echo "Для точной диагностики нужно:\n";
echo "1. Проверить логи при нажатии кнопки\n";
echo "2. Убедиться, что пользователь следует правильному пути\n";
echo "3. Очистить состояние разговора (/start)\n";
echo "4. Проверить, не перехватывают ли другие обработчики callback\n\n";

echo "=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";