<?php
// Live debug test to see what's happening with RK callback

echo "=== LIVE RK DEBUG TEST ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/autoload.php';

// Import all necessary classes
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;

// Import all handlers
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler;

try {
    // Create all services (minimal setup to avoid database issues)
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create all handlers
    $handlers = [
        new AdminHandler($adminService, $messageService),
        new AccessLevelHandler($adminService, $messageService),
        new SupervisorHandler($messageService, $adminService),
        new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService),
        new ProfileHandler($messageService),
        new FunctionalDirectionHandler($messageService),
        new HelpHandler($messageService),
        new MainMenuHandler($messageService)
    ];
    
    echo "=== HANDLER PRIORITIES ===\n";
    foreach ($handlers as $handler) {
        echo get_class($handler) . " priority: " . $handler->getPriority() . "\n";
    }
    echo "\n";
    
    // Create CallbackHandlerFactory and register handlers
    $factory = new CallbackHandlerFactory();
    foreach ($handlers as $handler) {
        $factory->registerHandler($handler);
    }
    
    echo "=== TESTING RK CALLBACKS ===\n";
    
    $testCallbacks = [
        'cat_ТабУстрЗамРК',
        'category_Табель_УЗ_РК'
    ];
    
    foreach ($testCallbacks as $callback) {
        echo "\nTesting callback: '{$callback}'\n";
        echo "Callback length: " . strlen($callback) . "\n";
        echo "Callback hex: " . bin2hex($callback) . "\n";
        
        // Test each handler manually
        foreach ($handlers as $handler) {
            $handlerName = basename(str_replace('\\', '/', get_class($handler)));
            try {
                $canHandle = $handler->canHandle($callback);
                echo "  {$handlerName}: " . ($canHandle ? 'YES' : 'NO') . "\n";
                
                if ($canHandle && $handlerName === 'TabelHandler') {
                    echo "    ✅ TabelHandler claims this callback!\n";
                }
            } catch (Exception $e) {
                echo "  {$handlerName}: ERROR - " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Check if there's a mapping issue
    echo "\n=== CHECKING CALLBACK MAPPINGS ===\n";
    
    // Test TabelHandler specifically
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    // Use reflection to access private method
    $reflection = new ReflectionClass($tabelHandler);
    $extractMethod = $reflection->getMethod('extractCategoryFromCallback');
    $extractMethod->setAccessible(true);
    
    foreach ($testCallbacks as $callback) {
        try {
            $extractedCategory = $extractMethod->invoke($tabelHandler, $callback);
            echo "Callback '{$callback}' -> Category: '{$extractedCategory}'\n";
        } catch (Exception $e) {
            echo "Callback '{$callback}' -> ERROR: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== ANALYSIS ===\n";
echo "If TabelHandler says YES to both callbacks, but you still get 'unknown command',\n";
echo "then another handler with higher priority is intercepting the callback.\n";
echo "Check the handler registration order and priorities above.\n";

?>