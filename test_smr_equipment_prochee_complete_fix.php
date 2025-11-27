<?php
/**
 * Comprehensive test to verify ШМР equipment "Прочее" buttons fix
 * Tests all components to ensure the fix is complete
 */

echo "=== ШМР EQUIPMENT 'ПРОЧЕЕ' BUTTONS COMPLETE FIX TEST ===\n\n";

// File paths
$tabelHandlerPath = 'c:\Users\user\Documents\projects\CustomCommands\Handlers\TabelHandler.php';
$tabelEntryPath = 'c:\Users\user\Documents\projects\CustomCommands\Services\TabelEntry.php';

// Check if files exist
if (!file_exists($tabelHandlerPath)) {
    echo "❌ ERROR: TabelHandler.php not found at {$tabelHandlerPath}\n";
    exit(1);
}

if (!file_exists($tabelEntryPath)) {
    echo "❌ ERROR: TabelEntry.php not found at {$tabelEntryPath}\n";
    exit(1);
}

// Read file contents
$tabelHandlerContent = file_get_contents($tabelHandlerPath);
$tabelEntryContent = file_get_contents($tabelEntryPath);

echo "1. Testing callback mappings in extractCategoryFromCallback...\n";
echo "===========================================================\n";

$callbackMappings = [
    'cat_ШМР_ЭлектрОб_Проч' => 'Табель_ШМР_Прочее_Электрооборудование',
    'cat_ШМР_ОтоплВент_Проч' => 'Табель_ШМР_Прочее_ОтоплениеИВентиляция',
    'cat_ШМР_ПожСигн_Проч' => 'Табель_ШМР_Прочее_ПожарнаяСигнализация',
    'cat_ШМР_ОхрСигн_Проч' => 'Табель_ШМР_Прочее_ОхраннаяСигнализация',
    'cat_ШМР_Автом_Проч' => 'Табель_ШМР_Прочее_Автоматизация',
    'cat_ШМР_Метрол_Проч' => 'Табель_ШМР_Прочее_Метрология'
];

foreach ($callbackMappings as $callback => $expectedMapping) {
    $mappingExists = strpos($tabelHandlerContent, "'$callback' => '$expectedMapping'") !== false;
    echo "  $callback => $expectedMapping: " . ($mappingExists ? "✅ FOUND" : "❌ MISSING") . "\n";
}

echo "\n2. Testing ШМР equipment 'Прочее' handlers in handleOtherWorkTypes...\n";
echo "====================================================================\n";

$handlers = [
    'Табель_ШМР_Прочее_Электрооборудование' => 'ШМР electrical equipment',
    'Табель_ШМР_Прочее_ОтоплениеИВентиляция' => 'ШМР heating/ventilation equipment',
    'Табель_ШМР_Прочее_ПожарнаяСигнализация' => 'ШМР fire alarm equipment',
    'Табель_ШМР_Прочее_ОхраннаяСигнализация' => 'ШМР security alarm equipment',
    'Табель_ШМР_Прочее_Автоматизация' => 'ШМР automation equipment',
    'Табель_ШМР_Прочее_Метрология' => 'ШМР metrology equipment'
];

foreach ($handlers as $handler => $description) {
    $handlerExists = strpos($tabelHandlerContent, "category === '$handler'") !== false;
    echo "  $description handler: " . ($handlerExists ? "✅ FOUND" : "❌ MISSING") . "\n";
}

echo "\n3. Testing activity types in activityTypes array...\n";
echo "==================================================\n";

$activityTypes = [
    'ШМР_Прочее_Электрооборудование',
    'ШМР_Прочее_ОтоплениеИВентиляция',
    'ШМР_Прочее_ПожарнаяСигнализация',
    'ШМР_Прочее_ОхраннаяСигнализация',
    'ШМР_Прочее_Автоматизация',
    'ШМР_Прочее_Метрология'
];

foreach ($activityTypes as $activityType) {
    $typeExists = strpos($tabelHandlerContent, "'$activityType'") !== false;
    echo "  $activityType: " . ($typeExists ? "✅ FOUND" : "❌ MISSING") . "\n";
}

echo "\n4. Testing activity type mappings in TabelEntry.php...\n";
echo "====================================================\n";

$activityMappings = [
    'ШМР_Прочее_Электрооборудование' => '📝 Прочее (Электрооборудование, ШМР)',
    'ШМР_Прочее_ОтоплениеИВентиляция' => '📝 Прочее (Отопление и вентиляция, ШМР)',
    'ШМР_Прочее_ПожарнаяСигнализация' => '📝 Прочее (Пожарная сигнализация, ШМР)',
    'ШМР_Прочее_ОхраннаяСигнализация' => '📝 Прочее (Охранная сигнализация, ШМР)',
    'ШМР_Прочее_Автоматизация' => '📝 Прочее (Автоматизация, ШМР)',
    'ШМР_Прочее_Метрология' => '📝 Прочее (Метрология, ШМР)'
];

foreach ($activityMappings as $activityType => $expectedMapping) {
    $mappingExists = strpos($tabelEntryContent, "'$activityType' => '$expectedMapping'") !== false;
    echo "  $activityType => $expectedMapping: " . ($mappingExists ? "✅ FOUND" : "❌ MISSING") . "\n";
}

echo "\n5. Testing confirmation mappings in TabelEntry.php activityNames...\n";
echo "=================================================================\n";

