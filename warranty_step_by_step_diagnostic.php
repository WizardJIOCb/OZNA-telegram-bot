<?php

echo "🔧 WARRANTY FUNCTIONALITY STEP-BY-STEP DIAGNOSTIC\n";
echo "=================================================\n\n";

// Include necessary files
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/MessageService.php';

echo "📋 STEP 1: Testing Warranty Button Recognition\n";
echo "---------------------------------------------\n";

$tabelHandler = new TabelHandler(
    new DatabaseService(),
    new MessageService(),
    new ProjectSelector(new DatabaseService()),
    new TabelEntry(new DatabaseService()),
    new AdminService(new DatabaseService())
);

// Test warranty button callback recognition
$warrantyCallbacks = [
    'cat_ТабГарантии',
    'Табель_РаботыПоГарантии', 
    'cat_ТабГарант',
    'category_Табель_Работы_по_гарантии'
];

foreach ($warrantyCallbacks as $callback) {
    $canHandle = $tabelHandler->canHandle($callback);
    echo "  ✓ Testing '{$callback}': " . ($canHandle ? "✅ RECOGNIZED" : "❌ NOT RECOGNIZED") . "\n";
}

echo "\n📋 STEP 2: Testing Warranty Equipment Menu Creation\n";
echo "-------------------------------------------------\n";

$messageService = new MessageService();

// Test warranty equipment keyboard creation
try {
    $keyboard = $messageService->createWarrantyEquipmentKeyboard();
    echo "  ✅ Warranty equipment keyboard created successfully\n";
    echo "  📊 Number of keyboard rows: " . count($keyboard) . "\n";
    
    // Display keyboard buttons
    foreach ($keyboard as $i => $row) {
        if (isset($row[0]['text']) && isset($row[0]['callback_data'])) {
            echo "  🔘 Button {$i}: {$row[0]['text']} -> {$row[0]['callback_data']}\n";
        }
    }
} catch (Exception $e) {
    echo "  ❌ Failed to create warranty equipment keyboard: " . $e->getMessage() . "\n";
}

echo "\n📋 STEP 3: Testing Warranty Equipment Callback Processing\n";
echo "--------------------------------------------------------\n";

// Test warranty equipment button callbacks
$warrantyEquipmentCallbacks = [
    'category_Табель_РабГарантии_ЭлектрОб',  // Electrical equipment
    'category_Табель_РабГарантии_НасОб',     // Pump equipment
    'cat_Гарантии_ЭлектрОб_yes'             // Electrical equipment confirmation
];

// Use reflection to test internal methods
$reflection = new ReflectionClass($tabelHandler);

foreach ($warrantyEquipmentCallbacks as $callback) {
    echo "  🔍 Testing callback: '{$callback}'\n";
    
    // Test canHandle
    $canHandle = $tabelHandler->canHandle($callback);
    echo "    - canHandle: " . ($canHandle ? "✅ YES" : "❌ NO") . "\n";
    
    // Test category extraction
    try {
        $method = $reflection->getMethod('extractCategoryFromCallback');
        $method->setAccessible(true);
        $category = $method->invoke($tabelHandler, $callback);
        echo "    - Extracted category: '{$category}'\n";
    } catch (Exception $e) {
        echo "    - Category extraction failed: " . $e->getMessage() . "\n";
    }
    
    // Test activity execution recognition
    if (str_contains($callback, '_yes')) {
        try {
            $method = $reflection->getMethod('isActivityExecution');
            $method->setAccessible(true);
            $isActivity = $method->invoke($tabelHandler, $callback);
            echo "    - isActivityExecution: " . ($isActivity ? "✅ YES" : "❌ NO") . "\n";
        } catch (Exception $e) {
            echo "    - isActivityExecution failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
}

echo "📋 STEP 4: Testing Activity Types Array\n";
echo "--------------------------------------\n";

// Check warranty activity types in the array
$property = $reflection->getProperty('activityTypes');
$property->setAccessible(true);
$activityTypes = $property->getValue($tabelHandler);

$warrantyActivityTypes = [
    'РабГарантии_ЭлектрОб',
    'РабГарантии_СрИзм',
    'РабГарантии_НасОб',
    'РабГарантии_ТрубАрм',
    'РабГарантии_ИспМех',
    'РабГарантии_КонтрОб',
    'РабГарантии_Прочее'
];

foreach ($warrantyActivityTypes as $activityType) {
    $exists = in_array($activityType, $activityTypes);
    echo "  🎯 Activity type '{$activityType}': " . ($exists ? "✅ REGISTERED" : "❌ MISSING") . "\n";
}

echo "\n🎯 DIAGNOSTIC SUMMARY\n";
echo "====================\n";

// Count issues
$issues = [];

// Check if main warranty button can be handled
if (!$tabelHandler->canHandle('cat_ТабГарантии')) {
    $issues[] = "Main warranty button not recognized";
}

// Check if warranty equipment keyboard can be created
try {
    $keyboard = $messageService->createWarrantyEquipmentKeyboard();
    if (empty($keyboard)) {
        $issues[] = "Warranty equipment keyboard is empty";
    }
} catch (Exception $e) {
    $issues[] = "Cannot create warranty equipment keyboard";
}

// Check warranty activity types
$missingTypes = [];
foreach ($warrantyActivityTypes as $activityType) {
    if (!in_array($activityType, $activityTypes)) {
        $missingTypes[] = $activityType;
    }
}
if (!empty($missingTypes)) {
    $issues[] = "Missing activity types: " . implode(', ', $missingTypes);
}

if (empty($issues)) {
    echo "✅ ALL BASIC COMPONENTS ARE WORKING\n";
    echo "\n💡 RECOMMENDATION:\n";
    echo "Since all basic components test successfully, the issue might be:\n";
    echo "1. The warranty button isn't appearing in your bot menu\n";
    echo "2. There's a server/webhook connectivity issue\n";
    echo "3. The debug logs aren't being written to the expected location\n";
    echo "\nNext steps:\n";
    echo "- Check if you can see the '🔧 Работы по гарантии' button in PNR department\n";
    echo "- Try clicking other buttons to see if the bot responds at all\n";
    echo "- Check if the server is running and receiving webhooks\n";
} else {
    echo "❌ ISSUES FOUND:\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". {$issue}\n";
    }
}

echo "\n📝 NEXT DIAGNOSTIC STEPS:\n";
echo "=========================\n";
echo "If all components test successfully but the button still doesn't work:\n";
echo "1. Test other bot buttons to confirm bot connectivity\n";
echo "2. Check webhook status and server logs\n";
echo "3. Verify the warranty button appears in your PNR department menu\n";
echo "4. Look for PHP error logs that might indicate runtime issues\n";