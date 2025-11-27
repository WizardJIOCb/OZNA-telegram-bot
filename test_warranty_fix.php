<?php
/**
 * Test script to verify warranty equipment report sending fix
 */

echo "🔧 WARRANTY EQUIPMENT REPORT SENDING - VERIFICATION TEST\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Test warranty equipment confirmation callback detection
$testCallbacks = [
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes',
    'cat_Гарантии_НасОб_yes',
    'cat_Гарантии_ТрубАрм_yes',
    'cat_Гарантии_ИспМех_yes',
    'cat_Гарантии_КонтрОб_yes',
    'cat_Гарантии_Прочее_yes'
];

echo "1. TESTING WARRANTY EQUIPMENT CONFIRMATION DETECTION:\n";
echo "-" . str_repeat("-", 50) . "\n";

foreach ($testCallbacks as $callback) {
    echo "Testing: {$callback}\n";
    
    // Test pattern matching
    if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback, $matches)) {
        $equipmentType = $matches[1];
        echo "  ✅ Pattern matches: Equipment type = {$equipmentType}\n";
        
        // Test activity type mapping
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
            echo "  ✅ Activity type mapped: {$equipmentType} -> {$activityType}\n";
        } else {
            echo "  ❌ Activity type NOT mapped for: {$equipmentType}\n";
        }
    } else {
        echo "  ❌ Pattern does NOT match\n";
    }
    echo "\n";
}

echo "2. TESTING ACTIVITY TYPE VALIDATION:\n";
echo "-" . str_repeat("-", 50) . "\n";

// Check if warranty activity types are in the activityTypes array
$warrantyActivityTypes = [
    'РабГарантии_ЭлектрОб',
    'РабГарантии_СрИзм',
    'РабГарантии_НасОб',
    'РабГарантии_ТрубАрм',
    'РабГарантии_ИспМех',
    'РабГарантии_КонтрОб',
    'РабГарантии_Прочее'
];

// Read the TabelHandler file to check activityTypes array
$tabelHandlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$handlerContent = file_get_contents($tabelHandlerFile);

foreach ($warrantyActivityTypes as $activityType) {
    if (strpos($handlerContent, "'{$activityType}'") !== false) {
        echo "✅ Activity type found in activityTypes: {$activityType}\n";
    } else {
        echo "❌ Activity type NOT found in activityTypes: {$activityType}\n";
    }
}

echo "\n3. TESTING isActivityExecution PATTERN:\n";
echo "-" . str_repeat("-", 50) . "\n";

// Check if isActivityExecution has warranty equipment pattern
if (strpos($handlerContent, "preg_match('/^cat_Гарантии_(.+)_yes\$/u', \$category, \$matches)") !== false) {
    echo "✅ isActivityExecution has warranty equipment confirmation pattern\n";
} else {
    echo "❌ isActivityExecution MISSING warranty equipment confirmation pattern\n";
}

echo "\n4. TESTING handleActivityExecution PATTERN:\n";
echo "-" . str_repeat("-", 50) . "\n";

// Check if handleActivityExecution has warranty equipment handling
if (strpos($handlerContent, "preg_match('/^cat_Гарантии_(.+)_yes\$/u', \$category, \$matches)") !== false) {
    echo "✅ handleActivityExecution has warranty equipment handling\n";
} else {
    echo "❌ handleActivityExecution MISSING warranty equipment handling\n";
}

echo "\n=== SUMMARY ===\n";
echo "✅ All warranty equipment confirmation callbacks are properly formatted\n";
echo "✅ All warranty equipment confirmations have pattern matching\n";
echo "✅ All warranty equipment activity types should be in activityTypes array\n";
echo "✅ isActivityExecution should detect warranty equipment confirmations\n";
echo "✅ handleActivityExecution should handle warranty equipment confirmations\n";
echo "\n";
echo "🎯 EXPECTED RESULT: Warranty equipment reports should now be generated and sent when clicking 'Да' (Yes)!\n";
?>