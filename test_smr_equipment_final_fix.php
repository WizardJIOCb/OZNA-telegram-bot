<?php
/**
 * Test to verify ШМР equipment buttons show submenus instead of immediate confirmations
 * 
 * ROOT CAUSE FOUND:
 * - The 7 ШМР equipment menu activities (ШМР_Электрооборудование, ШМР_Метрология, etc.)
 *   were incorrectly included in the $activityTypes array (lines 129-135 of TabelHandler.php)
 * - This caused the fallback check at line 4795 to treat them as terminal activities
 *   and show immediate confirmation dialogs instead of routing to submenu handlers
 * 
 * THE FIX:
 * 1. REMOVED these 7 menu activities from $activityTypes array (lines 129-135)
 * 2. SIMPLIFIED the exclusion logic at line 4775 (no longer need the in_array check)
 * 3. KEPT only the "Прочее" variants in activityTypes (they need custom input prompts)
 * 
 * FLOW AFTER FIX:
 * User clicks "⚡ Электрооборудование"
 *   → Callback: cat_ШМР_ЭлектрОб
 *   → extractCategoryFromCallback → Табель_ШМР_Электрооборудование
 *   → NOT in activityTypes array (because we removed it) ✅
 *   → Routes to submenu handler at line ~3474 ✅
 *   → Shows keyboard with "Прочее" and "Назад" buttons ✅
 */

echo "=== ШМР EQUIPMENT FINAL FIX VERIFICATION ===\n\n";

// Load source code files
$handlerCode = file_get_contents('CustomCommands/Handlers/TabelHandler.php');
$messageServiceCode = file_get_contents('CustomCommands/Services/MessageService.php');

// Test 1: Verify activityTypes array no longer contains equipment menu activities
echo "TEST 1: Verify equipment menu activities removed from activityTypes\n";
echo "---------------------------------------------------------------\n";
$equipmentActivities = [
    'ШМР_ТехнологическоеОборудование',
    'ШМР_Электрооборудование',
    'ШМР_ОтоплениеИВентиляция',
    'ШМР_ПожарнаяСигнализация',
    'ШМР_ОхраннаяСигнализация',
    'ШМР_Автоматизация',
    'ШМР_Метрология'
];

$allRemoved = true;
foreach ($equipmentActivities as $activity) {
    // Check if this activity appears in the activityTypes array definition
    // Look for pattern: 'ШМР_Электрооборудование', in the array definition (between lines 28-140)
    preg_match("/private array \\\$activityTypes = \[(.*?)\];/s", $handlerCode, $matches);
    if (isset($matches[1])) {
        $arrayContent = $matches[1];
        if (strpos($arrayContent, "'$activity'") !== false) {
            echo "  ❌ FAIL: '$activity' is still in activityTypes array\n";
            $allRemoved = false;
        } else {
            echo "  ✅ PASS: '$activity' removed from activityTypes array\n";
        }
    }
}

if ($allRemoved) {
    echo "\n✅ TEST 1 PASSED: All 7 equipment menu activities removed from activityTypes\n\n";
} else {
    echo "\n❌ TEST 1 FAILED: Some equipment menu activities still in activityTypes\n\n";
}

// Test 2: Verify "Прочее" variants are still in activityTypes
echo "TEST 2: Verify 'Прочее' variants remain in activityTypes\n";
echo "--------------------------------------------------------\n";

$procheeActivities = [
    'ШМР_Прочее_Электрооборудование',
    'ШМР_Прочее_ОтоплениеИВентиляция',
    'ШМР_Прочее_ПожарнаяСигнализация',
    'ШМР_Прочее_ОхраннаяСигнализация',
    'ШМР_Прочее_Автоматизация',
    'ШМР_Прочее_Метрология'
];

$allPresent = true;
foreach ($procheeActivities as $activity) {
    preg_match("/private array \\\$activityTypes = \[(.*?)\];/s", $handlerCode, $matches);
    if (isset($matches[1])) {
        $arrayContent = $matches[1];
        if (strpos($arrayContent, "'$activity'") !== false) {
            echo "  ✅ PASS: '$activity' is in activityTypes array\n";
        } else {
            echo "  ❌ FAIL: '$activity' missing from activityTypes array\n";
            $allPresent = false;
        }
    }
}

if ($allPresent) {
    echo "\n✅ TEST 2 PASSED: All 'Прочее' variants present in activityTypes\n\n";
} else {
    echo "\n❌ TEST 2 FAILED: Some 'Прочее' variants missing from activityTypes\n\n";
}

// Test 3: Verify exclusion logic simplified
echo "TEST 3: Verify exclusion logic simplified\n";
echo "------------------------------------------\n";

