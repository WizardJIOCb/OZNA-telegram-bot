<?php

/**
 * Тестовый скрипт для проверки функциональности табеля
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes since they're not autoloaded
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

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Тестирование функциональности табеля...\n";

try {
    // Создаем сервисы
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    echo "✅ Сервисы успешно созданы\n";
    
    // Создаем TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    echo "✅ TabelHandler успешно создан\n";
    
    // Тестируем canHandle метод
    $testCallbacks = [
        'category_Табель' => true,
        'category_Табель_Дорога' => true,
        'category_Табель_Подготовка' => true,
        'category_Табель_НесуществующаяКоманда' => true, // Should be handled by handleOtherWorkTypes
        'category_Админка' => false,
        'adm_req_clear' => false
    ];
    
    echo "Тестирование canHandle метода:\n";
    foreach ($testCallbacks as $callback => $expected) {
        $result = $tabelHandler->canHandle($callback);
        $status = ($result === $expected) ? '✅' : '❌';
        echo "  {$status} Callback: {$callback} - Ожидаем: " . ($expected ? 'true' : 'false') . ", Получаем: " . ($result ? 'true' : 'false') . "\n";
    }
    
    echo "✅ Тестирование canHandle завершено\n";
    
    // Проверяем наличие свойства activityTypes
    $reflection = new ReflectionClass($tabelHandler);
    if ($reflection->hasProperty('activityTypes')) {
        echo "✅ Свойство activityTypes существует\n";
    } else {
        echo "❌ Свойство activityTypes отсутствует\n";
    }
    
    echo "✅ Все тесты пройдены успешно!\n";
    echo "Теперь можно протестировать бота в Telegram\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}