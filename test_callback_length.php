<?php

// Test callback processing for project management
echo "Testing callback processing...\n";

$callbackData = 'category_УправлениеПроектом';
echo "Callback data: '{$callbackData}'\n";
echo "Callback length: " . strlen($callbackData) . " bytes\n";
echo "Callback hex: " . bin2hex($callbackData) . "\n\n";

// Simulate the CallbackHandlerFactory extraction logic
function extractCategory($callbackData) {
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    return '';
}

$category = extractCategory($callbackData);
echo "Extracted category: '{$category}'\n";

// Test ProjectManagementHandler canHandle method
function canHandle($callbackData) {
    return str_starts_with($callbackData, 'category_УправлениеПроектом');
}

$canHandle = canHandle($callbackData);
echo "ProjectManagementHandler canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";

echo "\nTest completed!\n";