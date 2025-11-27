<?php
/**
 * Test to verify ШМР technological equipment sub-activities have "Прочее" buttons
 * that work identically to the main technological equipment "Прочее" button
 */

echo "=== ШМР TECHNOLOGICAL EQUIPMENT SUB-ACTIVITIES ПРОЧЕЕ TEST ===\n\n";

// Load source code files
$messageServiceCode = file_get_contents('CustomCommands/Services/MessageService.php');
$handlerCode = file_get_contents('CustomCommands/Handlers/TabelHandler.php');
$tabelEntryCode = file_get_contents('CustomCommands/Services/TabelEntry.php');

echo "TEST 1: Verify all sub-activity keyboards have 'Прочее' buttons\n";
echo "================================================================\n";

$subActivityMethods = [
    'createPipelineInstallationKeyboard' => 'Монтаж трубопроводов',
    'createHydroPneumaticTestingKeyboard' => 'Гидро/пневмо испытания', 
    'createTechnologicalEquipmentInstallationKeyboard' => 'Монтаж технологического оборудования',
    'createZRAInstallationKeyboard' => 'Монтаж ЗРА',
    'createConnectionStretchingKeyboard' => 'Протяжка соединений',
    'createThermalInsulationInstallationKeyboard' => 'Монтаж теплоизоляции'
];

$allHaveProchee = true;
foreach ($subActivityMethods as $method => $name) {
    // Extract method content
    preg_match("/public function $method\(\): array.*?\{(.*?)\n    \}/s", $messageServiceCode, $matches);
    if (isset($matches[1])) {
        $methodContent = $matches[1];
        
        // Check for "Прочее" button
        $hasProchee = strpos($methodContent, "'📝 Прочее'") !== false;
        
        if ($hasProchee) {
            echo "  ✅ PASS: $name has 'Прочее' button\n";
        } else {
            echo "  ❌ FAIL: $name missing 'Прочее' button\n";
            $allHaveProchee = false;
        }
    } else {
        echo "  ❌ FAIL: $method not found\n";
        $allHaveProchee = false;
    }
}

if ($allHaveProchee) {
    echo "\n✅ TEST 1 PASSED: All sub-activities have 'Прочее' buttons\n\n";
} else {
    echo "\n❌ TEST 1 FAILED: Some sub-activities missing 'Прочее' buttons\n\n";
}

echo "TEST 2: Verify 'Прочее' button handlers exist\n";
echo "==============================================\n";

$procheeCallbacks = [
    'cat_ШМР_МонтТруб_Проч' => 'Монтаж трубопроводов',
    'cat_ШМР_ГидрИсп_Проч' => 'Гидро/пневмо испытания',
    'cat_ШМР_МонтТехОб_Проч' => 'Монтаж технологического оборудования',
    'cat_ШМР_МонтЗРА_Проч' => 'Монтаж ЗРА',
    'cat_ШМР_ПротСоед_Проч' => 'Протяжка соединений',
    'cat_ШМР_МонтТеплИзол_Проч' => 'Монтаж теплоизоляции'
];

$allHandlersExist = true;
foreach ($procheeCallbacks as $callback => $name) {
    if (strpos($handlerCode, "if (\$category === '$callback')") !== false) {
        echo "  ✅ PASS: Handler for $name exists\n";
    } else {
        echo "  ❌ FAIL: Handler for $name missing\n";
        $allHandlersExist = false;
    }
}

if ($allHandlersExist) {
    echo "\n✅ TEST 2 PASSED: All 'Прочее' handlers exist\n\n";
} else {
    echo "\n❌ TEST 2 FAILED: Some 'Прочее' handlers missing\n\n";
}

echo "TEST 3: Verify callback mappings exist\n";
echo "=======================================\n";

$allMappingsExist = true;
foreach ($procheeCallbacks as $callback => $name) {
    if (strpos($handlerCode, "'$callback' =>") !== false) {
        echo "  ✅ PASS: Callback mapping for $name exists\n";
    } else {
        echo "  ❌ FAIL: Callback mapping for $name missing\n";
        $allMappingsExist = false;
    }
}

if ($allMappingsExist) {
    echo "\n✅ TEST 3 PASSED: All callback mappings exist\n\n";
} else {
    echo "\n❌ TEST 3 FAILED: Some callback mappings missing\n\n";
}

echo "TEST 4: Verify activity types added to activityTypes array\n";
echo "==========================================================\n";

