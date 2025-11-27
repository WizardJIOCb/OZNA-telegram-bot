<?php
// Test script to verify the specific warranty case from the logs

echo "Testing specific warranty case from logs...\n\n";

// Simulate the exact scenario from the logs
$category = 'Табель_РабГарантии_ЭлектрОб';
$activityType = str_replace('Табель_', '', $category);

echo "Input Category: $category\n";
echo "Extracted Activity Type: $activityType\n\n";

// Test our new logic
$callbackData = '';

// Check if it's a warranty equipment activity
if (strpos($activityType, 'РабГарантии_') === 0) {
    // Use short format for warranty equipment activities to avoid BUTTON_DATA_INVALID error
    // Extract the equipment type (e.g., "ЭлектрОб" from "РабГарантии_ЭлектрОб")
    $equipmentType = substr($activityType, strlen('РабГарантии_'));
    $callbackData = "cat_Гарантии_{$equipmentType}_yes";
    echo "✅ WARRANTY EQUIPMENT DETECTED\n";
    echo "Equipment Type: $equipmentType\n";
} else {
    // Use full format for confirmation
    $callbackData = "category_Табель_{$activityType}_yes";
    echo "❌ Not warranty equipment\n";
}

echo "\nGenerated Callback Data: $callbackData\n";
echo "Callback Data Length: " . strlen($callbackData) . " bytes\n";

if (strlen($callbackData) <= 64) {
    echo "✅ SUCCESS: Callback data is within 64-byte limit\n";
} else {
    echo "❌ FAILURE: Callback data exceeds 64-byte limit\n";
}

echo "\nComparison with problematic old format:\n";
$oldFormat = "category_Табель_РабГарантии_ЭлектрОб_yes";
echo "Old format: $oldFormat (" . strlen($oldFormat) . " bytes)\n";
echo "New format: $callbackData (" . strlen($callbackData) . " bytes)\n";
echo "Length reduction: " . (strlen($oldFormat) - strlen($callbackData)) . " bytes\n";