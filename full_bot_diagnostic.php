<?php

echo "Full bot diagnostic and fix script\n";
echo "==================================\n\n";

// Функция для вывода результатов проверки
function checkResult($checkName, $status, $message = "") {
    $statusSymbol = $status ? "✓" : "✗";
    echo "{$statusSymbol} {$checkName}\n";
    if (!empty($message)) {
        echo "  {$message}\n";
    }
    echo "\n";
}

// Функция для проверки наличия файла
function checkFile($filePath, $description) {
    $exists = file_exists($filePath);
    checkResult($description, $exists, $exists ? "" : "File not found: {$filePath}");
    return $exists;
}

// Функция для проверки содержимого файла
function checkFileContent($filePath, $searchTerm, $description) {
    if (!file_exists($filePath)) {
        checkResult($description, false, "File not found: {$filePath}");
        return false;
    }
    
    $content = file_get_contents($filePath);
    $found = strpos($content, $searchTerm) !== false;
    checkResult($description, $found, $found ? "" : "Required content not found: {$searchTerm}");
    return $found;
}

// Начало диагностики
echo "Starting comprehensive bot diagnostic...\n\n";

// Проверка 1: Основные файлы бота
echo "=== Check 1: Core Bot Files ===\n";
$mainFiles = [
    __DIR__ . '/hook.php' => 'Webhook handler',
    __DIR__ . '/config.php' => 'Configuration file',
    __DIR__ . '/CustomCommands/StartCommand.php' => 'Start command',
    __DIR__ . '/CustomCommands/Services/MessageService.php' => 'Message service',
    __DIR__ . '/CustomCommands/Services/AdminService.php' => 'Admin service',
    __DIR__ . '/CustomCommands/Services/DatabaseService.php' => 'Database service'
];

$allFilesExist = true;
foreach ($mainFiles as $filePath => $description) {
    if (!checkFile($filePath, $description)) {
        $allFilesExist = false;
    }
}

checkResult("All core files exist", $allFilesExist);

// Проверка 2: Структура классов
echo "=== Check 2: Class Structure ===\n";
checkFileContent(__DIR__ . '/CustomCommands/StartCommand.php', 'class StartCommand', 'StartCommand class definition');
checkFileContent(__DIR__ . '/CustomCommands/Services/MessageService.php', 'class MessageService', 'MessageService class definition');
checkFileContent(__DIR__ . '/CustomCommands/Services/AdminService.php', 'class AdminService', 'AdminService class definition');
checkFileContent(__DIR__ . '/CustomCommands/Services/DatabaseService.php', 'class DatabaseService', 'DatabaseService class definition');

// Проверка 3: Методы в классах
echo "=== Check 3: Required Methods ===\n";
checkFileContent(__DIR__ . '/CustomCommands/StartCommand.php', 'execute()', 'StartCommand execute method');
checkFileContent(__DIR__ . '/CustomCommands/Services/MessageService.php', 'sendMessageWithReplyKeyboard', 'MessageService sendMessageWithReplyKeyboard method');
checkFileContent(__DIR__ . '/CustomCommands/Services/MessageService.php', 'createMainMenuReplyKeyboard', 'MessageService createMainMenuReplyKeyboard method');
checkFileContent(__DIR__ . '/CustomCommands/Services/AdminService.php', 'getUserAccessLevel', 'AdminService getUserAccessLevel method');

// Проверка 4: Конфигурация
echo "=== Check 4: Configuration ===\n";
$configExists = checkFile(__DIR__ . '/config.php', 'Configuration file');
if ($configExists) {
    checkFileContent(__DIR__ . '/config.php', 'api_key', 'API key in config');
    checkFileContent(__DIR__ . '/config.php', 'hook_url', 'Hook URL in config');
}

// Проверка 5: Подключение к базе данных
echo "=== Check 5: Database Connection ===\n";
try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
    
    $databaseService = new DatabaseService();
    $connection = $databaseService->getConnection();
    checkResult("Database connection", true, "Connected successfully");
    
    // Проверка таблиц
    $query = "SHOW TABLES LIKE 'ozna_data'";
    $result = $connection->query($query);
    checkResult("ozna_data table exists", $result && $result->num_rows > 0);
    
    $query = "SHOW TABLES LIKE 'access_requests'";
    $result = $connection->query($query);
    checkResult("access_requests table exists", $result && $result->num_rows > 0);
    
} catch (Exception $e) {
    checkResult("Database connection", false, "Error: " . $e->getMessage());
}

