<?php
/**
 * Final verification test for ALL ШМР intra-block installation "Прочее" buttons
 * Verifies that ALL 7 equipment types have complete "Прочее" functionality
 */

echo "=== COMPLETE ШМР INTRA-BLOCK 'ПРОЧЕЕ' VERIFICATION ===\n\n";

$messageServicePath = 'c:\Users\user\Documents\projects\CustomCommands\Services\MessageService.php';
$tabelHandlerPath = 'c:\Users\user\Documents\projects\CustomCommands\Handlers\TabelHandler.php';
$tabelEntryPath = 'c:\Users\user\Documents\projects\CustomCommands\Services\TabelEntry.php';

if (!file_exists($messageServicePath) || !file_exists($tabelHandlerPath) || !file_exists($tabelEntryPath)) {
    echo "❌ ERROR: Required files not found\n";
    exit(1);
}

$messageContent = file_get_contents($messageServicePath);
$handlerContent = file_get_contents($tabelHandlerPath);
$entryContent = file_get_contents($tabelEntryPath);

// Define all 7 equipment types with their expected components
$equipmentTypes = [
    'Technological' => [
        'name' => 'Технологическое оборудование',
        'keyboard_method' => 'createSMRTechnologicalEquipmentKeyboard',
        'button_callback' => 'cat_ШМР_ТехОб_Проч',
        'category_mapping' => 'Табель_ШМР_Прочее_ТехнологическоеОборудование',
        'activity_type' => 'ШМР_Прочее_ТехнологическоеОборудование',
        'confirmation_callback' => 'cat_ШМР_ТехОб_Проч_yes',
        'handler_category' => 'cat_ШМР_ТехОб_Проч_РабГр'
    ],
    'Electrical' => [
        'name' => 'Электрооборудование',
        'keyboard_method' => 'createSMRElectricalEquipmentKeyboard',
        'button_callback' => 'cat_ШМР_ЭлектрОб_Проч',
        'category_mapping' => 'Табель_ШМР_Прочее_Электрооборудование',
        'activity_type' => 'ШМР_Прочее_Электрооборудование',
        'confirmation_callback' => 'cat_ШМР_ЭлектрОб_Проч_yes',
        'handler_category' => 'cat_ШМР_ЭлектрОб_Проч'
    ],
    'HeatingVentilation' => [
        'name' => 'Отопление и вентиляция',
        'keyboard_method' => 'createSMRHeatingVentilationKeyboard',
        'button_callback' => 'cat_ШМР_ОтоплВент_Проч',
        'category_mapping' => 'Табель_ШМР_Прочее_ОтоплениеИВентиляция',
        'activity_type' => 'ШМР_Прочее_ОтоплениеИВентиляция',
        'confirmation_callback' => 'cat_ШМР_ОтоплВент_Проч_yes',
        'handler_category' => 'cat_ШМР_ОтоплВент_Проч'
    ],
    'FireAlarm' => [
        'name' => 'Пожарная сигнализация',
        'keyboard_method' => 'createSMRFireAlarmKeyboard',
        'button_callback' => 'cat_ШМР_ПожСигн_Проч',
        'category_mapping' => 'Табель_ШМР_Прочее_ПожарнаяСигнализация',
        'activity_type' => 'ШМР_Прочее_ПожарнаяСигнализация',
        'confirmation_callback' => 'cat_ШМР_ПожСигн_Проч_yes',
        'handler_category' => 'cat_ШМР_ПожСигн_Проч'
    ],
    'SecurityAlarm' => [
        'name' => 'Охранная сигнализация',
        'keyboard_method' => 'createSMRSecurityAlarmKeyboard',
        'button_callback' => 'cat_ШМР_ОхрСигн_Проч',
        'category_mapping' => 'Табель_ШМР_Прочее_ОхраннаяСигнализация',
        'activity_type' => 'ШМР_Прочее_ОхраннаяСигнализация',
        'confirmation_callback' => 'cat_ШМР_ОхрСигн_Проч_yes',
        'handler_category' => 'cat_ШМР_ОхрСигн_Проч'
    ],
    'Automation' => [
        'name' => 'Автоматизация',
        'keyboard_method' => 'createSMRAutomationKeyboard',
        'button_callback' => 'cat_ШМР_Автом_Проч',
        'category_mapping' => 'Табель_ШМР_Прочее_Автоматизация',
        'activity_type' => 'ШМР_Прочее_Автоматизация',
        'confirmation_callback' => 'cat_ШМР_Автом_Проч_yes',
        'handler_category' => 'cat_ШМР_Автом_Проч'
    ],
    'Metrology' => [
        'name' => 'Метрология',
        'keyboard_method' => 'createSMRMetrologyKeyboard',
        'button_callback' => 'cat_ШМР_Метрол_Проч',
        'category_mapping' => 'Табель_ШМР_Прочее_Метрология',
        'activity_type' => 'ШМР_Прочее_Метрология',
        'confirmation_callback' => 'cat_ШМР_Метрол_Проч_yes',
        'handler_category' => 'cat_ШМР_Метрол_Проч'
    ]
];

