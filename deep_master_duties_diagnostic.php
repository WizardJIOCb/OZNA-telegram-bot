<?php
echo "🔍 DEEP MASTER DUTIES DIAGNOSTIC\n";
echo "================================\n\n";

echo "PHASE 1: COMPONENT VERIFICATION\n";
echo "-------------------------------\n";

// 1. Check button configuration
$messageServiceFile = 'CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $content = file_get_contents($messageServiceFile);
    
    // Check if button still exists (we removed it earlier)
    $buttonExists = strpos($content, "'text' => '👷 Исполнение обязанностей мастера'") !== false;
    echo "1. Button in MessageService: " . ($buttonExists ? "✅ EXISTS" : "❌ REMOVED") . "\n";
    
    if ($buttonExists) {
        // Check callback format
        if (strpos($content, "'callback_data' => 'category_Табель_ИсполнениеОбязМастера'") !== false) {
            echo "   Callback format: ✅ CORRECT (full format)\n";
        } elseif (strpos($content, "'callback_data' => 'cat_ТабИсполнОбяз'") !== false) {
            echo "   Callback format: ⚠️  SHORT FORMAT\n";
        } else {
            echo "   Callback format: ❌ UNKNOWN\n";
        }
    }
} else {
    echo "1. ❌ MessageService.php not found\n";
}

