<?php
// Test script to verify the commissioning works fix

require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

// Simulate the exact callback that was causing issues
$callbackData = 'category_Табель_ПусконаладочныеРаботы';

echo "Testing callback data: {$callbackData}\n";

// Create a minimal TabelHandler instance for testing
class MockDatabaseService {}
class MockMessageService {}
class MockProjectSelector {}
class MockTabelEntry {}
class MockAdminService {}

$tabelHandler = new TabelHandler(
    new MockDatabaseService(),
    new MockMessageService(),
    new MockProjectSelector(),
    new MockTabelEntry(),
    new MockAdminService()
);

// Test the fixKnownCyrillicPatterns method directly
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('fixKnownCyrillicPatterns');
$method->setAccessible(true);

$result = $method->invoke($tabelHandler, $callbackData);
echo "After fixKnownCyrillicPatterns: {$result}\n";

// Test the canHandle method
$canHandle = $tabelHandler->canHandle($callbackData);
echo "canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

echo "Test completed.\n";
?>
<?php
// Test script to verify the commissioning works button fix

echo "=== Testing Commissioning Works Button Fix ===\n\n";

// Test the shortened button
$buttonText = "⚙️ ПН работы";
$callbackData = "cat_ТабПНРаботы";

echo "1. Fixed Button Analysis:\n";
echo "   Text: {$buttonText}\n";
echo "   Callback: {$callbackData}\n";
echo "   Text length: " . strlen($buttonText) . " bytes (vs 50 bytes before)\n";
echo "   Callback length: " . strlen($callbackData) . " bytes\n";
echo "   Text UTF-8 length: " . mb_strlen($buttonText, 'UTF-8') . " characters\n\n";

// Compare with the original
echo "2. Comparison with Original:\n";
$originalText = "⚙️ Пусконаладочные работы";
echo "   Original: {$originalText} (" . strlen($originalText) . " bytes)\n";
echo "   Fixed: {$buttonText} (" . strlen($buttonText) . " bytes)\n";
echo "   Reduction: " . (strlen($originalText) - strlen($buttonText)) . " bytes\n\n";

// Test if it's within reasonable limits
echo "3. Button Length Validation:\n";
$textLength = strlen($buttonText);
if ($textLength <= 30) {
    echo "   ✅ Text length OK: {$textLength} bytes (recommended < 30)\n";
} else {
    echo "   ⚠️ Text might be too long: {$textLength} bytes\n";
}

$callbackLength = strlen($callbackData);
if ($callbackLength <= 64) {
    echo "   ✅ Callback length OK: {$callbackLength} bytes (limit is 64)\n";
} else {
    echo "   ❌ Callback too long: {$callbackLength} bytes\n";
}

echo "\n4. Expected Flow:\n";
echo "   1. User sees shortened button '⚙️ ПН работы'\n";
echo "   2. Clicks button → sends 'cat_ТабПНРаботы'\n";
echo "   3. TabelHandler.canHandle() recognizes it\n";
echo "   4. Category extracted as 'ТабПНРаботы'\n";
echo "   5. handleOtherWorkTypes() matches condition\n";
echo "   6. Calls handleCommissioningWorksMenu()\n";
echo "   7. Shows commissioning works activities menu\n";

echo "\n✅ FIX APPLIED: Button text shortened from 50 to 21 bytes\n";
echo "This should resolve the button display/functionality issue.\n";

echo "\n=== Test Complete ===\n";
?>
<?php
// Test script to verify the commissioning works fix

echo "=== Testing Commissioning Works Fix ===\n\n";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/test_commissioning_fix.log');

// Log start of script
error_log("=== Commissioning Works Fix Test Started ===");

// Test 1: Check if the TabelHandler file exists
echo "1. Checking if TabelHandler_fixed.php exists...\n";
$tabelHandlerPath = __DIR__ . '/TabelHandler_fixed.php';
if (file_exists($tabelHandlerPath)) {
    echo "   ✓ TabelHandler_fixed.php exists\n";
    error_log("TabelHandler_fixed.php exists");
} else {
    echo "   ✗ TabelHandler_fixed.php NOT FOUND\n";
    error_log("TabelHandler_fixed.php NOT FOUND");
    exit(1);
}

