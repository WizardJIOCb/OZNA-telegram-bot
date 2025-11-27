<?php
// Детальный тест ProfileHandler

// Включаем вывод ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_profile_handler.log');

echo "ProfileHandler detailed test\n";
echo "===========================\n";

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Создаем mock объекты
class MockCallbackQuery {
    public function getData() {
        return 'category_Профиль_Просмотр';
    }
    
    public function getMessage() {
        return new class {
            public function getChat() {
                return new class {
                    public function getId() {
                        return 123456789;
                    }
                };
            }
        };
    }
    
    public function getFrom() {
        return new class {
            public function getId() {
                return 123456789;
            }
        };
    }
}

class MockConversation {
    // Пустой mock класс
}

echo "Creating mock objects...\n";

// Создаем сервисы
$messageService = new MessageService();
$profileHandler = new ProfileHandler($messageService);

echo "Testing canHandle method...\n";

// Тестируем canHandle
$callbackData = 'category_Профиль_Просмотр';
$canHandle = $profileHandler->canHandle($callbackData);
echo "canHandle result for '$callbackData': " . ($canHandle ? 'true' : 'false') . "\n";

// Тестируем другие варианты
$testCases = [
    'category_Профиль',
    'category_Профиль_Просмотр',
    'category_Профиль_ФИО',
    'category_Профиль_ФН',
    'category_Профиль_ФН_МобильнаяБригада'
];

foreach ($testCases as $testCase) {
    $result = $profileHandler->canHandle($testCase);
    echo "canHandle result for '$testCase': " . ($result ? 'true' : 'false') . "\n";
}

echo "\nTesting encoding...\n";

// Проверяем кодировку
$testString = 'Профиль_Просмотр';
echo "Test string: $testString\n";
echo "String length: " . strlen($testString) . "\n";
echo "MB string length: " . mb_strlen($testString, 'UTF-8') . "\n";
echo "Hex representation: " . bin2hex($testString) . "\n";

// Проверяем кодировку с различными преобразованиями
$utf8String = mb_convert_encoding($testString, 'UTF-8', 'auto');
echo "After mb_convert_encoding: $utf8String\n";
echo "UTF-8 check: " . (mb_check_encoding($utf8String, 'UTF-8') ? 'valid' : 'invalid') . "\n";

echo "\nTest completed.\n";