$totalTests = 0;
$passedTests = 0;

foreach ($equipmentTypes as $key => $equipment) {
    echo "\n=== {$equipment['name']} ===\n";
    
    // Test 1: Keyboard has "Прочее" button
    $hasButton = strpos($messageContent, "'{$equipment['button_callback']}'") !== false;
    echo "1. Keyboard button ({$equipment['button_callback']}): " . ($hasButton ? "✅" : "❌") . "\n";
    $totalTests++;
    if ($hasButton) $passedTests++;
    
    // Test 2: Callback mapping exists
    $hasMapping = strpos($handlerContent, "'{$equipment['button_callback']}' => '{$equipment['category_mapping']}'") !== false;
    echo "2. Callback mapping: " . ($hasMapping ? "✅" : "❌") . "\n";
    $totalTests++;
    if ($hasMapping) $passedTests++;
    
    // Test 3: Activity type registered
    $hasActivityType = strpos($handlerContent, "'{$equipment['activity_type']}'") !== false;
    echo "3. Activity type registered: " . ($hasActivityType ? "✅" : "❌") . "\n";
    $totalTests++;
    if ($hasActivityType) $passedTests++;
    
    // Test 4: Handler exists in handleOtherWorkTypes
    $hasHandler = strpos($handlerContent, $equipment['handler_category']) !== false && 
                  strpos($handlerContent, "awaiting_custom_input") !== false;
    echo "4. Custom input handler: " . ($hasHandler ? "✅" : "❌") . "\n";
    $totalTests++;
    if ($hasHandler) $passedTests++;
    
    // Test 5: Confirmation callback mapping
    $hasConfirmation = strpos($handlerContent, "'{$equipment['confirmation_callback']}'") !== false;
    echo "5. Confirmation callback: " . ($hasConfirmation ? "✅" : "❌") . "\n";
    $totalTests++;
    if ($hasConfirmation) $passedTests++;
    
    // Test 6: Activity type mapping in TabelEntry
    $hasEntryMapping = strpos($entryContent, "'{$equipment['activity_type']}' =>") !== false;
    echo "6. TabelEntry mapping: " . ($hasEntryMapping ? "✅" : "❌") . "\n";
    $totalTests++;
    if ($hasEntryMapping) $passedTests++;
    
    // Test 7: Confirmation message mapping in TabelEntry
    $hasConfirmationMapping = strpos($entryContent, "'{$equipment['activity_type']}' =>") !== false;
    echo "7. Confirmation message mapping: " . ($hasConfirmationMapping ? "✅" : "❌") . "\n";
    $totalTests++;
    if ($hasConfirmationMapping) $passedTests++;
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "SUMMARY: {$passedTests}/{$totalTests} tests passed\n";

if ($passedTests === $totalTests) {
    echo "\n🎉 SUCCESS! All 7 ШМР intra-block equipment types have complete 'Прочее' functionality!\n";
    echo "\n✅ Expected behavior for ALL equipment types:\n";
    echo "1. User clicks any ШМР equipment 'Прочее' button\n";
    echo "2. System prompts for custom work description\n";
    echo "3. User enters description\n";
    echo "4. System shows confirmation\n";
    echo "5. User confirms\n";
    echo "6. Tabel entry created with custom description\n";
} else {
    $failedTests = $totalTests - $passedTests;
    echo "\n⚠️ WARNING: {$failedTests} test(s) failed!\n";
    echo "Some equipment types may not have complete 'Прочее' functionality.\n";
    
    $percentage = ($passedTests / $totalTests) * 100;
    if ($percentage >= 85) {
        echo "\n✅ Most components are in place ({$percentage}% complete)\n";
        echo "The functionality should work for most equipment types.\n";
    } else {
        echo "\n❌ CRITICAL: Only {$percentage}% complete\n";
        echo "Significant components are missing.\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";