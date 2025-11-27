<?php
/**
 * Test warranty equipment callback flow to identify the current issue
 */

echo "=== TESTING CURRENT WARRANTY EQUIPMENT CALLBACK FLOW ===\n\n";

// Test the warranty equipment initial selection callbacks
$warrantyInitialCallbacks = [
    'cat_ТабГарантии',
    'Табель_РабГарантии_ЭлектрОб',
    'Табель_РабГарантии_СрИзм',
    'Табель_РабГарантии_НасОб',
];

echo "1. TESTING WARRANTY EQUIPMENT INITIAL SELECTION:\n";
echo "===============================================\n";

foreach ($warrantyInitialCallbacks as $callback) {
    if (str_starts_with($callback, 'Табель_РабГарантии_')) {
        $equipmentType = str_replace('Табель_РабГарантии_', '', $callback);
        $activityType = 'РабГарантии_' . $equipmentType;
        $confirmationCallback = "cat_Гарантии_{$equipmentType}_yes";
        echo "✅ INITIAL: $callback -> Equipment: $equipmentType -> Confirmation: $confirmationCallback\n";
    } else {
        echo "🔧 MENU: $callback -> Should show warranty equipment menu\n";
    }
}

echo "\n2. TESTING WARRANTY EQUIPMENT CONFIRMATION CALLBACKS:\n";
echo "====================================================\n";

$warrantyConfirmationCallbacks = [
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes',
    'cat_Гарантии_НасОб_yes',
    'cat_Гарантии_ТрубАрм_yes',
    'cat_Гарантии_ИспМех_yes',
    'cat_Гарантии_КонтрОб_yes',
    'cat_Гарантии_Прочее_yes'
];

// Test callback mapping (simulating extractCategoryFromCallback)
$shortFormats = [
    'cat_Гарантии_ЭлектрОб_yes' => 'РабГарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes' => 'РабГарантии_СрИзм_yes',
    'cat_Гарантии_НасОб_yes' => 'РабГарантии_НасОб_yes',
    'cat_Гарантии_ТрубАрм_yes' => 'РабГарантии_ТрубАрм_yes',
    'cat_Гарантии_ИспМех_yes' => 'РабГарантии_ИспМех_yes',
    'cat_Гарантии_КонтрОб_yes' => 'РабГарантии_КонтрОб_yes',
    'cat_Гарантии_Прочее_yes' => 'РабГарантии_Прочее_yes'
];

foreach ($warrantyConfirmationCallbacks as $callback) {
    if (isset($shortFormats[$callback])) {
        $mapped = $shortFormats[$callback];
        echo "✅ MAPPED: $callback -> $mapped\n";
        
        // Test activity execution pattern
        if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback, $matches)) {
            $equipmentType = $matches[1];
            $activityType = 'РабГарантии_' . $equipmentType;
            echo "  ✅ PATTERN MATCH: Equipment: $equipmentType -> Activity: $activityType\n";
        } else {
            echo "  ❌ PATTERN NO MATCH\n";
        }
    } else {
        echo "❌ NOT MAPPED: $callback\n";
    }
}

echo "\n3. TESTING ELECTRICAL EQUIPMENT CALLBACK (USER REPORTED BROKEN):\n";
echo "===============================================================\n";

// Test electrical equipment specifically since user reported it's broken
$electricalCallbacks = [
    'cat_ТабЭлОб', // Initial electrical equipment menu
    'cat_ТабЭлОб_РабПоГр_yes', // Electrical equipment confirmation
];

foreach ($electricalCallbacks as $callback) {
    if ($callback === 'cat_ТабЭлОб') {
        echo "🔌 ELECTRICAL MENU: $callback -> Should show electrical equipment menu\n";
    } elseif (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $callback, $matches)) {
        $shortActivityKey = $matches[1];
        echo "🔌 ELECTRICAL CONFIRMATION: $callback -> Activity: $shortActivityKey\n";
        
        // Test if this matches electrical equipment pattern in handleActivityExecution
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_ЭлектрОб',
            'ФормИД' => 'ФормИД_ЭлектрОб',
            'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
            'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
            'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
            'МонтОб' => 'МонтОб_ЭлектрОб',
            'ДопРаб' => 'ДопРаб_ЭлектрОб',
            'ПодтВех' => 'ПодтВех_ЭлектрОб'
        ];
        
        if (isset($shortToFullMap[$shortActivityKey])) {
            $activityType = $shortToFullMap[$shortActivityKey];
            echo "  ✅ ELECTRICAL ACTIVITY MAPPED: $shortActivityKey -> $activityType\n";
        } else {
            echo "  ❌ ELECTRICAL ACTIVITY NOT MAPPED: $shortActivityKey\n";
        }
    }
}

echo "\n=== CURRENT STATUS ANALYSIS ===\n";
echo "✅ Warranty equipment confirmation callbacks are properly mapped\n";
echo "✅ Warranty equipment confirmation patterns should work\n";
echo "✅ Electrical equipment patterns should work\n";
echo "⚠️  Issue might be in activity execution logic or validation\n";
echo "📝 Need to check isActivityExecution and handleActivityExecution methods\n";