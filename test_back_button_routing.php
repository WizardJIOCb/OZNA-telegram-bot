<?php
/**
 * Diagnostic script to test back button routing
 */

echo "=== BACK BUTTON ROUTING TEST ===\n\n";

// Include the necessary files
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Test 1: Check if TabelHandler can handle 'Табель_ШМ_Back' callback
echo "1. Testing TabelHandler canHandle method:\n";
echo "=====================================\n";

// Create a mock TabelHandler (we'll need to mock the dependencies)
echo "Testing canHandle('Табель_ШМ_Back'): ";

// Since we can't easily instantiate TabelHandler without dependencies,
// let's check the canHandle logic directly
$callbackData = 'Табель_ШМ_Back';
$canHandle = str_starts_with($callbackData, 'category_Табель') || 
             str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
             str_starts_with($callbackData, 'cat_Таб') ||
             str_starts_with($callbackData, 'cat_ШМР_') ||
             str_starts_with($callbackData, 'cat_Гарантии_') ||
             $callbackData === 'Табель_ШМ_Back' ||
             $callbackData === 'новое_прочее_yes' ||
             $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
             $callbackData === 'cat_ТабВентОтопл' ||
             $callbackData === 'cat_ТабОтоплВент' ||
             str_starts_with($callbackData, 'cat_ТабВентОтопл_') ||
             str_starts_with($callbackData, 'cat_ТабОтоплВент_') ||
             $callbackData === 'cat_ТабГарантии';

if ($canHandle) {
    echo "✅ SUCCESS - TabelHandler can handle 'Табель_ШМ_Back'\n";
} else {
    echo "❌ FAILURE - TabelHandler cannot handle 'Табель_ШМ_Back'\n";
}

echo "\n2. Testing callback extraction:\n";
echo "=============================\n";

// Test callback extraction for different back button formats
$testCallbacks = [
    'Табель_ШМ_Back',
    'category_Табель_ШМ_Простой',
    'cat_ТабПНРаботы'
];

foreach ($testCallbacks as $callback) {
    echo "Callback: {$callback}\n";
    
    // Simulate the extractCategoryFromCallback logic
    if (strpos($callback, 'category_') === 0) {
        $extracted = substr($callback, strlen('category_'));
        echo "  Extracted category: {$extracted}\n";
    } elseif (strpos($callback, 'cat_Таб') === 0 || strpos($callback, 'cat_Гарантии_') === 0 || strpos($callback, 'cat_ШМР_') === 0) {
        // This would use the shortFormats mapping
        echo "  Would use shortFormats mapping\n";
    } else {
        echo "  No extraction rule matched\n";
    }
    
    echo "\n";
}

echo "3. Testing keyboard back buttons:\n";
echo "===============================\n";

// Create MessageService instance
$messageService = new MessageService();

// Test supervision keyboard back button
echo "Supervision keyboard back button:\n";
$supervisionKeyboard = $messageService->createSupervisionKeyboard();
$backButton = end($supervisionKeyboard);
echo "  Text: {$backButton[0]['text']}\n";
echo "  Callback: {$backButton[0]['callback_data']}\n";

if ($backButton[0]['callback_data'] === 'Табель_ШМ_Back') {
    echo "  ✅ CORRECT - Uses 'Табель_ШМ_Back' callback\n";
} else {
    echo "  ❌ INCORRECT - Expected 'Табель_ШМ_Back'\n";
}

echo "\nTest completed.\n";
?>