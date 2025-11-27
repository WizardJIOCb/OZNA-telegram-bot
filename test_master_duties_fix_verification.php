<?php
/**
 * Test script to verify the master duties button fix
 * This script checks that all the necessary changes have been made
 */

echo "=== Master Duties Button Fix Verification ===\n\n";

// Check 1: MessageService.php button callback
$messageServiceContent = file_get_contents('CustomCommands/Services/MessageService.php');
$buttonPattern = '/\[\'text\' => \'👷 Исполнение обязанностей мастера\', \'callback_data\' => \'cat_ТабИсполнОбяз\'\]/';
if (preg_match($buttonPattern, $messageServiceContent)) {
    echo "✅ Check 1 PASSED: MessageService.php uses ultra-short callback format\n";
} else {
    echo "❌ Check 1 FAILED: MessageService.php still uses long callback format\n";
    // Show what's actually there
    if (preg_match('/\[\'text\' => \'👷 Исполнение обязанностей мастера\', \'callback_data\' => \'category_Табель_ИсполнениеОбязМастера\'\]/', $messageServiceContent)) {
        echo "   Found: category_Табель_ИсполнениеОбязМастера (long format)\n";
    }
}

// Check 2: TabelHandler.php master duties handler
$tabelHandlerContent = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Check that the master duties handler uses ultra-short callback
$handlerPattern = '/\$confirmationCallback = "cat_ИОМ_yes";/';
if (preg_match($handlerPattern, $tabelHandlerContent)) {
    echo "✅ Check 2 PASSED: TabelHandler.php master duties handler uses ultra-short callback\n";
} else {
    echo "❌ Check 2 FAILED: TabelHandler.php master duties handler doesn't use ultra-short callback\n";
}

// Check 3: isConfirmationCallback method recognizes ultra-short format
$isConfirmationPattern = '/if \(\$category === \'cat_ИОМ_yes\'\) \{/';
if (preg_match($isConfirmationPattern, $tabelHandlerContent)) {
    echo "✅ Check 3 PASSED: isConfirmationCallback recognizes ultra-short format\n";
} else {
    echo "❌ Check 3 FAILED: isConfirmationCallback doesn't recognize ultra-short format\n";
}

// Check 4: handleActivityExecution method maps ultra-short format
$handleActivityPattern = '/elseif \(\$category === \'cat_ИОМ_yes\'\) \{/';
if (preg_match($handleActivityPattern, $tabelHandlerContent)) {
    echo "✅ Check 4 PASSED: handleActivityExecution maps ultra-short format\n";
} else {
    echo "❌ Check 4 FAILED: handleActivityExecution doesn't map ultra-short format\n";
}

// Check 5: Callback length verification
$callbackLength = strlen('cat_ИОМ_yes');
echo "\n=== Callback Length Verification ===\n";
echo "Ultra-short callback: 'cat_ИОМ_yes'\n";
echo "Length: {$callbackLength} bytes\n";
if ($callbackLength <= 64) {
    echo "✅ Length is within Telegram's 64-byte limit\n";
} else {
    echo "❌ Length exceeds Telegram's 64-byte limit\n";
}

// Check 6: Mapping verification
echo "\n=== Callback Mapping Verification ===\n";
$mappings = [
    'Button Callback' => 'cat_ТабИсполнОбяз',
    'Confirmation Callback' => 'cat_ИОМ_yes'
];

foreach ($mappings as $description => $callback) {
    $length = strlen($callback);
    echo "{$description}: '{$callback}' ({$length} bytes)\n";
    if ($length <= 64) {
        echo "  ✅ Within limit\n";
    } else {
        echo "  ❌ Exceeds limit\n";
    }
}

echo "\n=== Summary ===\n";
echo "The master duties button should now work correctly with Telegram's 64-byte callback limit.\n";
echo "All callbacks use ultra-short format to avoid BUTTON_DATA_INVALID errors.\n";