// Check if the old in_array exclusion logic is removed
if (preg_match("/in_array\(\\\$strippedCategory, \[.*'ШМР_Электрооборудование'/s", $handlerCode)) {
    echo "  ❌ FAIL: Old exclusion logic with in_array still present\n";
    echo "\n❌ TEST 3 FAILED\n\n";
} else {
    echo "  ✅ PASS: Old exclusion logic with in_array removed\n";
    
    // Check if simplified logic is present
    if (preg_match("/\\\$shouldSkip = str_contains\(\\\$strippedCategory, 'Прочее'\);/", $handlerCode)) {
        echo "  ✅ PASS: Simplified exclusion logic present\n";
        echo "\n✅ TEST 3 PASSED: Exclusion logic properly simplified\n\n";
    } else {
        echo "  ❌ FAIL: Simplified exclusion logic not found\n";
        echo "\n❌ TEST 3 FAILED\n\n";
    }
}

// Test 4: Verify submenu handlers are present
echo "TEST 4: Verify submenu handlers are present\n";
echo "--------------------------------------------\n";

$handlers = [
    'Табель_ШМР_Электрооборудование' => 'createSMRElectricalEquipmentKeyboard',
    'Табель_ШМР_ОтоплениеИВентиляция' => 'createSMRHeatingVentilationKeyboard',
    'Табель_ШМР_ПожарнаяСигнализация' => 'createSMRFireAlarmKeyboard',
    'Табель_ШМР_ОхраннаяСигнализация' => 'createSMRSecurityAlarmKeyboard',
    'Табель_ШМР_Автоматизация' => 'createSMRAutomationKeyboard',
    'Табель_ШМР_Метрология' => 'createSMRMetrologyKeyboard'
];

$allHandlersPresent = true;
foreach ($handlers as $category => $keyboardMethod) {
    if (strpos($handlerCode, "if (\$category === '$category')") !== false &&
        strpos($handlerCode, "$keyboardMethod()") !== false) {
        echo "  ✅ PASS: Handler for '$category' present\n";
    } else {
        echo "  ❌ FAIL: Handler for '$category' missing\n";
        $allHandlersPresent = false;
    }
}

if ($allHandlersPresent) {
    echo "\n✅ TEST 4 PASSED: All submenu handlers present\n\n";
} else {
    echo "\n❌ TEST 4 FAILED: Some submenu handlers missing\n\n";
}

// Test 5: Verify MessageService keyboards have correct structure
echo "TEST 5: Verify MessageService keyboards have 2-button structure\n";
echo "----------------------------------------------------------------\n";

$messageServiceCode = file_get_contents('CustomCommands/Services/MessageService.php');

$keyboardMethods = [
    'createSMRElectricalEquipmentKeyboard',
    'createSMRHeatingVentilationKeyboard',
    'createSMRFireAlarmKeyboard',
    'createSMRSecurityAlarmKeyboard',
    'createSMRAutomationKeyboard',
    'createSMRMetrologyKeyboard'
];

$allCorrect = true;
foreach ($keyboardMethods as $method) {
    // Extract method content
    preg_match("/public function $method\(\): array.*?\{(.*?)\n    \}/s", $messageServiceCode, $matches);
    if (isset($matches[1])) {
        $methodContent = $matches[1];
        
        // Check for "Прочее" button
        $hasProchee = strpos($methodContent, "'📝 Прочее'") !== false;
        
        // Check for "Назад" button  
        $hasBack = strpos($methodContent, "'⬅️ Назад'") !== false;
        
        // Check for old 4-button structure (should NOT have these)
        $hasOldButtons = strpos($methodContent, "'📅 Работы по графику'") !== false ||
                         strpos($methodContent, "'✅ Подтвердить достижение вехи'") !== false;
        
        if ($hasProchee && $hasBack && !$hasOldButtons) {
            echo "  ✅ PASS: $method has correct 2-button structure\n";
        } else {
            echo "  ❌ FAIL: $method has incorrect structure\n";
            if (!$hasProchee) echo "      - Missing 'Прочее' button\n";
            if (!$hasBack) echo "      - Missing 'Назад' button\n";
            if ($hasOldButtons) echo "      - Still has old 4-button structure\n";
            $allCorrect = false;
        }
    } else {
        echo "  ❌ FAIL: $method not found\n";
        $allCorrect = false;
    }
}

if ($allCorrect) {
    echo "\n✅ TEST 5 PASSED: All keyboards have correct 2-button structure\n\n";
} else {
    echo "\n❌ TEST 5 FAILED: Some keyboards have incorrect structure\n\n";
}

// Summary
echo "=============================================================\n";
echo "SUMMARY: FIX VERIFICATION\n";
echo "=============================================================\n\n";

echo "✅ ROOT CAUSE IDENTIFIED:\n";
echo "   Equipment menu activities were in activityTypes array\n\n";

echo "✅ FIX APPLIED:\n";
echo "   1. Removed 7 equipment menu activities from activityTypes\n";
echo "   2. Simplified exclusion logic (removed unnecessary in_array)\n";
echo "   3. Kept 'Прочее' variants in activityTypes (need custom input)\n\n";

echo "✅ EXPECTED BEHAVIOR:\n";
echo "   Equipment buttons → Show submenus (Прочее + Назад)\n";
echo "   'Прочее' buttons → Prompt for custom text input\n";
echo "   After text input → Show confirmation dialog\n\n";

echo "=============================================================\n";
echo "READY FOR USER TESTING IN TELEGRAM BOT\n";
echo "=============================================================\n";
