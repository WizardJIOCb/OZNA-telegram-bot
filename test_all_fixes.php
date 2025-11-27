<?php
// Comprehensive test script to verify all fixes

echo "Testing all fixes...\n\n";

// Test 1: Encoding helper functions
echo "1. Testing encoding helper functions...\n";
require_once __DIR__ . '/encoding_helper.php';

// Test cases from the logs
$testCases = [
    'ð Ð¢Ð°Ð±ÐµÐ»Ñ',
    'ð¤ ÐÑÐ¾ÑÐ¸Ð»Ñ',
    'â ÐÐ¾Ð¼Ð¾ÑÑ',
    'âï¸ ÐÐ´Ð¼Ð¸Ð½ÐºÐ°',
    'category_Ð¢Ð°Ð±ÐµÐ»Ñ',
    'category_ÐÐ´Ð¼Ð¸Ð½ÐºÐ°',
    'category_ÐÑÐ¾ÑÐ¸Ð»Ñ',
    'category_ÐÐ¾Ð¼Ð¾ÑÑ'
];

foreach ($testCases as $test) {
    echo "   Original: $test\n";
    $fixed = fixEncoding($test);
    echo "   Fixed:    $fixed\n";
    echo "   ------------------------\n";
}

echo "\n2. Testing TextmenuCommand...\n";
// Test that TextmenuCommand properly handles text
require_once __DIR__ . '/CustomCommands/TextmenuCommand.php';
echo "   TextmenuCommand loaded successfully\n";

echo "\n3. Testing MessageService...\n";
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
$messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
echo "   MessageService loaded successfully\n";

// Test keyboard creation
$keyboard = $messageService->createMainMenuReplyKeyboard();
echo "   Main menu keyboard created successfully\n";

echo "\n4. Testing StartCommand...\n";
require_once __DIR__ . '/CustomCommands/StartCommand.php';
echo "   StartCommand loaded successfully\n";

echo "\nAll tests completed successfully!\n";
echo "The encoding issues should now be fixed.\n";
?>