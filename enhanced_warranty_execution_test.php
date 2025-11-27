<?php

echo "🚀 ENHANCED WARRANTY EXECUTION TEST\n";
echo "==================================\n\n";

// Include necessary files
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/AdminService.php';

// Create mock services for testing
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector($databaseService);
$tabelEntry = new TabelEntry($databaseService);
$adminService = new AdminService($databaseService);

// Create TabelHandler with proper dependencies
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

echo "🔍 TESTING EXECUTION FLOW FOR BOTH EQUIPMENT TYPES\n";
echo "---------------------------------------------------\n\n";

// Test callback patterns
$testCallbacks = [
    'WARRANTY_ELECTRICAL' => 'category_Табель_РабГарантии_ЭлектрОб',
    'WARRANTY_PUMP' => 'category_Табель_РабГарантии_НасОб',
    'WORKING_PUMP' => 'category_Табель_НасОб',
    'WORKING_ELECTRICAL' => 'category_Табель_ЭлектрОб'
];

echo "📋 STEP 1: CALLBACK HANDLING TEST\n";
echo "--------------------------------\n";

foreach ($testCallbacks as $type => $callback) {
    echo "Testing $type: $callback\n";
    
    // Test canHandle
    $canHandle = $tabelHandler->canHandle($callback);
    echo "  ✅ canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";
    
    // Test category extraction
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('extractCategoryFromCallback');
    $method->setAccessible(true);
    $category = $method->invoke($tabelHandler, $callback);
    echo "  📝 Extracted category: $category\n";
    
    // Test handler routing
    $handleMethod = $reflection->getMethod('handleOtherWorkTypes');
    $handleMethod->setAccessible(true);
    
    echo "  🎯 Handler routing test:\n";
    
    // Mock conversation and chat ID for testing
    $mockConversation = (object)[
        'notes' => ['project_code' => 'TEST_PROJECT'],
        'update' => function() {}
    ];
    
    try {
        // Test if handler would be called (we'll catch the execution)
        if (str_contains($category, 'РабГарантии')) {
            echo "    → Would call WARRANTY equipment handler\n";
        } elseif (str_contains($category, 'НасОб')) {
            echo "    → Would call PUMP equipment handler\n";
        } elseif (str_contains($category, 'ЭлектрОб')) {
            echo "    → Would call ELECTRICAL equipment handler\n";
        } else {
            echo "    → No specific handler found\n";
        }
    } catch (Exception $e) {
        echo "    ⚠️ Handler error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "📊 STEP 2: ACTIVITY EXECUTION COMPARISON\n";
echo "---------------------------------------\n";

// Test activity execution patterns
$activityCallbacks = [
    'WARRANTY_ELECTRICAL_CONFIRM' => 'РабГарантии_ЭлектрОб_confirm',
    'WARRANTY_PUMP_CONFIRM' => 'РабГарантии_НасОб_confirm', 
    'WORKING_PUMP_CONFIRM' => 'НасОб_confirm',
    'WORKING_ELECTRICAL_CONFIRM' => 'ЭлектрОб_confirm'
];

foreach ($activityCallbacks as $type => $callback) {
    echo "Testing $type: $callback\n";
    
    // Test isActivityExecution using reflection
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('isActivityExecution');
    $method->setAccessible(true);
    $isActivity = $method->invoke($tabelHandler, $callback);
    echo "  ✅ isActivityExecution: " . ($isActivity ? 'YES' : 'NO') . "\n";
    
    if ($isActivity) {
        // Extract activity type
        $activityType = str_replace('_confirm', '', $callback);
        echo "  📝 Activity type: $activityType\n";
        
        // Check if activity type exists in activityTypes array
        $reflection = new ReflectionClass($tabelHandler);
        $property = $reflection->getProperty('activityTypes');
        $property->setAccessible(true);
        $activityTypes = $property->getValue($tabelHandler);
        
        $hasActivityType = array_key_exists($activityType, $activityTypes);
        echo "  🎯 Activity type registered: " . ($hasActivityType ? 'YES' : 'NO') . "\n";
        
        if ($hasActivityType) {
            echo "  📋 Activity name: " . $activityTypes[$activityType] . "\n";
        }
    }
    
    echo "\n";
}

echo "🔬 STEP 3: DETAILED HANDLER ANALYSIS\n";
echo "-----------------------------------\n";

// Read the TabelHandler file to analyze handler implementations
$handlerFile = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Find warranty equipment handlers
$warrantyHandlers = [
    'handleWarrantyElectricalEquipment',
    'handleWarrantyPumpEquipment'
];

$workingHandlers = [
    'handleElectricalEquipment', 
    'handlePumpEquipment'
];

foreach ($warrantyHandlers as $handler) {
    if (strpos($handlerFile, "function $handler") !== false) {
        echo "✅ Found warranty handler: $handler\n";
        
        // Extract handler content
        $start = strpos($handlerFile, "function $handler");
        $end = strpos($handlerFile, "\n    }", $start) + 5;
        $handlerContent = substr($handlerFile, $start, $end - $start);
        
        // Analyze handler content
        $lines = explode("\n", $handlerContent);
        echo "  📏 Handler lines: " . count($lines) . "\n";
        echo "  🎯 Contains project_code: " . (strpos($handlerContent, 'project_code') !== false ? 'YES' : 'NO') . "\n";
        echo "  📝 Contains tabel_data: " . (strpos($handlerContent, 'tabel_data') !== false ? 'YES' : 'NO') . "\n";
        echo "  ✅ Contains confirmation: " . (strpos($handlerContent, '_confirm') !== false ? 'YES' : 'NO') . "\n";
        
    } else {
        echo "❌ Missing warranty handler: $handler\n";
    }
}

echo "\n🎯 STEP 4: EXECUTION PATH TRACE\n";
echo "------------------------------\n";

echo "Testing full execution path simulation:\n\n";

// Simulate warranty electrical equipment click
echo "1️⃣ SIMULATING: Warranty Electrical Equipment Button Click\n";
echo "Callback: category_Табель_РабГарантии_ЭлектрОб\n";

$callback1 = 'category_Табель_РабГарантии_ЭлектрОб';
$canHandle1 = $tabelHandler->canHandle($callback1);
echo "✅ Step 1 - canHandle: " . ($canHandle1 ? 'PASS' : 'FAIL') . "\n";

$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('extractCategoryFromCallback');
$method->setAccessible(true);
$category1 = $method->invoke($tabelHandler, $callback1);
echo "✅ Step 2 - Category extraction: $category1\n";

// Check handler routing
$shouldRoute = ($category1 === 'Табель_РабГарантии_ЭлектрОб');
echo "✅ Step 3 - Handler routing: " . ($shouldRoute ? 'PASS' : 'FAIL') . "\n";

echo "\n2️⃣ SIMULATING: Warranty Electrical Equipment Confirmation\n";
echo "Callback: РабГарантии_ЭлектрОб_confirm\n";

$callback2 = 'РабГарантии_ЭлектрОб_confirm';
// Test isActivityExecution using reflection
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('isActivityExecution');
$method->setAccessible(true);
$isActivity2 = $method->invoke($tabelHandler, $callback2);
echo "✅ Step 1 - isActivityExecution: " . ($isActivity2 ? 'PASS' : 'FAIL') . "\n";

$activityType2 = str_replace('_confirm', '', $callback2);
echo "✅ Step 2 - Activity type: $activityType2\n";

$property = $reflection->getProperty('activityTypes');
$property->setAccessible(true);
$activityTypes = $property->getValue($tabelHandler);
$hasType2 = array_key_exists($activityType2, $activityTypes);
echo "✅ Step 3 - Activity registered: " . ($hasType2 ? 'PASS' : 'FAIL') . "\n";

if ($hasType2) {
    echo "✅ Step 4 - Activity name: " . $activityTypes[$activityType2] . "\n";
}

echo "\n🎯 FINAL ANALYSIS\n";
echo "================\n";

if ($canHandle1 && $shouldRoute && $isActivity2 && $hasType2) {
    echo "✅ ALL CHECKS PASSED - Warranty equipment should work!\n\n";
    echo "🔍 POSSIBLE ISSUES TO INVESTIGATE:\n";
    echo "1. Database/conversation context issues\n";
    echo "2. Missing error handling in actual execution\n";  
    echo "3. Telegram API response issues\n";
    echo "4. Report generation logic problems\n";
    echo "5. Missing required conversation notes\n";
} else {
    echo "❌ SOME CHECKS FAILED - Issues found:\n";
    if (!$canHandle1) echo "- canHandle method not recognizing warranty callbacks\n";
    if (!$shouldRoute) echo "- Handler routing not working for warranty equipment\n";
    if (!$isActivity2) echo "- Activity execution not recognized\n";
    if (!$hasType2) echo "- Activity type not registered\n";
}

echo "\n💡 RECOMMENDATION: Add debug logging to actual handlers to trace execution\n";