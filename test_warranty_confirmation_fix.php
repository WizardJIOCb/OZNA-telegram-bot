<?php
// Test script to verify warranty equipment callback data length fix

echo "Testing warranty equipment callback data length fix...\n\n";

// Test case: Electrical equipment warranty
$activityType = 'РабГарантии_ЭлектрОб';
$equipmentType = substr($activityType, strlen('РабГарантии_'));
$callbackData = "cat_Гарантии_{$equipmentType}_yes";

echo "Activity Type: $activityType\n";
echo "Equipment Type: $equipmentType\n";
echo "Callback Data: $callbackData\n";
echo "Callback Data Length: " . strlen($callbackData) . " bytes\n";

if (strlen($callbackData) <= 64) {
    echo "✅ SUCCESS: Callback data is within 64-byte limit\n";
} else {
    echo "❌ FAILURE: Callback data exceeds 64-byte limit\n";
}

echo "\n";

// Test other warranty equipment types
$warrantyTypes = [
    'РабГарантии_СрИзм',
    'РабГарантии_НасОб',
    'РабГарантии_ТрубАрм',
    'РабГарантии_ИспМех',
    'РабГарантии_КонтрОб',
    'РабГарантии_Прочее'
];

foreach ($warrantyTypes as $type) {
    $equipmentType = substr($type, strlen('РабГарантии_'));
    $callbackData = "cat_Гарантии_{$equipmentType}_yes";
    
    echo "Activity Type: $type\n";
    echo "Callback Data: $callbackData\n";
    echo "Callback Data Length: " . strlen($callbackData) . " bytes\n";
    
    if (strlen($callbackData) <= 64) {
        echo "✅ SUCCESS: Within limit\n";
    } else {
        echo "❌ FAILURE: Exceeds limit\n";
    }
    echo "\n";
}

// Compare with old format
$oldCallbackData = "category_Табель_РабГарантии_ЭлектрОб_yes";
echo "Old format callback data: $oldCallbackData\n";
echo "Old format length: " . strlen($oldCallbackData) . " bytes\n";
echo "Old format exceeds limit: " . (strlen($oldCallbackData) > 64 ? "YES" : "NO") . "\n";