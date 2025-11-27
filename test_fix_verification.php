<?php
// Test script to verify the fix for the specific issue reported by the user

require_once 'vendor/autoload.php';
require_once 'custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

echo "TESTING FIX FOR ELECTRICAL EQUIPMENT ISSUE\n";
echo "========================================\n\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the specific issue: when user clicks "yes" button for electrical equipment activities
echo "Testing the specific issue reported by the user:\n";
echo "When user clicks 'yes' button for electrical equipment activities, it should NOT show 'Неизвестная команда табеля'\n\n";

$testCallbacks = [
    'cat_ТабЭлОб_РабПоГр_yes' => 'Работы по графику - should create timesheet entry',
    'cat_ТабЭлОб_ФормИД_yes' => 'Формирование ИД - should create timesheet entry',
    'cat_ТабЭлОб_ИзмСхПд_yes' => 'Изменение схемы подключения - should create timesheet entry',
    'cat_ТабЭлОб_МонтКбЛн_yes' => 'Прокладка кабеля - should create timesheet entry',
    'cat_ТабЭлОб_РаскКбЛн_yes' => 'Подключение кабеля - should create timesheet entry',
    'cat_ТабЭлОб_ДопРаб_yes' => 'Дополнительные работы - should create timesheet entry',
    'cat_ТабЭлОб_ПодтВех_yes' => 'Подтверждение достижения вехи - should create timesheet entry',
    'cat_ТабЭлОб_Проч_yes' => 'Прочее - should create timesheet entry'
];

echo "Testing isActivityExecution method:\n";
foreach ($testCallbacks as $callback => $description) {
    echo "Callback: {$callback}\n";
    echo "Description: {$description}\n";
    
    // Use reflection to access private method
    try {
        $reflection = new ReflectionClass($tabelHandler);
        $method = $reflection->getMethod('isActivityExecution');
        $method->setAccessible(true);
        $result = $method->invoke($tabelHandler, $callback);
        
        echo "isActivityExecution result: " . ($result ? 'YES (creates entry)' : 'NO (shows confirmation or error)') . "\n";
        
        if ($result) {
            echo "✅ CORRECT - This callback will create a timesheet entry\n";
        } else {
            echo "❌ INCORRECT - This callback would show 'Неизвестная команда табеля'\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Test completed.\n";
echo "If all tests show ✅ CORRECT, then the issue has been fixed!\n";
?>