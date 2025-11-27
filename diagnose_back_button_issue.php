<?php
// Diagnostic test to check why the "Назад" button is not responding

echo "=== DIAGNOSTIC TEST FOR BACK BUTTON ISSUE ===\n\n";

// Step 1: Check if the back button is properly configured
echo "1. CHECKING BACK BUTTON CONFIGURATION...\n";

$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');
$backButtonPattern = "/createSupervisionInstallationWorksKeyboard.*?\['text' => '⬅️ Назад', 'callback_data' => 'Табель_ШМ_Back'\]/s";

if (preg_match($backButtonPattern, $messageServiceContent)) {
    echo "   ✅ Back button is correctly configured in supervision installation works menu\n";
    echo "   Callback data: 'Табель_ШМ_Back'\n\n";
} else {
    echo "   ❌ Back button is NOT correctly configured\n\n";
}

// Step 2: Check if TabelHandler can handle the callback
echo "2. CHECKING IF TABELHANDLER CAN HANDLE CALLBACK...\n";

$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check canHandle method
$canHandlePattern = "/canHandle.*?Табель_ШМ_Back/s";
if (preg_match($canHandlePattern, $tabelHandlerContent)) {
    echo "   ✅ TabelHandler can handle 'Табель_ШМ_Back' callback\n\n";
} else {
    echo "   ❌ TabelHandler CANNOT handle 'Табель_ШМ_Back' callback\n\n";
}

// Step 3: Check if routing is correct
echo "3. CHECKING ROUTING LOGIC...\n";

$routingPattern = "/if \\\$category === 'Табель_ШМ_Back'.*?handleBackToSupervision/s";
if (preg_match($routingPattern, $tabelHandlerContent)) {
    echo "   ✅ Callback correctly routes to handleBackToSupervision method\n\n";
} else {
    echo "   ❌ Callback does NOT route to handleBackToSupervision method\n\n";
}

// Step 4: Check if the target method exists
echo "4. CHECKING TARGET METHOD...\n";

if (strpos($tabelHandlerContent, "private function handleBackToSupervision(") !== false) {
    echo "   ✅ handleBackToSupervision method exists\n\n";
} else {
    echo "   ❌ handleBackToSupervision method does NOT exist\n\n";
}

// Step 5: Check if method creates correct keyboard
echo "5. CHECKING METHOD IMPLEMENTATION...\n";

if (strpos($tabelHandlerContent, "\$keyboard = \$this->messageService->createSupervisionKeyboard();") !== false) {
    echo "   ✅ handleBackToSupervision creates supervision keyboard\n\n";
} else {
    echo "   ❌ handleBackToSupervision does NOT create supervision keyboard\n\n";
}

// Step 6: Check if supervision keyboard exists
echo "6. CHECKING SUPERVISION KEYBOARD...\n";

if (strpos($messageServiceContent, "public function createSupervisionKeyboard()") !== false) {
    echo "   ✅ Supervision keyboard method exists\n\n";
} else {
    echo "   ❌ Supervision keyboard method does NOT exist\n\n";
}

// Possible issues and solutions
echo "=== POSSIBLE ISSUES AND SOLUTIONS ===\n";

$issues = [];

// Issue 1: Callback not handled
if (strpos($tabelHandlerContent, "\$callbackData === 'Табель_ШМ_Back'") === false) {
    $issues[] = "TabelHandler does not recognize 'Табель_ШМ_Back' callback";
}

// Issue 2: Method not implemented correctly
if (strpos($tabelHandlerContent, "private function handleBackToSupervision(") === false) {
    $issues[] = "handleBackToSupervision method is missing";
}

// Issue 3: Keyboard method missing
if (strpos($messageServiceContent, "public function createSupervisionKeyboard()") === false) {
    $issues[] = "createSupervisionKeyboard method is missing";
}

if (empty($issues)) {
    echo "✅ No obvious configuration issues found\n";
    echo "The back button should be working. Possible causes:\n";
    echo "   - Server caching issue (try restarting the bot)\n";
    echo "   - Network connectivity issues\n";
    echo "   - Telegram API temporary problems\n\n";
} else {
    echo "❌ Found issues that need to be fixed:\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
    echo "\n";
}

echo "=== RECOMMENDED ACTIONS ===\n";
echo "1. Restart the Telegram bot server to clear any caching\n";
echo "2. Check server logs for error messages when pressing the button\n";
echo "3. Verify network connectivity\n";
echo "4. Test with a different Telegram client\n";
echo "5. If issues persist, check Telegram Bot API status\n";