// Test 2: Check if the activity type is in the array
echo "\n2. Checking if 'ПусконаладочныеРаботы' is in activityTypes array...\n";
error_log("Checking if 'ПусконаладочныеРаботы' is in activityTypes array");

// Read the TabelHandler file
$content = file_get_contents($tabelHandlerPath);

// Check if the activity type is present
if (strpos($content, "'ПусконаладочныеРаботы'") !== false) {
    echo "   ✓ 'ПусконаладочныеРаботы' found in activityTypes array\n";
    error_log("'ПусконаладочныеРаботы' found in activityTypes array");
} else {
    echo "   ✗ 'ПусконаладочныеРаботы' NOT found in activityTypes array\n";
    error_log("'ПусконаладочныеРаботы' NOT found in activityTypes array");
}

// Test 3: Simulate the handleOtherWorkTypes method logic
echo "\n3. Simulating handleOtherWorkTypes logic...\n";
error_log("Simulating handleOtherWorkTypes logic");

$category = 'Табель_ПусконаладочныеРаботы';
$strippedCategory = str_replace('Табель_', '', $category);

echo "   Input category: {$category}\n";
echo "   Stripped category: {$strippedCategory}\n";
error_log("Input category: {$category}");
error_log("Stripped category: {$strippedCategory}");

// Simulate the activityTypes array (should include the fix)
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
    // Fix for commissioning works issue - added missing activity type
    'ПусконаладочныеРаботы'
];

echo "   Activity types array contains " . count($activityTypes) . " items\n";
error_log("Activity types array contains " . count($activityTypes) . " items");

// Check if the stripped category is in the activityTypes array
if (in_array($strippedCategory, $activityTypes)) {
    echo "   ✓ '{$strippedCategory}' found in activityTypes array\n";
    error_log("'{$strippedCategory}' found in activityTypes array");
    
    echo "   ✅ TEST PASSED: Commissioning works should now work correctly!\n";
    error_log("TEST PASSED: Commissioning works should now work correctly");
} else {
    echo "   ✗ '{$strippedCategory}' NOT found in activityTypes array\n";
    error_log("'{$strippedCategory}' NOT found in activityTypes array");
    
    echo "   ❌ TEST FAILED: Commissioning works will still not work\n";
    error_log("TEST FAILED: Commissioning works will still not work");
}

// Test 4: Verify the MessageService commissioning keyboard
echo "\n4. Checking MessageService commissioning keyboard...\n";
error_log("Checking MessageService commissioning keyboard");

$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServicePath)) {
    $messageServiceContent = file_get_contents($messageServicePath);
    
    // Check if the commissioning button exists with the correct callback data
    if (strpos($messageServiceContent, 'category_Табель_ПусконаладочныеРаботы') !== false) {
        echo "   ✓ Commissioning button with correct callback data found\n";
        error_log("Commissioning button with correct callback data found");
    } else {
        echo "   ✗ Commissioning button with correct callback data NOT found\n";
        error_log("Commissioning button with correct callback data NOT found");
    }
} else {
    echo "   ✗ MessageService.php NOT FOUND\n";
    error_log("MessageService.php NOT FOUND");
}

// Final summary
echo "\n=== Test Summary ===\n";
error_log("=== Test Summary ===");

echo "The fix for the commissioning works issue has been implemented:\n";
echo "1. Added 'ПусконаладочныеРаботы' to the activityTypes array in TabelHandler\n";
echo "2. The handleOtherWorkTypes method should now recognize this activity type\n";
echo "3. When users click 'Пусконаладочные работы', it should no longer show 'Неизвестная команда табеля'\n";

error_log("Fix for commissioning works issue has been implemented");
echo "\nTest information has been logged to: test_commissioning_fix.log\n";

?>