<?php
/**
 * Test warranty equipment regex patterns directly
 */

echo "🔧 WARRANTY EQUIPMENT REGEX TEST\n";
echo "=".str_repeat("=", 50)."\n\n";

// Test callback data
$testCallbackData = 'cat_Гарантии_ЭлектрОб_yes';
echo "Testing callback data: '{$testCallbackData}'\n\n";

echo "1. TESTING MAIN WARRANTY REGEX PATTERN\n";
echo "-".str_repeat("-", 40)."\n";

// Test the main regex pattern used in isActivityExecution
if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $testCallbackData, $matches)) {
    $equipmentType = $matches[1];
    echo "✅ Regex pattern matched successfully\n";
    echo "   Equipment type extracted: '{$equipmentType}'\n";
    
    // Test the warranty equipment map
    $warrantyEquipmentMap = [
        'ЭлектрОб' => 'РабГарантии_ЭлектрОб',
        'СрИзм' => 'РабГарантии_СрИзм',
        'НасОб' => 'РабГарантии_НасОб',
        'ТрубАрм' => 'РабГарантии_ТрубАрм',
        'ИспМех' => 'РабГарантии_ИспМех',
        'КонтрОб' => 'РабГарантии_КонтрОб',
        'Прочее' => 'РабГарантии_Прочее'
    ];
    
    if (isset($warrantyEquipmentMap[$equipmentType])) {
        $activityType = $warrantyEquipmentMap[$equipmentType];
        echo "✅ Equipment type found in map\n";
        echo "   Mapped to activity type: '{$activityType}'\n";
        echo "   Should return TRUE for isActivityExecution\n\n";
    } else {
        echo "❌ Equipment type NOT found in map\n\n";
    }
} else {
    echo "❌ Main regex pattern did not match\n\n";
}

echo "2. TESTING TRANSFORMED WARRANTY REGEX PATTERN\n";
echo "-".str_repeat("-", 40)."\n";

// Test the transformed warranty regex pattern
$transformedCallbackData = 'РабГарантии_ЭлектрОб_yes';
echo "Testing transformed callback data: '{$transformedCallbackData}'\n";

if (preg_match('/^РабГарантии_(.+)_yes$/u', $transformedCallbackData, $matches)) {
    $equipmentType = $matches[1];
    echo "✅ Transformed regex pattern matched successfully\n";
    echo "   Equipment type extracted: '{$equipmentType}'\n";
    
    // Check if this is a valid warranty equipment type
    $validWarrantyEquipment = [
        'ЭлектрОб', 'СрИзм', 'НасОб', 'ТрубАрм', 'ИспМех', 'КонтрОб', 'Прочее'
    ];
    
    if (in_array($equipmentType, $validWarrantyEquipment)) {
        echo "✅ Equipment type is valid\n";
        echo "   Should return TRUE for isActivityExecution\n\n";
    } else {
        echo "❌ Equipment type is NOT valid\n\n";
    }
} else {
    echo "❌ Transformed regex pattern did not match\n\n";
}

echo "3. TESTING HANDLE ACTIVITY EXECUTION REGEX\n";
echo "-".str_repeat("-", 40)."\n";

// Test the regex pattern used in handleActivityExecution
if (preg_match('/^cat_(.+)_yes$/u', $testCallbackData, $matches)) {
    $shortActivityKey = $matches[1];
    echo "✅ Short format regex matched successfully\n";
    echo "   Short activity key: '{$shortActivityKey}'\n";
    
    // Check if this matches warranty equipment
    $warrantyEquipmentMap = [
        'ЭлектрОб' => 'РабГарантии_ЭлектрОб',
        'СрИзм' => 'РабГарантии_СрИзм',
        'НасОб' => 'РабГарантии_НасОб',
        'ТрубАрм' => 'РабГарантии_ТрубАрм',
        'ИспМех' => 'РабГарантии_ИспМех',
        'КонтрОб' => 'РабГарантии_КонтрОб',
        'Прочее' => 'РабГарантии_Прочее'
    ];
    
    if (isset($warrantyEquipmentMap[$shortActivityKey])) {
        $activityType = $warrantyEquipmentMap[$shortActivityKey];
        echo "✅ Warranty equipment found in short format map\n";
        echo "   Mapped to activity type: '{$activityType}'\n";
        echo "   Should be processed in handleActivityExecution\n\n";
    } else {
        echo "❌ Warranty equipment NOT found in short format map\n\n";
    }
} else {
    echo "❌ Short format regex did not match\n\n";
}

echo "=".str_repeat("=", 50)."\n";
echo "REGEX TEST COMPLETE\n";
echo "=".str_repeat("=", 50)."\n";
?>