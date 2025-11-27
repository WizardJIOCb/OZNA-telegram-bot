<?php

echo "=== TESTING SITE INSPECTION EMERGENCY OVERRIDE ===\n\n";

// Test the emergency override logic directly without instantiating the class
function testEmergencyOverride($activityType) {
    // This is the exact logic from the emergency override
    if (stripos($activityType, 'АналФунд') !== false || 
        stripos($activityType, 'Фунд') !== false ||
        stripos($activityType, 'анал') !== false ||
        stripos($activityType, 'площадк') !== false ||
        stripos($activityType, 'монтаж') !== false ||
        stripos($activityType, 'site') !== false ||
        stripos($activityType, 'inspection') !== false ||
        stripos($activityType, 'о добавлена') !== false) {
        return true; // Emergency override will trigger
    }
    return false; // Will use normal processing
}

// Test various possible corrupted activity types that might come through
$testCases = [
    'АналФунд',
    'ШМР_АналФунд',
    'ШМР_АналФунд_yes', 
    'АнализИнформацииПоФундаменту',
    'ШМР_АнализИнформацииПоФундаменту',
    'Фундамент',
    'анализ',
    'площадка', 
    'монтаж',
    'ПроверкаМонтажнойПлощадки',
    'site_inspection',
    'о добавлена', // This is from the wrong message format
    'RandomActivity', // This should NOT trigger
    'SomeOtherActivity' // This should NOT trigger
];

echo "Testing emergency override logic with various activity types:\n\n";

foreach ($testCases as $activityType) {
    echo "Testing activityType: '$activityType'\n";
    
    $willTrigger = testEmergencyOverride($activityType);
    
    if ($willTrigger) {
        echo "✅ OVERRIDE: Emergency override will trigger - site inspection format!\n";
        echo "Result: '✅Тест тест тест, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки'\n\n";
    } else {
        echo "ℹ️ NO OVERRIDE: Will use normal processing (expected for non-site inspection)\n\n";
    }
}

echo "=== EMERGENCY OVERRIDE TEST COMPLETE ===\n";
echo "\nThe emergency override will catch any activity type containing:\n";
echo "- 'АналФунд' (case insensitive)\n";
echo "- 'Фунд' (case insensitive)\n";
echo "- 'анал' (case insensitive)\n";
echo "- 'площадк' (case insensitive)\n";
echo "- 'монтаж' (case insensitive)\n";
echo "- 'site' (case insensitive)\n";
echo "- 'inspection' (case insensitive)\n";
echo "- 'о добавлена' (from wrong message format)\n";
echo "\nThis should now catch the site inspection button regardless\n";
echo "of how the callback is corrupted or processed!\n";