<?php

// Test script to verify profile button functionality
require __DIR__ . '/vendor/autoload.php';

// Test the callback data processing
echo "Testing profile button callback processing...\n";

// Simulate the callback data that should be sent by the profile button
$callbackData = 'category_Профиль_Просмотр';

echo "Callback data: {$callbackData}\n";
echo "Callback data length: " . strlen($callbackData) . "\n";
echo "Callback data mb_strlen: " . mb_strlen($callbackData, 'UTF-8') . "\n";

// Test if ProfileHandler can handle this callback data
require_once __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

$messageService = new MessageService();
$profileHandler = new ProfileHandler($messageService);

$canHandle = $profileHandler->canHandle($callbackData);
echo "ProfileHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

// Test with different encoding
$callbackDataWindows1251 = iconv('UTF-8', 'Windows-1251', $callbackData);
echo "Callback data in Windows-1251: " . $callbackDataWindows1251 . "\n";

$callbackDataConverted = iconv('Windows-1251', 'UTF-8', $callbackDataWindows1251);
echo "Callback data converted back to UTF-8: " . $callbackDataConverted . "\n";

$canHandleConverted = $profileHandler->canHandle($callbackDataConverted);
echo "ProfileHandler canHandle result for converted data: " . ($canHandleConverted ? 'true' : 'false') . "\n";

echo "Test completed.\n";