<?php
/**
 * Quick test to check exact callback routing for Automation Прочее
 */

echo "=== AUTOMATION ПРОЧЕЕ CALLBACK TEST ===\n";

// Test the exact callback that should be generated
$expectedCallback = 'cat_ШМР_Автом_Проч_yes';
echo "Expected callback: '{$expectedCallback}'\n\n";

// Check current confirmation handler - simplified test
$tabelHandler = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Find the line that contains our callback
if (strpos($tabelHandler, $expectedCallback) !== false) {
    echo "✅ Callback '{$expectedCallback}' found in TabelHandler.php\n";
    
    // Count occurrences
    $count = substr_count($tabelHandler, $expectedCallback);
    echo "   Found {$count} occurrences\n";
    
    // Check if it's in the confirmation handler (around line 695)
    $lines = explode("\n", $tabelHandler);
    for ($i = 690; $i < 700; $i++) {
        if (isset($lines[$i]) && strpos($lines[$i], $expectedCallback) !== false) {
            echo "   ✅ Found in confirmation handler at line " . ($i + 1) . "\n";
            echo "   Line content: " . trim($lines[$i]) . "\n";
        }
    }
} else {
    echo "❌ Callback '{$expectedCallback}' NOT found in TabelHandler.php\n";
}

echo "\n=== POSSIBLE CAUSES OF 'Неизвестная команда табеля' ===\n";
echo "1. Callback data transformation issue in extractCategoryFromCallback\n";
echo "2. Conversation state issue (missing required notes)\n";
echo "3. Database connection issue preventing user data retrieval\n";
echo "4. Exception thrown in handleNewProcheeConfirmation method\n";
echo "5. Missing activity type detection in method\n";

echo "\n=== NEXT DEBUGGING STEPS ===\n";
echo "1. Check bot logs during testing to see exact callback received\n";
echo "2. Verify conversation state has required notes\n";
echo "3. Test with debug logging in handleNewProcheeConfirmation\n";