<?php
/**
 * Verification script to simulate guest user running /start command
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== VERIFICATION: Guest User Running /start Command ===\n\n";

// Simulate guest user
$guestUserId = 999999; // Test guest user ID
$guestAccessLevel = 0; // Guest level

// Initialize services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

echo "Step 1: Simulating guest user with ID: {$guestUserId}\n";
echo "        Access level: {$guestAccessLevel} (Guest)\n\n";

echo "Step 2: Creating keyboard based on access level...\n";

// This is what StartCommand does
if ($guestAccessLevel == 0) {
    echo "        User is a guest, using createGuestMainMenuReplyKeyboard()\n";
    $keyboard = $messageService->createGuestMainMenuReplyKeyboard();
} else {
    echo "        User is NOT a guest, using createMainMenuReplyKeyboard()\n";
    $keyboard = $messageService->createMainMenuReplyKeyboard();
}

echo "\nStep 3: Keyboard created successfully!\n";
echo "        Buttons that will appear:\n";

foreach ($keyboard as $row) {
    foreach ($row as $button) {
        echo "        ✓ {$button['text']}\n";
    }
}

// Check for the access request button
$hasAccessButton = false;
foreach ($keyboard as $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Запросить доступ') !== false) {
            $hasAccessButton = true;
            break 2;
        }
    }
}

echo "\n";
if ($hasAccessButton) {
    echo "✅ SUCCESS: '🔐 Запросить доступ' button WILL APPEAR for guest users!\n";
    echo "\nThe button will be visible in the persistent menu at the bottom of the chat.\n";
} else {
    echo "❌ ERROR: '🔐 Запросить доступ' button is MISSING!\n";
}

echo "\n=== WHAT TO DO NEXT ===\n";
echo "1. Test in the actual bot by logging in as a guest\n";
echo "2. Send /start command to the bot\n";
echo "3. Check the persistent menu at the bottom - you should see:\n";
echo "   - 👤 Профиль\n";
echo "   - ❓ Помощь\n";
echo "   - 🔐 Запросить доступ\n";
echo "\n✅ Verification complete!\n";
