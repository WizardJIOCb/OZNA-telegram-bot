<?php

echo "Diagnosing and fixing /start button issue...\n\n";

// Проверка 1: Структура команды StartCommand
echo "=== Check 1: StartCommand Structure ===\n";
$startCommandPath = __DIR__ . '/CustomCommands/StartCommand.php';

if (file_exists($startCommandPath)) {
    echo "✓ StartCommand.php file exists\n";
    
    $content = file_get_contents($startCommandPath);
    
    // Проверяем наличие необходимых элементов
    if (strpos($content, 'class StartCommand') !== false) {
        echo "✓ StartCommand class found\n";
    } else {
        echo "✗ StartCommand class not found\n";
    }
    
    if (strpos($content, 'execute(): ServerResponse') !== false) {
        echo "✓ execute method found\n";
    } else {
        echo "✗ execute method not found\n";
    }
    
    if (strpos($content, 'sendMessageWithReplyKeyboard') !== false) {
        echo "✓ sendMessageWithReplyKeyboard method call found\n";
    } else {
        echo "✗ sendMessageWithReplyKeyboard method call not found\n";
    }
    
} else {
    echo "✗ StartCommand.php file does not exist\n";
}

echo "\n";

// Проверка 2: Структура MessageService
echo "=== Check 2: MessageService Structure ===\n";
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';

if (file_exists($messageServicePath)) {
    echo "✓ MessageService.php file exists\n";
    
    $content = file_get_contents($messageServicePath);
    
    if (strpos($content, 'class MessageService') !== false) {
        echo "✓ MessageService class found\n";
    } else {
        echo "✗ MessageService class not found\n";
    }
    
    if (strpos($content, 'sendMessageWithReplyKeyboard') !== false) {
        echo "✓ sendMessageWithReplyKeyboard method found\n";
    } else {
        echo "✗ sendMessageWithReplyKeyboard method not found\n";
    }
    
    if (strpos($content, 'createMainMenuReplyKeyboard') !== false) {
        echo "✓ createMainMenuReplyKeyboard method found\n";
    } else {
        echo "✗ createMainMenuReplyKeyboard method not found\n";
    }
    
} else {
    echo "✗ MessageService.php file does not exist\n";
}

echo "\n";

// Проверка 3: Проверка регистрации команды
echo "=== Check 3: Command Registration ===\n";
$commandsPath = __DIR__ . '/CustomCommands';

if (is_dir($commandsPath)) {
    $files = scandir($commandsPath);
    $startCommandFound = false;
    
    foreach ($files as $file) {
        if (strpos($file, 'StartCommand.php') !== false) {
            echo "✓ StartCommand.php found in CustomCommands directory\n";
            $startCommandFound = true;
            break;
        }
    }
    
    if (!$startCommandFound) {
        echo "✗ StartCommand.php not found in CustomCommands directory\n";
    }
    
} else {
    echo "✗ CustomCommands directory does not exist\n";
}

echo "\n";

// Проверка 4: Проверка конфигурации бота
echo "=== Check 4: Bot Configuration ===\n";
$configFiles = [
    __DIR__ . '/config.php',
    __DIR__ . '/hook.php',
    __DIR__ . '/composer.json'
];

foreach ($configFiles as $configFile) {
    if (file_exists($configFile)) {
        echo "✓ " . basename($configFile) . " found\n";
    } else {
        echo "? " . basename($configFile) . " not found (may not be required)\n";
    }
}

echo "\n";

// Проверка 5: Создание тестовой команды
echo "=== Check 5: Creating Test Command ===\n";
$testCommandContent = '<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

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
        $user_id = $message->getFrom()->getId();
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

if (file_put_contents($testCommandPath, $testCommandContent)) {
    echo "✓ TestmenuCommand.php created successfully\n";
    echo "  You can test the menu functionality by sending /testmenu to the bot\n";
} else {
    echo "✗ Failed to create TestmenuCommand.php\n";
}

echo "\n";

// Проверка 6: Рекомендации по исправлению
echo "=== Check 6: Recommendations ===\n";
echo "If the /start button is still not working, try these steps:\n\n";

echo "1. Restart the bot:\n";
echo "   - Stop the bot service\n";
echo "   - Start the bot service again\n\n";

echo "2. Clear any cached data:\n";
echo "   - Check if there are any cache files in the /cache directory\n";
echo "   - Remove them if they exist\n\n";

echo "3. Test with the /testmenu command we just created:\n";
echo "   - Send /testmenu to the bot\n";
echo "   - See if the menu appears correctly\n\n";

echo "4. Check the bot logs:\n";
echo "   - Run the check_bot_logs.php script\n";
echo "   - Look for any error messages related to StartCommand\n\n";

echo "5. Verify database connectivity:\n";
echo "   - Run the test_database_connection.php script\n";
echo "   - Ensure the database connection is working properly\n\n";

echo "Diagnosis completed.\n";