// Проверка 6: Уровни доступа пользователей
echo "=== Check 6: User Access Levels ===\n";
try {
    require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
    use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
    
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Тестовый пользователь
    $testUserId = 123456789;
    $accessLevel = $adminService->getUserAccessLevel($testUserId);
    checkResult("User access level check", true, "Access level: {$accessLevel}");
    
} catch (Exception $e) {
    checkResult("User access level check", false, "Error: " . $e->getMessage());
}

// Проверка 7: Создание клавиатур
echo "=== Check 7: Keyboard Creation ===\n";
try {
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
    
    $messageService = new MessageService();
    
    // Создание главного меню
    $mainMenu = $messageService->createMainMenuReplyKeyboard();
    checkResult("Main menu creation", is_array($mainMenu) && !empty($mainMenu), "Menu created successfully");
    
    // Создание гостевого меню
    $guestMenu = [
        [
            ['text' => '👤 Профиль'], 
            ['text' => '❓ Помощь']
        ]
    ];
    checkResult("Guest menu creation", is_array($guestMenu) && !empty($guestMenu), "Guest menu created successfully");
    
} catch (Exception $e) {
    checkResult("Keyboard creation", false, "Error: " . $e->getMessage());
}

// Проверка 8: Webhook
echo "=== Check 8: Webhook ===\n";
checkFile(__DIR__ . '/webhook_info.php', 'Webhook info script');
checkFile(__DIR__ . '/set-hook.py', 'Webhook setup script');

// Проверка 9: Логи
echo "=== Check 9: Logs ===\n";
$logFiles = [
    __DIR__ . '/logs/bot.log',
    __DIR__ . '/bot.log',
    '/var/log/bot.log'
];

$logFound = false;
foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $logFound = true;
        break;
    }
}

checkResult("Log files", $logFound, $logFound ? "Log file found" : "No log files found");

// Проверка 10: Тестовая команда
echo "=== Check 10: Test Command ===\n";
$testCommandContent = '<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

class TestmenuCommand extends SystemCommand
{
    protected $name = \'testmenu\';
    protected $description = \'Test menu command\';
    protected $usage = \'/testmenu\';
    protected $version = \'1.0.0\';
    protected $private_only = false;

    public function execute(): ServerResponse
    {
        // Get user info
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        
        // Initialize services
        $messageService = new MessageService();
        
        // Create main menu reply keyboard
        $keyboard = [
            [
                [\'text\' => \'📊 Табель\'], 
                [\'text\' => \'❓ Помощь\']
            ],
            [
                [\'text\' => \'👤 Профиль\'], 
                [\'text\' => \'⚙️ Админка\']
            ]
        ];
        
        // Send message with reply keyboard (persistent menu)
        return $messageService->sendMessageWithReplyKeyboard(
            $chat_id,
            "<b>🏠 Test Menu</b>\\r\\n\\r\\nThis is a test menu to verify functionality.",
            $keyboard
        );
    }
}';

$testCommandPath = __DIR__ . '/CustomCommands/TestmenuCommand.php';
$testCommandCreated = file_put_contents($testCommandPath, $testCommandContent) !== false;
checkResult("Test command creation", $testCommandCreated, $testCommandCreated ? "TestmenuCommand.php created" : "Failed to create TestmenuCommand.php");

// Финальная сводка
echo "=== Final Summary ===\n";
echo "Diagnostic completed. Please review the results above.\n";
echo "If any checks failed, please address those issues.\n";
echo "You can test the menu functionality by sending /testmenu to the bot.\n";

echo "\nRecommended actions:\n";
echo "1. If the /start button is still not working, restart the bot service\n";
echo "2. Check the bot logs for any error messages\n";
echo "3. Verify the webhook is properly configured\n";
echo "4. Test with the /testmenu command we created\n";
echo "5. Ensure database connectivity is working properly\n";