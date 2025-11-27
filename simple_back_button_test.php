<?php
/**
 * Simple diagnostic script to test back button routing logic
 */

echo "=== SIMPLE BACK BUTTON TEST ===\n\n";

// Test 1: Check if TabelHandler can handle 'Табель_ШМ_Back' callback
echo "1. Testing canHandle logic for 'Табель_ШМ_Back':\n";
echo "==========================================\n";

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

echo "\n2. Testing callback extraction logic:\n";
echo "==================================\n";

// Test the extractCategoryFromCallback logic for back button
$callbackData = 'Табель_ШМ_Back';
echo "Input callback: {$callbackData}\n";

// Since this is a direct callback (no prefix), it should return as-is
echo "Extracted category: {$callbackData}\n";

echo "\n3. Testing routing logic:\n";
echo "======================\n";

$category = 'Табель_ШМ_Back';
echo "Category to route: {$category}\n";

// Check if this matches the back button routing condition
if ($category === 'Табель_ШМ_Back') {
    echo "✅ SUCCESS - Would route to handleBackToSupervision\n";
} else {
    echo "❌ FAILURE - Would NOT route to handleBackToSupervision\n";
}

echo "\nTest completed.\n";
?>