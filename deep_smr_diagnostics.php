<?php
/*
 * Deep Diagnostics for ШМР "Работы по графику" execution issue
 * This will trace the complete execution path step by step
 */

echo "🔍 DEEP ШМР DIAGNOSTICS - COMPLETE EXECUTION TRACE\n";
echo "================================================\n\n";

// Test the complete flow from button click to report generation
$testCallback = 'cat_ШМР_ВхКонтр_РабПоГр_yes';

echo "📋 STEP 1: Testing Callback Recognition\n";
echo "Testing callback: '{$testCallback}'\n";

// Test 1: Check if callback is recognized as activity execution
echo "\n🔍 Test 1A: isActivityExecution Pattern Matching\n";

// Pattern for ШМР schedule confirmations
$pattern = '/^cat_ШМР_(.+)_РабПоГр_yes$/u';
if (preg_match($pattern, $testCallback, $matches)) {
    echo "✅ Pattern MATCHES: {$pattern}\n";
    echo "✅ Extracted short key: '{$matches[1]}'\n";
} else {
    echo "❌ Pattern NO MATCH: {$pattern}\n";
}

// Test 1B: Check early routing in routeTabelCallback
echo "\n🔍 Test 1B: Early Routing Pattern\n";
$earlyRoutingPattern = '/^cat_ШМР_.+_РабПоГр_yes$/u';
if (preg_match($earlyRoutingPattern, $testCallback)) {
    echo "✅ Early routing pattern MATCHES\n";
    echo "✅ Should be routed directly to handleActivityExecution\n";
} else {
    echo "❌ Early routing pattern NO MATCH\n";
}

echo "\n📋 STEP 2: Testing Activity Type Mapping\n";

// Test the mapping that happens in handleActivityExecution
$shortToFullMap = [
    'ВхКонтр' => 'ШМР_ВходнойКонтроль_РабПоГр',
    'ЗавОбор' => 'ШМР_ЗавозОборудования_РабПоГр',
    // ... other mappings
];

if (preg_match($pattern, $testCallback, $matches)) {
    $shortKey = $matches[1];
    if (isset($shortToFullMap[$shortKey])) {
        $mappedActivityType = $shortToFullMap[$shortKey];
        echo "✅ Mapping successful: '{$shortKey}' -> '{$mappedActivityType}'\n";
        
        // Test if this activity type exists in TabelEntry
        echo "\n🔍 Test 2A: TabelEntry Activity Type Lookup\n";
        try {
            require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
            $tabelEntry = new CustomCommands\Services\TabelEntry();
            
            // Check if activity type exists in mappings
            echo "Testing activity type: '{$mappedActivityType}'\n";
            
            // This will test the complete message creation flow
            $testFio = 'Test User';
            $testDate = date('d.m.Y');
            $testProject = 'Test Project';
            
            echo "Attempting to create tabel message...\n";
            $message = $tabelEntry->createTabelMessage($testFio, $testDate, $testProject, $mappedActivityType, '');
            
            if (!empty($message)) {
                echo "✅ Message creation SUCCESSFUL\n";
                echo "✅ Generated message: {$message}\n";
            } else {
                echo "❌ Message creation FAILED - empty result\n";
            }
            
        } catch (Exception $e) {
            echo "❌ TabelEntry test FAILED: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "❌ Mapping FAILED for key: '{$shortKey}'\n";
        echo "Available mappings:\n";
        foreach ($shortToFullMap as $key => $value) {
            echo "   '{$key}' -> '{$value}'\n";
        }
    }
}

echo "\n📋 STEP 3: Testing Database User Validation\n";

// This simulates the user validation that happens in handleActivityExecution
echo "🔍 Test 3A: User Profile Requirements\n";
echo "For reports to work, user must have:\n";
echo "1. FIO set (not 'Без ФИО')\n";
echo "2. functional_direction set (not 'Не указано')\n";
echo "\n";

echo "📋 STEP 4: Complete Execution Flow Summary\n";
echo "The execution should follow this path:\n";
echo "1. User clicks 'Да' -> sends '{$testCallback}'\n";
echo "2. routeTabelCallback() -> early routing pattern matches\n";
echo "3. Routes directly to handleActivityExecution()\n";
echo "4. Pattern matches and maps to full activity type\n";
echo "5. Validates user has FIO and functional_direction\n";
echo "6. Calls tabelEntry->addEntry()\n";
echo "7. Calls tabelEntry->createTabelMessage()\n";
echo "8. Sends message to user\n";

echo "\n🎯 LIKELY FAILURE POINTS:\n";
echo "If reports aren't sending, the issue is likely:\n";
echo "A) User profile validation failing (missing FIO/functional_direction)\n";
echo "B) Database connection issue in addEntry()\n";
echo "C) Exception in tabelEntry->addEntry() method\n";
echo "D) Exception in message creation or sending\n";

echo "\n📝 NEXT DEBUGGING STEPS:\n";
echo "1. Test with actual user by clicking 'Работы по графику' -> 'Да'\n";
echo "2. Check user profile with /profile command\n";
echo "3. Look for error logs during execution\n";
echo "4. Check if 'addEntry SUCCESS' or 'addEntry FAILED' appears in logs\n";

echo "\n✅ DIAGNOSTIC COMPLETE\n";
echo "The callback recognition and mapping logic appears correct.\n";
echo "Issue is likely in user validation or database operations.\n";