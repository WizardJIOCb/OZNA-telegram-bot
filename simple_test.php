<?php

// Простой тест для проверки логики обработки callback'ов
echo "Testing callback processing logic\n";

$category = 'Табель_Мобильная_Бригада_ДатаСегодня';

echo "Category: $category\n";

// Проверка условий, как в методе handleDateSelection
if (str_contains($category, 'Текущая')) {
    echo "Matched: Текущая деятельность\n";
} elseif (str_contains($category, 'Пусконаладка') || str_contains($category, 'ПНЛ')) {
    echo "Matched: Пусконаладка\n";
} elseif (str_contains($category, 'Шеф-монтаж') || str_contains($category, 'ШМ')) {
    echo "Matched: Шеф-монтаж\n";
} elseif (str_contains($category, 'Мобильная_Бригада')) {
    echo "Matched: Мобильная бригада (NEW)\n";
} else {
    echo "Matched: Other (default)\n";
}

echo "Test completed.\n";

// Simple test to check if handlers can process callback data correctly

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/FunctionalDirectionHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/HelpHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/SupervisorHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Test TabelHandler directly
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test callback data that should be handled by TabelHandler
$callbackData = 'category_Табель_Дорога';
$category = 'Табель_Дорога'; // This is what CallbackHandlerFactory would extract

echo "Testing TabelHandler with callback data: {$callbackData}\n";
echo "Extracted category: {$category}\n";

// Check if TabelHandler can handle this callback data
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

if ($canHandle) {
    echo "TabelHandler would handle this callback correctly\n";
} else {
    echo "TabelHandler cannot handle this callback - there may be an issue\n";
}

// Test other callback data
$testCallbacks = [
    'category_Табель_Подготовка',
    'category_Табель_Инструктаж',
    'category_Табель_Ожидание',
    'category_Табель_Гарантия',
    'category_Табель_Простой',
    'category_Табель_Мобильная_Бригада_Дата',
    'category_Табель_Шеф_монтаж',
    'category_Табель_Пусконаладка',
    'category_Табель_Текущая'
];

foreach ($testCallbacks as $callback) {
    $canHandle = $tabelHandler->canHandle($callback);
    echo "Callback: {$callback} - Can handle: " . ($canHandle ? 'true' : 'false') . "\n";
}

?>
```

```
<?php
// Simple test to check for duplicated conditions in TabelHandler.php

$content = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Check for duplicated callback data conditions
$pattern = "/if \(.*?callbackData === 'cat_ТабЭлОб_РабПоГр_yes'.*?callbackData === 'cat_ТабЭлОб_РабПоГр_yes'/s";
if (preg_match($pattern, $content)) {
    echo "ERROR: Found duplicated condition for 'cat_ТабЭлОб_РабПоГр_yes'\n";
} else {
    echo "OK: No duplication found for 'cat_ТабЭлОб_РабПоГр_yes'\n";
}

$pattern = "/if \(.*?callbackData === 'cat_ТабЭлОб_ФормИД_yes'.*?callbackData === 'cat_ТабЭлОб_ФормИД_yes'/s";
if (preg_match($pattern, $content)) {
    echo "ERROR: Found duplicated condition for 'cat_ТабЭлОб_ФормИД_yes'\n";
} else {
    echo "OK: No duplication found for 'cat_ТабЭлОб_ФормИД_yes'\n";
}

// Check for duplicated actualCategory conditions
$pattern = "/if \(.*?actualCategory === 'cat_ТабЭлОб_РабПоГр_yes'.*?actualCategory === 'cat_ТабЭлОб_РабПоГр_yes'/s";
if (preg_match($pattern, $content)) {
    echo "ERROR: Found duplicated condition for 'cat_ТабЭлОб_РабПоГр_yes' in actualCategory\n";
} else {
    echo "OK: No duplication found for 'cat_ТабЭлОб_РабПоГр_yes' in actualCategory\n";
}

$pattern = "/if \(.*?actualCategory === 'cat_ТабЭлОб_ФормИД_yes'.*?actualCategory === 'cat_ТабЭлОб_ФормИД_yes'/s";
if (preg_match($pattern, $content)) {
    echo "ERROR: Found duplicated condition for 'cat_ТабЭлОб_ФормИД_yes' in actualCategory\n";
} else {
    echo "OK: No duplication found for 'cat_ТабЭлОб_ФормИД_yes' in actualCategory\n";
}

echo "Test completed!\n";
?>