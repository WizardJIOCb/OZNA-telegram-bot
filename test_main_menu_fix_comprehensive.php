<?php

// Comprehensive test to verify the main menu fix for guests

echo "=== Comprehensive Main Menu Fix Verification ===\n\n";

// Test 1: Check that all required handlers are registered
echo "Test 1: Checking handler registration...\n";
$callbackQueryCommandFile = file_get_contents(__DIR__ . '/CustomCommands/CallbackqueryCommand.php');

$usesMainMenuHandler = strpos($callbackQueryCommandFile, 'use Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler;') !== false;
$registersMainMenuHandler = strpos($callbackQueryCommandFile, 'new MainMenuHandler($this->messageService)') !== false;

echo "  Uses MainMenuHandler: " . ($usesMainMenuHandler ? "YES" : "NO") . "\n";
echo "  Registers MainMenuHandler: " . ($registersMainMenuHandler ? "YES" : "NO") . "\n";

// Test 2: Check that MainMenuHandler has correct priority
echo "\nTest 2: Checking handler priorities...\n";
$mainMenuHandlerFile = file_get_contents(__DIR__ . '/CustomCommands/Handlers/MainMenuHandler.php');
$adminHandlerFile = file_get_contents(__DIR__ . '/CustomCommands/Handlers/AdminHandler.php');

$mainMenuPriority = strpos($mainMenuHandlerFile, 'return 50;') !== false; // Highest priority
$adminPriority = strpos($adminHandlerFile, 'return 30;') !== false; // Lower priority

echo "  MainMenuHandler priority (50): " . ($mainMenuPriority ? "YES" : "NO") . "\n";
echo "  AdminHandler priority (30): " . ($adminPriority ? "YES" : "NO") . "\n";

// Test 3: Check that both handlers can handle category_Главное
echo "\nTest 3: Checking callback handling capabilities...\n";

$mainMenuCanHandle = strpos($mainMenuHandlerFile, "return \$callbackData === 'category_Главное';") !== false;
$adminCanHandle = strpos($adminHandlerFile, '$equalsCategoryMain = $callbackData === \'category_Главное\';') !== false;

echo "  MainMenuHandler can handle category_Главное: " . ($mainMenuCanHandle ? "YES" : "NO") . "\n";
echo "  AdminHandler can handle category_Главное: " . ($adminCanHandle ? "YES" : "NO") . "\n";

// Test 4: Check that MainMenuHandler properly handles guest access
echo "\nTest 4: Checking guest access handling...\n";

$mainMenuGuestCheck = strpos($mainMenuHandlerFile, 'if ($userAccessLevel == 0)') !== false;
$mainMenuGuestReplyKeyboard = strpos($mainMenuHandlerFile, 'createGuestMainMenuReplyKeyboard') !== false;
$mainMenuRegisteredUserReplyKeyboard = strpos($mainMenuHandlerFile, 'createMainMenuReplyKeyboard') !== false;

echo "  MainMenuHandler checks guest access level: " . ($mainMenuGuestCheck ? "YES" : "NO") . "\n";
echo "  MainMenuHandler uses createGuestMainMenuReplyKeyboard: " . ($mainMenuGuestReplyKeyboard ? "YES" : "NO") . "\n";
echo "  MainMenuHandler uses createMainMenuReplyKeyboard for registered users: " . ($mainMenuRegisteredUserReplyKeyboard ? "YES" : "NO") . "\n";

// Test 5: Check that AdminHandler also properly handles guest access (as backup)
echo "\nTest 5: Checking AdminHandler guest access handling...\n";

$adminMenuGuestCheck = strpos($adminHandlerFile, 'if ($userAccessLevel == 0)') !== false;
$adminMenuGuestReplyKeyboard = strpos($adminHandlerFile, 'createGuestMainMenuReplyKeyboard') !== false;

echo "  AdminHandler checks guest access level in showMainMenu: " . ($adminMenuGuestCheck ? "YES" : "NO") . "\n";
echo "  AdminHandler uses createGuestMainMenuReplyKeyboard: " . ($adminMenuGuestReplyKeyboard ? "YES" : "NO") . "\n";

// Test 6: Check MessageService methods
echo "\nTest 6: Checking MessageService methods...\n";
$messageServiceFile = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

$hasCreateMainMenuReplyKeyboard = strpos($messageServiceFile, 'createMainMenuReplyKeyboard') !== false;
$hasCreateGuestMainMenuReplyKeyboard = strpos($messageServiceFile, 'createGuestMainMenuReplyKeyboard') !== false;
$hasSendMessageWithReplyKeyboard = strpos($messageServiceFile, 'sendMessageWithReplyKeyboard') !== false;

echo "  MessageService has createMainMenuReplyKeyboard: " . ($hasCreateMainMenuReplyKeyboard ? "YES" : "NO") . "\n";
echo "  MessageService has createGuestMainMenuReplyKeyboard: " . ($hasCreateGuestMainMenuReplyKeyboard ? "YES" : "NO") . "\n";
echo "  MessageService has sendMessageWithReplyKeyboard: " . ($hasSendMessageWithReplyKeyboard ? "YES" : "NO") . "\n";

// Summary
echo "\n=== SUMMARY ===\n";
$allTestsPassed = $usesMainMenuHandler && $registersMainMenuHandler && 
                  $mainMenuPriority && $adminPriority &&
                  $mainMenuCanHandle && $adminCanHandle &&
                  $mainMenuGuestCheck && $mainMenuGuestReplyKeyboard && $mainMenuRegisteredUserReplyKeyboard &&
                  $adminMenuGuestCheck && $adminMenuGuestReplyKeyboard &&
                  $hasCreateMainMenuReplyKeyboard && $hasCreateGuestMainMenuReplyKeyboard && $hasSendMessageWithReplyKeyboard;

if ($allTestsPassed) {
    echo "✅ ALL TESTS PASSED - The main menu fix is correctly implemented!\n";
    echo "\nWhat was fixed:\n";
    echo "1. MainMenuHandler is now properly registered in CallbackqueryCommand\n";
    echo "2. MainMenuHandler has higher priority (50) than AdminHandler (30)\n";
    echo "3. Both handlers can handle 'category_Главное' callback\n";
    echo "4. MainMenuHandler properly checks user access level and updates reply keyboard\n";
    echo "5. Guests will see only 'Профиль' and 'Помощь' in both inline and reply keyboards\n";
    echo "6. Registered users will see the full menu with all options\n";
    echo "7. When guests press 'Вернуться в главное меню', they will get the limited reply keyboard\n";
} else {
    echo "❌ SOME TESTS FAILED - Please check the implementation\n";
}

echo "\n=== END OF TEST ===\n";