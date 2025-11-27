<?php
/**
 * Quick test to verify warranty equipment handler registration and basic flow
 */

echo "=== TESTING WARRANTY EQUIPMENT HANDLER REGISTRATION ===\n\n";

// Test if the TabelHandler's canHandle method would return true for warranty callbacks
echo "1. Testing TabelHandler canHandle method logic:\n";
echo "=" . str_repeat("=", 50) . "\n";

$warrantyCallbacks = [
    'cat_Гарантии_ЭлектрОб',
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes',
    'cat_Гарантии_Прочее_yes'
];

foreach ($warrantyCallbacks as $callback) {
    echo "Testing: '{$callback}'\n";
    
    // Simulate TabelHandler::canHandle logic
    $canHandle = str_starts_with($callback, 'category_Табель') || 
                 str_starts_with($callback, 'cat_ТабПНРаботы') ||
                 str_starts_with($callback, 'cat_Таб') ||
                 str_starts_with($callback, 'cat_Гарантии_') ||
                 $callback === 'новое_прочее_yes' ||
                 $callback === 'cat_ТабЭлОб_Проч_yes' ||
                 $callback === 'cat_ТабВентОтопл' ||
                 $callback === 'cat_ТабОтоплВент' ||
                 str_starts_with($callback, 'cat_ТабВентОтопл_') ||
                 str_starts_with($callback, 'cat_ТабОтоплВент_') ||
                 $callback === 'cat_ТабГарантии';
    
    echo "  TabelHandler canHandle: " . ($canHandle ? "✅ YES" : "❌ NO") . "\n";
    echo "\n";
}

echo "2. Testing CallbackqueryCommand early exit filter:\n";
echo "=" . str_repeat("=", 50) . "\n";

foreach ($warrantyCallbacks as $callback) {
    echo "Testing: '{$callback}'\n";
    
    // Simulate CallbackqueryCommand early exit logic
    $passesFilter = strpos($callback, 'category_') === 0 || 
                    strpos($callback, 'adm_req_') === 0 || 
                    strpos($callback, 'acc_lvl_') === 0 || 
                    strpos($callback, 'cat_Таб') === 0 ||
                    strpos($callback, 'cat_Гарантии') === 0 ||
                    strpos($callback, 'proj_ctrl_') === 0 ||
                    strpos($callback, 'proj_list_') === 0;
    
    echo "  CallbackqueryCommand passes filter: " . ($passesFilter ? "✅ YES" : "❌ NO") . "\n";
    echo "\n";
}

echo "3. Testing isActivityExecution pattern matching:\n";
echo "=" . str_repeat("=", 50) . "\n";

$warrantyConfirmations = [
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes',
    'cat_Гарантии_Прочее_yes'
];

foreach ($warrantyConfirmations as $callback) {
    echo "Testing: '{$callback}'\n";
    
    // Test warranty equipment pattern matching
    if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback, $matches)) {
        $equipmentType = $matches[1];
        echo "  Pattern matches: ✅ Equipment type = '{$equipmentType}'\n";
        
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
            echo "  Activity type mapped: ✅ '{$activityType}'\n";
        } else {
            echo "  Activity type mapping: ❌ FAILED\n";
        }
    } else {
        echo "  Pattern matches: ❌ NO MATCH\n";
    }
    echo "\n";
}

echo "=== DIAGNOSIS ===\n";
echo "If all tests pass but the user reports no logs, the issue might be:\n";
echo "1. TabelHandler not being registered in CallbackHandlerFactory\n";
echo "2. Another handler with higher priority is capturing the callbacks\n";
echo "3. The bot server needs to be restarted to load updated code\n";
echo "4. The actual callback data format is different from expected\n";
echo "5. A PHP error is preventing TabelHandler from being instantiated\n\n";

echo "RECOMMENDATION: Check the bot server logs and restart the bot if needed.\n";
?>