<?php
<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "=== Testing Supervision Back Button Fix ===\n\n";

// Load necessary files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
use CustomCommands\Services\MessageService;

// Initialize MessageService to test keyboard
$messageService = new MessageService();

// Test 1: Check createSupervisionKeyboard method
echo "1. Testing createSupervisionKeyboard() method:\n";
$keyboard = $messageService->createSupervisionKeyboard();

$backButtonFound = false;
$correctText = false;
$correctCallback = false;

foreach ($keyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && isset($button['callback_data'])) {
            echo "   • {$button['text']} → {$button['callback_data']}\n";
            
            // Check for back button
            if (strpos($button['text'], 'Вернуться в табель') !== false) {
                $backButtonFound = true;
                $correctText = ($button['text'] === '⬅️ Вернуться в табель');
                $correctCallback = ($button['callback_data'] === 'category_Табель');
            }
        }
    }
}

echo "\nBack button analysis:\n";
echo "   ✅ Back button found: " . ($backButtonFound ? 'YES' : 'NO') . "\n";
echo "   ✅ Correct text ('⬅️ Вернуться в табель'): " . ($correctText ? 'YES' : 'NO') . "\n";
echo "   ✅ Correct callback ('category_Табель'): " . ($correctCallback ? 'YES' : 'NO') . "\n";

// Test 2: Check createSupervisionProjectActivityKeyboard method
echo "\n2. Testing createSupervisionProjectActivityKeyboard() method:\n";
$projectKeyboard = $messageService->createSupervisionProjectActivityKeyboard();

$projectBackButtonFound = false;
$projectCorrectText = false;
$projectCorrectCallback = false;

foreach ($projectKeyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && isset($button['callback_data'])) {
            echo "   • {$button['text']} → {$button['callback_data']}\n";
            
            // Check for back button
            if (strpos($button['text'], 'Вернуться в табель') !== false) {
                $projectBackButtonFound = true;
                $projectCorrectText = ($button['text'] === '⬅️ Вернуться в табель');
                $projectCorrectCallback = ($button['callback_data'] === 'category_Табель');
            }
        }
    }
}

echo "\nProject activity back button analysis:\n";
echo "   ✅ Back button found: " . ($projectBackButtonFound ? 'YES' : 'NO') . "\n";
echo "   ✅ Correct text ('⬅️ Вернуться в табель'): " . ($projectCorrectText ? 'YES' : 'NO') . "\n";
echo "   ✅ Correct callback ('category_Табель'): " . ($projectCorrectCallback ? 'YES' : 'NO') . "\n";

// Test 3: Check for any remaining instances of old callback
echo "\n3. Checking for old 'category_Табель_ШМ_Back' references:\n";

$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

$oldCallbackFound = false;

if (strpos($messageServiceContent, "'callback_data' => 'category_Табель_ШМ_Back'") !== false ||
    strpos($tabelHandlerContent, "'callback_data' => 'category_Табель_ШМ_Back'") !== false) {
    $oldCallbackFound = true;
    echo "   ❌ Old callback 'category_Табель_ШМ_Back' still found!\n";
} else {
    echo "   ✅ No old callbacks found - all updated successfully!\n";
}

echo "\n=== Summary ===\n";

$allTestsPassed = $backButtonFound && $correctText && $correctCallback && 
                  $projectBackButtonFound && $projectCorrectText && $projectCorrectCallback && 
                  !$oldCallbackFound;

if ($allTestsPassed) {
    echo "🎉 ALL TESTS PASSED! Supervision back button fix is successful!\n\n";
    echo "Expected user flow:\n";
    echo "1. User selects 'Отдел ШМР' → Date → Project\n";
    echo "2. User sees supervision activities with '⬅️ Вернуться в табель' button\n";
    echo "3. User clicks the back button\n";
    echo "4. User returns to main tabel menu (not stuck in supervision submenu)\n\n";
    echo "✅ Button text: '⬅️ Вернуться в табель' (was: '⬅️ Назад')\n";
    echo "✅ Callback: 'category_Табель' (was: 'category_Табель_ШМ_Back')\n";
    echo "✅ Navigation: Returns to main tabel menu (was: staying in supervision)\n";
} else {
    echo "❌ SOME TESTS FAILED! Please check the issues above.\n";
}

echo "\n=== Fix Complete ===\n";
?>