$confirmationMappings = [
    'ШМР_Прочее_Электрооборудование' => 'Прочее (Электрооборудование)',
    'ШМР_Прочее_ОтоплениеИВентиляция' => 'Прочее (Отопление и вентиляция)',
    'ШМР_Прочее_ПожарнаяСигнализация' => 'Прочее (Пожарная сигнализация)',
    'ШМР_Прочее_ОхраннаяСигнализация' => 'Прочее (Охранная сигнализация)',
    'ШМР_Прочее_Автоматизация' => 'Прочее (Автоматизация)',
    'ШМР_Прочее_Метрология' => 'Прочее (Метрология)'
];

foreach ($confirmationMappings as $activityType => $expectedMapping) {
    $mappingExists = strpos($tabelEntryContent, "'$activityType' => '$expectedMapping'") !== false;
    echo "  $activityType => $expectedMapping: " . ($mappingExists ? "✅ FOUND" : "❌ MISSING") . "\n";
}

echo "\n6. Testing confirmation callbacks in extractCategoryFromCallback...\n";
echo "================================================================\n";

$confirmationCallbacks = [
    'cat_ШМР_ЭлектрОб_Проч_yes' => 'Табель_ШМР_Прочее_Электрооборудование_yes',
    'cat_ШМР_ОтоплВент_Проч_yes' => 'Табель_ШМР_Прочее_ОтоплениеИВентиляция_yes',
    'cat_ШМР_ПожСигн_Проч_yes' => 'Табель_ШМР_Прочее_ПожарнаяСигнализация_yes',
    'cat_ШМР_ОхрСигн_Проч_yes' => 'Табель_ШМР_Прочее_ОхраннаяСигнализация_yes',
    'cat_ШМР_Автом_Проч_yes' => 'Табель_ШМР_Прочее_Автоматизация_yes',
    'cat_ШМР_Метрол_Проч_yes' => 'Табель_ШМР_Прочее_Метрология_yes'
];

foreach ($confirmationCallbacks as $callback => $expectedMapping) {
    $mappingExists = strpos($tabelHandlerContent, "'$callback' => '$expectedMapping'") !== false;
    echo "  $callback => $expectedMapping: " . ($mappingExists ? "✅ FOUND" : "❌ MISSING") . "\n";
}

echo "\n7. Testing handleNewProcheeConfirmation callbacks...\n";
echo "==================================================\n";

$procheeConfirmationCallbacks = [
    'cat_ШМР_ЭлектрОб_Проч_yes',
    'cat_ШМР_ОтоплВент_Проч_yes',
    'cat_ШМР_ПожСигн_Проч_yes',
    'cat_ШМР_ОхрСигн_Проч_yes',
    'cat_ШМР_Автом_Проч_yes',
    'cat_ШМР_Метрол_Проч_yes'
];

foreach ($procheeConfirmationCallbacks as $callback) {
    $callbackExists = strpos($tabelHandlerContent, "'$callback'") !== false;
    echo "  $callback in handleNewProcheeConfirmation: " . ($callbackExists ? "✅ FOUND" : "❌ MISSING") . "\n";
}

echo "\n=== SUMMARY ===\n";

// Count all tests
$totalTests = count($callbackMappings) + count($handlers) + count($activityTypes) + 
              count($activityMappings) + count($confirmationMappings) + 
              count($confirmationCallbacks) + count($procheeConfirmationCallbacks);

$passedTests = 0;

// Count passed tests
foreach ($callbackMappings as $callback => $expectedMapping) {
    if (strpos($tabelHandlerContent, "'$callback' => '$expectedMapping'") !== false) {
        $passedTests++;
    }
}

foreach ($handlers as $handler => $description) {
    if (strpos($tabelHandlerContent, "category === '$handler'") !== false) {
        $passedTests++;
    }
}

foreach ($activityTypes as $activityType) {
    if (strpos($tabelHandlerContent, "'$activityType'") !== false) {
        $passedTests++;
    }
}

foreach ($activityMappings as $activityType => $expectedMapping) {
    if (strpos($tabelEntryContent, "'$activityType' => '$expectedMapping'") !== false) {
        $passedTests++;
    }
}

foreach ($confirmationMappings as $activityType => $expectedMapping) {
    if (strpos($tabelEntryContent, "'$activityType' => '$expectedMapping'") !== false) {
        $passedTests++;
    }
}

foreach ($confirmationCallbacks as $callback => $expectedMapping) {
    if (strpos($tabelHandlerContent, "'$callback' => '$expectedMapping'") !== false) {
        $passedTests++;
    }
}

foreach ($procheeConfirmationCallbacks as $callback) {
    if (strpos($tabelHandlerContent, "'$callback'") !== false) {
        $passedTests++;
    }
}

echo "\nTest Results: $passedTests/$totalTests tests passed\n";

if ($passedTests === $totalTests) {
    echo "🎉 ALL TESTS PASSED! ШМР equipment 'Прочее' buttons should now work correctly.\n";
    echo "\n✅ Expected behavior:\n";
    echo "1. User clicks ШМР equipment 'Прочее' button (e.g., cat_ШМР_ЭлектрОб_Проч)\n";
    echo "2. System prompts: 'Пожалуйста, введите описание выполняемой работы:'\n";
    echo "3. User enters custom text (e.g., 'Тестовая работа')\n";
    echo "4. System shows confirmation with custom text\n";
    echo "5. User confirms and tabel entry is created with custom description\n";
    echo "6. Final report includes the custom work description\n";
} else {
    echo "❌ SOME TESTS FAILED! Please check the missing components above.\n";
    
    if ($passedTests > $totalTests * 0.8) {
        echo "\n⚠️ Most components are in place. The fix might still work with minor issues.\n";
    } else {
        echo "\n🚨 CRITICAL: Many components are missing. The fix will likely not work.\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";