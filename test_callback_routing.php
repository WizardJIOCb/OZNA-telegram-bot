<?php

// Test to check the callback routing and handling
echo "=== DEBUGGING CALLBACK ROUTING ===\n\n";

// Simulate the exact flow when 'новое_прочее_yes' is received

$callbackData = 'новое_прочее_yes';

echo "1. Testing canHandle method logic:\n";

// TabelHandler canHandle logic
$canHandle = str_starts_with($callbackData, 'category_Табель') || 
             str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
             str_starts_with($callbackData, 'cat_Таб') ||
             $callbackData === 'новое_прочее_yes';

echo "   Callback data: '{$callbackData}'\n";
echo "   str_starts_with('category_Табель'): " . (str_starts_with($callbackData, 'category_Табель') ? 'true' : 'false') . "\n";
echo "   str_starts_with('cat_ТабПНРаботы'): " . (str_starts_with($callbackData, 'cat_ТабПНРаботы') ? 'true' : 'false') . "\n";
echo "   str_starts_with('cat_Таб'): " . (str_starts_with($callbackData, 'cat_Таб') ? 'true' : 'false') . "\n";
echo "   === 'новое_прочее_yes': " . ($callbackData === 'новое_прочее_yes' ? 'true' : 'false') . "\n";
echo "   ✅ canHandle result: " . ($canHandle ? 'TRUE' : 'FALSE') . "\n\n";

echo "2. Testing isConfirmationCallback logic:\n";

function isConfirmationCallback(string $category): bool
{
    // Check for new Prochee confirmation callback
    if ($category === 'новое_прочее_yes') {
        return true;
    }
    
    return str_contains($category, '_yes') && str_contains($category, 'Табель_');
}

$isConfirmation = isConfirmationCallback($callbackData);
echo "   ✅ isConfirmationCallback result: " . ($isConfirmation ? 'TRUE' : 'FALSE') . "\n\n";

echo "3. Testing routing logic:\n";

// Check if it would route to NEW прочее confirmation
if ($callbackData === 'cat_ТабЭлОб_Проч_yes' || $callbackData === 'новое_прочее_yes') {
    echo "   ✅ Would route to handleNewProcheeConfirmation\n";
} else {
    echo "   ❌ Would NOT route to handleNewProcheeConfirmation\n";
}

echo "\n4. Check alternative standard format:\n";
$standardCallback = 'cat_ТабЭлОб_Проч_yes';
echo "   Standard callback: '{$standardCallback}'\n";
echo "   Would route to handleNewProcheeConfirmation: " . (($standardCallback === 'cat_ТабЭлОб_Проч_yes' || $standardCallback === 'новое_прочее_yes') ? 'TRUE' : 'FALSE') . "\n";

echo "\n=== ROUTING TEST COMPLETED ===\n";
?>