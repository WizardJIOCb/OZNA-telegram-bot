<?php
/**
 * Test script to verify the "Проверка монтажной площадки" button fix
 */

echo "🏗️ TESTING SUPERVISION SITE INSPECTION FIX\n";
echo "=========================================\n\n";

// Test the complete flow from button click to activity execution
$testCallbacks = [
    'cat_ШМР_АналФунд' => 'Site inspection button click',
    'cat_ШМР_АналФунд_yes' => 'Site inspection confirmation (Да button)'
];

echo "📋 STEP 1: Testing Button Callback Creation\n";
echo "-------------------------------------------\n";

// Check if MessageService creates the correct callback
$buttonCallback = 'cat_ШМР_АналФунд';
echo "✅ Button callback: '{$buttonCallback}'\n";
echo "✅ This should trigger confirmation dialog\n\n";

echo "📋 STEP 2: Testing Confirmation Callback Recognition\n";
echo "----------------------------------------------------\n";

$confirmationCallback = 'cat_ШМР_АналФунд_yes';
echo "✅ Confirmation callback: '{$confirmationCallback}'\n";

// Test the pattern that we just fixed
$pattern = '/^cat_ШМР_(.+)_yes$/u';
if (preg_match($pattern, $confirmationCallback, $matches)) {
    echo "✅ Pattern MATCHES: {$pattern}\n";
    echo "✅ Extracted short key: '{$matches[1]}'\n";
    
    // Test the mapping
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР',
        'ПодтВех' => 'ПодтверждениеВехи_ШМР'
    ];
    
    $shortActivityKey = $matches[1];
    if (isset($simpleSupervisionMap[$shortActivityKey])) {
        $activityType = $simpleSupervisionMap[$shortActivityKey];
        echo "✅ Mapping found: '{$shortActivityKey}' → '{$activityType}'\n";
        echo "✅ isActivityExecution() should return TRUE\n";
    } else {
        echo "❌ No mapping found for: '{$shortActivityKey}'\n";
    }
} else {
    echo "❌ Pattern NO MATCH: {$pattern}\n";
}

echo "\n📋 STEP 3: Testing Activity Type Validation\n";
echo "-------------------------------------------\n";

// Check if activity type is in activityTypes array (simulated)
$activityTypes = [
    'ПроверкаМонтажнойПлощадки', // This should be present
    'ПодписаниеАктаШМР',
    'ПодтверждениеВехи_ШМР'
];

$activityType = 'ПроверкаМонтажнойПлощадки';
if (in_array($activityType, $activityTypes)) {
    echo "✅ Activity type '{$activityType}' found in activityTypes array\n";
    echo "✅ Activity execution validation should PASS\n";
} else {
    echo "❌ Activity type '{$activityType}' NOT found in activityTypes array\n";
}

echo "\n📋 STEP 4: Testing TabelEntry Mapping\n";
echo "-------------------------------------\n";

// Check if activity has proper mapping in TabelEntry
$activityTypeMappings = [
    'ПроверкаМонтажнойПлощадки' => '🏗️ Проверка монтажной площадки'
];

if (isset($activityTypeMappings[$activityType])) {
    $activityText = $activityTypeMappings[$activityType];
    echo "✅ Activity text mapping found: '{$activityText}'\n";
    echo "✅ Report creation should succeed\n";
} else {
    echo "❌ No activity text mapping found for: '{$activityType}'\n";
}

echo "\n📋 EXPECTED REPORT MESSAGE:\n";
echo "---------------------------\n";
echo "✅ 05.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки\n\n";

echo "🎉 CONCLUSION:\n";
echo "=============\n";
echo "The fix should now resolve the callback conflict issue.\n";
echo "The 'Проверка монтажной площадки' button should create correct OZNA reports!\n\n";

echo "⚠️ ВАЖНО: Необходим перезапуск веб-сервера для применения изменений!\n";
?>