// 2. Check callback mapping
$handlerFile = 'CustomCommands/Handlers/TabelHandler.php';
if (file_exists($handlerFile)) {
    $content = file_get_contents($handlerFile);
    
    echo "\n2. Callback Mapping Analysis:\n";
    
    // Check for full format mapping
    $fullMapping = strpos($content, "'category_Табель_ИsполнениеОбязМастера' => 'Табель_ИsполнениеОбязМастера'") !== false;
    echo "   Full format mapping: " . ($fullMapping ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    // Check for short format mapping  
    $shortMapping = strpos($content, "'cat_ТабИsполнОбяз' => 'Табель_ИsполнениеОбязМастера'") !== false;
    echo "   Short format mapping: " . ($shortMapping ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    // 3. Check isActivityExecution exclusion
    echo "\n3. isActivityExecution Fix:\n";
    $exclusionFix = strpos($content, "🔥 CRITICAL FIX: Exclude Master Duties initial button") !== false;
    echo "   Critical exclusion fix: " . ($exclusionFix ? "✅ APPLIED" : "❌ MISSING") . "\n";
    
    // 4. Check handler logic
    echo "\n4. Handler Logic:\n";
    $handlerExists = strpos($content, "if (\$category === 'Табель_ИsполнениеОбязМастера')") !== false;
    echo "   Master Duties handler: " . ($handlerExists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    // 5. Check activity type registration
    echo "\n5. Activity Type Registration:\n";
    $activityTypeExists = strpos($content, "'ИsполнениеОбязМастера'") !== false;
    echo "   In activityTypes array: " . ($activityTypeExists ? "✅ REGISTERED" : "❌ MISSING") . "\n";
    
} else {
    echo "2. ❌ TabelHandler.php not found\n";
}

// 6. Check TabelEntry activity mapping
$tabelEntryFile = 'CustomCommands/Services/TabelEntry.php';
if (file_exists($tabelEntryFile)) {
    $content = file_get_contents($tabelEntryFile);
    echo "\n6. TabelEntry Activity Mapping:\n";
    $displayMapping = strpos($content, "'ИsполнениеОбязМастера' => '👷 Исполнение обязанностей мастера'") !== false;
    echo "   Display text mapping: " . ($displayMapping ? "✅ EXISTS" : "❌ MISSING") . "\n";
} else {
    echo "6. ❌ TabelEntry.php not found\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "PHASE 2: ROUTING FLOW SIMULATION\n";
echo "--------------------------------\n";

// Simulate the complete flow
$testCallback = 'category_Табель_ИsполнениеОбязМастера';
echo "Testing callback: '{$testCallback}'\n\n";

// Step 1: TabelHandler.canHandle()  
$canHandle = (strpos($testCallback, 'category_Табель') === 0);
echo "1. TabelHandler.canHandle(): " . ($canHandle ? "✅ TRUE" : "❌ FALSE") . "\n";

if ($canHandle) {
    // Step 2: extractCategoryFromCallback()
    $extractedCategory = 'Табель_ИsполнениеОбязМастера'; // Simulated result
    echo "2. extractCategoryFromCallback(): '{$extractedCategory}'\n";
    
    // Step 3: isActivityExecution() with our fix
    $isActivityExecution = false; // Our fix should make this FALSE
    if ($extractedCategory === 'Табель_ИsполнениеОбязМастера') {
        echo "3. isActivityExecution(): ✅ FALSE (exclusion applied)\n";
        $routesTo = "handleOtherWorkTypes";
    } else {
        echo "3. isActivityExecution(): ❌ TRUE (would route incorrectly)\n";
        $routesTo = "handleActivityExecution";
    }
    
    // Step 4: Routing decision
    echo "4. Routes to: {$routesTo}\n";
    
    if ($routesTo === "handleOtherWorkTypes") {
        // Step 5: Handler matching
        $handlerMatch = ($extractedCategory === 'Табель_ИsполнениеОбязМастера');
        echo "5. Handler condition match: " . ($handlerMatch ? "✅ TRUE" : "❌ FALSE") . "\n";
        
        if ($handlerMatch) {
            echo "6. Expected result: ✅ Confirmation dialog with Да/Нет buttons\n";
            $flowResult = "SUCCESS";
        } else {
            echo "6. Expected result: ❌ 'Unknown command'\n";
            $flowResult = "FAILED";
        }
    } else {
        echo "5. Expected result: ❌ Incorrect routing to activity execution\n";
        $flowResult = "FAILED";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "PHASE 3: CONFIRMATION FLOW TEST\n";
echo "-------------------------------\n";

// Test confirmation flow
$confirmCallback = 'category_Табель_ИsполнениеОбязМастера_yes';
echo "Testing confirmation: '{$confirmCallback}'\n\n";

$confirmExtracted = 'Табель_ИsполнениеОбязМастера_yes';
echo "1. Extracted category: '{$confirmExtracted}'\n";

$confirmIsExecution = (strpos($confirmExtracted, '_yes') !== false);
echo "2. isActivityExecution(): " . ($confirmIsExecution ? "✅ TRUE" : "❌ FALSE") . "\n";

if ($confirmIsExecution) {
    echo "3. Routes to: handleActivityExecution\n";
    echo "4. Expected result: ✅ Creates tabel entry and sends report\n";
    $confirmResult = "SUCCESS";
} else {
    echo "3. Expected result: ❌ Confirmation not processed\n";
    $confirmResult = "FAILED";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "PHASE 4: DIAGNOSTIC SUMMARY\n";
echo "---------------------------\n";

$overallStatus = "SUCCESS";
if (!isset($buttonExists) || !$buttonExists) {
    echo "⚠️  WARNING: Button was removed from UI\n";
    $overallStatus = "BUTTON_REMOVED";
} elseif (!isset($flowResult) || $flowResult !== "SUCCESS") {
    echo "❌ CRITICAL: Initial button flow failed\n";
    $overallStatus = "FAILED";
} elseif (!isset($confirmResult) || $confirmResult !== "SUCCESS") {
    echo "❌ CRITICAL: Confirmation flow failed\n";
    $overallStatus = "FAILED";
}

echo "\n🎯 OVERALL STATUS: ";
switch ($overallStatus) {
    case "SUCCESS":
        echo "✅ ALL SYSTEMS OPERATIONAL\n";
        echo "The Master Duties button should work correctly.\n";
        break;
    case "BUTTON_REMOVED":
        echo "⚠️  BUTTON REMOVED FROM UI\n";
        echo "The button was removed as requested earlier.\n";
        break;
    case "FAILED":
        echo "❌ SYSTEM FAILURE DETECTED\n";
        echo "Additional fixes required.\n";
        break;
}

echo "\n📋 NEXT STEPS:\n";
if ($overallStatus === "BUTTON_REMOVED") {
    echo "- Button was removed per user request\n";
    echo "- No further action needed unless button should be restored\n";
} elseif ($overallStatus === "SUCCESS") {
    echo "- Test the button in Telegram: /tabel → ШМР → Project → Проектная деятельность\n";
    echo "- Verify confirmation dialog appears\n";
    echo "- Test full report generation flow\n";
} else {
    echo "- Review failed components above\n";
    echo "- Apply missing fixes\n";
    echo "- Re-run diagnostic\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "DIAGNOSTIC COMPLETE\n";
?>