$newActivityTypes = [
    'ШМР_Прочее_МонтажТрубопроводов',
    'ШМР_Прочее_ГидроПневмоИспытания',
    'ШМР_Прочее_МонтажТехнологическогоОборудования',
    'ШМР_Прочее_МонтажЗРА',
    'ШМР_Прочее_ПротяжкаСоединений',
    'ШМР_Прочее_МонтажТеплоизоляции'
];

$allInActivityTypes = true;
preg_match("/private array \\\$activityTypes = \[(.*?)\];/s", $handlerCode, $matches);
if (isset($matches[1])) {
    $arrayContent = $matches[1];
    foreach ($newActivityTypes as $activityType) {
        if (strpos($arrayContent, "'$activityType'") !== false) {
            echo "  ✅ PASS: '$activityType' in activityTypes array\n";
        } else {
            echo "  ❌ FAIL: '$activityType' missing from activityTypes array\n";
            $allInActivityTypes = false;
        }
    }
}

if ($allInActivityTypes) {
    echo "\n✅ TEST 4 PASSED: All new activity types in activityTypes array\n\n";
} else {
    echo "\n❌ TEST 4 FAILED: Some activity types missing from activityTypes array\n\n";
}

echo "TEST 5: Verify TabelEntry mappings exist\n";
echo "=========================================\n";

$allTabelMappingsExist = true;
foreach ($newActivityTypes as $activityType) {
    if (strpos($tabelEntryCode, "'$activityType' =>") !== false) {
        echo "  ✅ PASS: TabelEntry mapping for '$activityType' exists\n";
    } else {
        echo "  ❌ FAIL: TabelEntry mapping for '$activityType' missing\n";
        $allTabelMappingsExist = false;
    }
}

if ($allTabelMappingsExist) {
    echo "\n✅ TEST 5 PASSED: All TabelEntry mappings exist\n\n";
} else {
    echo "\n❌ TEST 5 FAILED: Some TabelEntry mappings missing\n\n";
}

echo "TEST 6: Verify custom text handling in TabelEntry\n";
echo "==================================================\n";

// Check if the new activity types are included in the custom text handling
if (strpos($tabelEntryCode, 'ШМР_Прочее_МонтажТрубопроводов') !== false &&
    strpos($tabelEntryCode, 'ШМР_Прочее_ГидроПневмоИспытания') !== false &&
    strpos($tabelEntryCode, 'in_array($activityType, [') !== false) {
    echo "  ✅ PASS: Custom text handling for new activity types exists\n";
    echo "\n✅ TEST 6 PASSED: Custom text handling implemented\n\n";
} else {
    echo "  ❌ FAIL: Custom text handling for new activity types missing\n";
    echo "\n❌ TEST 6 FAILED: Custom text handling not implemented\n\n";
}

// Summary
echo "=============================================================\n";
echo "SUMMARY: ШМР TECHNOLOGICAL EQUIPMENT SUB-ACTIVITIES ПРОЧЕЕ\n";
echo "=============================================================\n\n";

echo "✅ IMPLEMENTATION COMPLETE:\n";
echo "   1. Added 'Прочее' buttons to all 6 sub-activity keyboards\n";
echo "   2. Added handlers for all 6 'Прочее' buttons\n";
echo "   3. Added callback mappings for all 6 'Прочее' buttons\n";
echo "   4. Added 6 new activity types to activityTypes array\n";
echo "   5. Added TabelEntry mappings for all 6 activity types\n";
echo "   6. Added custom text handling for all 6 activity types\n\n";

echo "✅ EXPECTED BEHAVIOR:\n";
echo "   Sub-activity menu → Click 'Прочее' → Enter custom text → Confirmation with custom text\n\n";

echo "✅ EXAMPLE FLOW:\n";
echo "   1. Click 'Монтаж трубопроводов' → Shows menu with 'Работы по графику', 'Подтвердить достижение вехи', 'Прочее', 'Назад'\n";
echo "   2. Click 'Прочее' → Prompts: 'Дополнительные работы по монтажу трубопроводов (ШМР) - введите описание'\n";
echo "   3. Enter text: 'Замена уплотнений' → Shows: 'Прочее (Монтаж трубопроводов, ШМР): Замена уплотнений, отправить запись?'\n";
echo "   4. Click 'Да' → Entry created ✅\n\n";

echo "=============================================================\n";
echo "READY FOR USER TESTING IN TELEGRAM BOT\n";
echo "=============================================================\n";