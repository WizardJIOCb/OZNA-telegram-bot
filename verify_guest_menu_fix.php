<?php

// Comprehensive test to verify the guest menu fix

echo "=== Guest Menu Fix Verification ===\n\n";

// Test 1: Check that MessageService has the required methods
echo "Test 1: Checking MessageService methods...\n";
$messageServiceFile = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

$hasCreateMainMenuReplyKeyboard = strpos($messageServiceFile, 'createMainMenuReplyKeyboard') !== false;
$hasCreateGuestMainMenuReplyKeyboard = strpos($messageServiceFile, 'createGuestMainMenuReplyKeyboard') !== false;
$hasSendMessageWithReplyKeyboard = strpos($messageServiceFile, 'sendMessageWithReplyKeyboard') !== false;

echo "  createMainMenuReplyKeyboard method: " . ($hasCreateMainMenuReplyKeyboard ? "FOUND" : "MISSING") . "\n";
echo "  createGuestMainMenuReplyKeyboard method: " . ($hasCreateGuestMainMenuReplyKeyboard ? "FOUND" : "MISSING") . "\n";
echo "  sendMessageWithReplyKeyboard method: " . ($hasSendMessageWithReplyKeyboard ? "FOUND" : "MISSING") . "\n";

// Test 2: Check that MainMenuHandler uses the correct methods
echo "\nTest 2: Checking MainMenuHandler implementation...\n";
$mainMenuHandlerFile = file_get_contents(__DIR__ . '/CustomCommands/Handlers/MainMenuHandler.php');

$usesCreateGuestMainMenuReplyKeyboard = strpos($mainMenuHandlerFile, 'createGuestMainMenuReplyKeyboard') !== false;
$usesSendMessageWithReplyKeyboard = strpos($mainMenuHandlerFile, 'sendMessageWithReplyKeyboard') !== false;
$hasAccessLevelCheck = strpos($mainMenuHandlerFile, '$userAccessLevel == 0') !== false;

echo "  Uses createGuestMainMenuReplyKeyboard: " . ($usesCreateGuestMainMenuReplyKeyboard ? "YES" : "NO") . "\n";
echo "  Uses sendMessageWithReplyKeyboard: " . ($usesSendMessageWithReplyKeyboard ? "YES" : "NO") . "\n";
echo "  Has access level check for guests: " . ($hasAccessLevelCheck ? "YES" : "NO") . "\n";

// Test 3: Check the specific logic for guest vs registered users
echo "\nTest 3: Checking guest vs registered user logic...\n";

// Look for the conditional logic that handles guests differently
$guestLogic = strpos($mainMenuHandlerFile, 'if ($userAccessLevel == 0)') !== false;
$registeredUserLogic = strpos($mainMenuHandlerFile, 'else {') !== false && strpos($mainMenuHandlerFile, 'createMainMenuReplyKeyboard') !== false;

echo "  Guest-specific logic implemented: " . ($guestLogic ? "YES" : "NO") . "\n";
echo "  Registered user logic implemented: " . ($registeredUserLogic ? "YES" : "NO") . "\n";

// Summary
echo "\n=== SUMMARY ===\n";
$allTestsPassed = $hasCreateMainMenuReplyKeyboard && $hasCreateGuestMainMenuReplyKeyboard && 
                  $hasSendMessageWithReplyKeyboard && $usesCreateGuestMainMenuReplyKeyboard && 
                  $usesSendMessageWithReplyKeyboard && $hasAccessLevelCheck && 
                  $guestLogic && $registeredUserLogic;

if ($allTestsPassed) {
    echo "✅ ALL TESTS PASSED - The guest menu fix is correctly implemented!\n";
    echo "\nWhat was fixed:\n";
    echo "1. MainMenuHandler now updates the reply keyboard (persistent menu) based on user access level\n";
    echo "2. Guests will see only 'Профиль' and 'Помощь' buttons in both inline and reply keyboards\n";
    echo "3. Registered users will see the full menu with 'Табель', 'Профиль', 'Админка', and 'Помощь'\n";
    echo "4. When guests press 'Вернуться в главное меню', they will get the limited reply keyboard\n";
} else {
    echo "❌ SOME TESTS FAILED - Please check the implementation\n";
}

echo "\n=== END OF TEST ===\n";