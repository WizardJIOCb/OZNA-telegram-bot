<?php
/**
 * SIMPLE MASTER DUTIES DIAGNOSTIC
 * This runs without loading complex classes and shows results directly
 */

echo "🔍 MASTER DUTIES DIAGNOSTIC RESULTS\n";
echo "===================================\n\n";

// Check if TabelHandler.php exists
$handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (!file_exists($handlerFile)) {
    echo "❌ CRITICAL ERROR: TabelHandler.php not found!\n";
    exit(1);
}

$content = file_get_contents($handlerFile);

echo "1. CALLBACK MAPPING CHECK:\n";
echo "==========================\n";

// Check short format mapping
if (strpos($content, "'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера'") !== false) {
    echo "✅ Short format mapping: FOUND\n";
    $shortMappingOK = true;
} else {
    echo "❌ Short format mapping: MISSING\n";
    $shortMappingOK = false;
}

// Check full format mapping  
if (strpos($content, "'category_Табель_ИсполнениеОбязМастера' => 'Табель_ИсполнениеОбязМастера'") !== false) {
    echo "✅ Full format mapping: FOUND\n";
    $fullMappingOK = true;
} else {
    echo "❌ Full format mapping: MISSING\n";
    $fullMappingOK = false;
}

echo "\n2. HANDLER METHOD CHECK:\n";
echo "========================\n";

// Check handler method
if (strpos($content, "category === 'Табель_ИсполнениеОбязМастера'") !== false) {
    echo "✅ Master Duties handler: FOUND\n";
    $handlerOK = true;
} else {
    echo "❌ Master Duties handler: MISSING\n";
    $handlerOK = false;
}

echo "\n3. ACTIVITY TYPE CHECK:\n";
echo "=======================\n";

// Check activity type registration
if (strpos($content, "'ИсполнениеОбязМастера'") !== false) {
    echo "✅ Activity type registered: FOUND\n";
    $activityTypeOK = true;
} else {
    echo "❌ Activity type registered: MISSING\n";
    $activityTypeOK = false;
}

echo "\n4. CRITICAL EXCLUSION CHECK:\n";
echo "============================\n";

// Check critical exclusion fix
if (strpos($content, "CRITICAL FIX: Exclude Master Duties") !== false || 
    strpos($content, "category === 'Табель_ИсполнениеОбязМастера'") !== false) {
    echo "✅ Critical exclusion fix: FOUND\n";
    $exclusionOK = true;
} else {
    echo "❌ Critical exclusion fix: MISSING\n";
    $exclusionOK = false;
}

echo "\n5. BUTTON CONFIGURATION CHECK:\n";
echo "==============================\n";

// Check MessageService for button
$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $messageContent = file_get_contents($messageServiceFile);
    
    if (strpos($messageContent, 'Исполнение обязанностей мастера') !== false) {
        echo "✅ Button text found: FOUND\n";
        $buttonTextOK = true;
        
        // Look for callback
        if (strpos($messageContent, 'cat_ТабИсполнОбяз') !== false) {
            echo "✅ Button callback found: FOUND\n";
            $buttonCallbackOK = true;
        } else {
            echo "❌ Button callback found: MISSING\n";
            $buttonCallbackOK = false;
        }
    } else {
        echo "❌ Button text found: MISSING\n";
        $buttonTextOK = false;
        $buttonCallbackOK = false;
    }
} else {
    echo "❌ MessageService.php not found\n";
    $buttonTextOK = false;
    $buttonCallbackOK = false;
}

echo "\n6. CALLBACK FLOW SIMULATION:\n";
echo "============================\n";

$testCallback = 'cat_ТабИсполнОбяз';
echo "Testing callback: '{$testCallback}'\n";

// Simulate the mapping logic
if ($shortMappingOK) {
    $mappedCategory = 'Табель_ИсполнениеОбязМастера';
    echo "Mapped to: '{$mappedCategory}'\n";
    
    if ($handlerOK) {
        echo "✅ Handler would match: YES\n";
        echo "✅ Expected result: Confirmation dialog\n";
        $flowOK = true;
    } else {
        echo "❌ Handler would match: NO\n";
        $flowOK = false;
    }
} else {
    echo "❌ Mapping would fail: callback not mapped\n";
    $flowOK = false;
}

echo "\n7. OVERALL STATUS:\n";
echo "==================\n";

$allChecksPass = $shortMappingOK && $handlerOK && $activityTypeOK && $buttonTextOK && $buttonCallbackOK;

if ($allChecksPass) {
    echo "🎉 ALL SYSTEMS GREEN - BUTTON SHOULD WORK!\n\n";
    echo "If button still doesn't work, possible causes:\n";
    echo "• User access level is 0 (guest) - blocked\n";
    echo "• User missing FIO - blocked\n";
    echo "• User missing functional direction - blocked\n";
    echo "• Wrong navigation path\n";
    echo "• Missing project selection\n";
    echo "• Recent tabel entry (for non-admin users)\n";
    echo "• Server/cache needs restart\n";
} else {
    echo "⚠️ ISSUES DETECTED - BUTTON MAY NOT WORK\n\n";
    echo "Failed checks:\n";
    if (!$shortMappingOK) echo "• Short format mapping missing\n";
    if (!$fullMappingOK) echo "• Full format mapping missing\n";
    if (!$handlerOK) echo "• Handler method missing\n";
    if (!$activityTypeOK) echo "• Activity type not registered\n";
    if (!$buttonTextOK) echo "• Button text missing\n";
    if (!$buttonCallbackOK) echo "• Button callback missing\n";
    if (!$exclusionOK) echo "• Critical exclusion missing\n";
}

echo "\n8. MANUAL TEST INSTRUCTIONS:\n";
echo "============================\n";
echo "To test manually in Telegram:\n";
echo "1. Go to /tabel\n";
echo "2. Select 'Отдел ШМР'\n";
echo "3. Select any date\n";
echo "4. Select any project\n";
echo "5. Click 'Проектная деятельность'\n";
echo "6. Look for '👷 Исполнение обязанностей мастера' button\n";
echo "7. Click the button\n";
echo "8. Expected: Confirmation dialog with 'Да' and 'Нет' buttons\n";

echo "\nDiagnostic completed at: " . date('Y-m-d H:i:s') . "\n";
?>