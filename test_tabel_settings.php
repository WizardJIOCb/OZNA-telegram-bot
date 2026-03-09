<?php
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelManagerHandler;

// Test 1: isOtherDateEnabled (should be false - no settings file yet)
$result = TabelManagerHandler::isOtherDateEnabled();
echo "Test 1 - isOtherDateEnabled (default): " . ($result ? 'enabled' : 'disabled') . PHP_EOL;

// Test 2: Create settings file and check
$settingsFile = __DIR__ . '/tabel_settings.json';
file_put_contents($settingsFile, json_encode(['other_date_enabled' => true], JSON_PRETTY_PRINT));
$result = TabelManagerHandler::isOtherDateEnabled();
echo "Test 2 - isOtherDateEnabled (after enable): " . ($result ? 'enabled' : 'disabled') . PHP_EOL;

// Test 3: Disable and check
file_put_contents($settingsFile, json_encode(['other_date_enabled' => false], JSON_PRETTY_PRINT));
$result = TabelManagerHandler::isOtherDateEnabled();
echo "Test 3 - isOtherDateEnabled (after disable): " . ($result ? 'enabled' : 'disabled') . PHP_EOL;

// Clean up
unlink($settingsFile);
echo "Test complete - settings file cleaned up." . PHP_EOL;
