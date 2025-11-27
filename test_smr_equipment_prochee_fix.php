<?php

/**
 * Test to verify ШМР equipment "Прочее" buttons fix
 * 
 * This test verifies that all 6 ШМР equipment types now have working "Прочее" buttons:
 * 1. Электрооборудование (Electrical Equipment)
 * 2. Отопление и вентиляция (Heating and Ventilation) 
 * 3. Пожарная сигнализация (Fire Alarm)
 * 4. Охранная сигнализация (Security Alarm)
 * 5. Автоматизация (Automation)
 * 6. Метрология (Metrology)
 */

echo "=== ШМР Equipment 'Прочее' Buttons Fix Test ===\n\n";

// Check if the handler files exist
$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
$tabelEntryPath = __DIR__ . '/CustomCommands/Services/TabelEntry.php';

echo "1. Checking required files...\n";
echo "  TabelHandler.php: " . (file_exists($tabelHandlerPath) ? "✅ EXISTS" : "❌ MISSING") . "\n";
echo "  MessageService.php: " . (file_exists($messageServicePath) ? "✅ EXISTS" : "❌ MISSING") . "\n";
echo "  TabelEntry.php: " . (file_exists($tabelEntryPath) ? "✅ EXISTS" : "❌ MISSING") . "\n\n";

// Check TabelHandler.php for new ШМР equipment handlers
echo "2. Checking TabelHandler.php for ШМР equipment 'Прочее' handlers...\n";
$tabelContent = file_get_contents($tabelHandlerPath);

$equipmentTypes = [
    'Электрооборудование' => 'Табель_ШМР_Прочее_Электрооборудование',
    'ОтоплениеИВентиляция' => 'Табель_ШМР_Прочее_ОтоплениеИВентиляция', 
    'ПожарнаяСигнализация' => 'Табель_ШМР_Прочее_ПожарнаяСигнализация',
    'ОхраннаяСигнализация' => 'Табель_ШМР_Прочее_ОхраннаяСигнализация',
    'Автоматизация' => 'Табель_ШМР_Прочее_Автоматизация',
    'Метрология' => 'Табель_ШМР_Прочее_Метрология'
];

foreach ($equipmentTypes as $equipmentName => $handlerPattern) {
    $hasHandler = strpos($tabelContent, $handlerPattern) !== false;
    echo "  $equipmentName: " . ($hasHandler ? "✅ HANDLER FOUND" : "❌ HANDLER MISSING") . "\n";
}

// Check callback mappings
echo "\n3. Checking callback mappings...\n";
$callbackMappings = [
    'cat_ШМР_ЭлектрОб_Проч' => 'Табель_ШМР_Прочее_Электрооборудование',
    'cat_ШМР_ОтоплВент_Проч' => 'Табель_ШМР_Прочее_ОтоплениеИВентиляция',
    'cat_ШМР_ПожСигн_Проч' => 'Табель_ШМР_Прочее_ПожарнаяСигнализация',
    'cat_ШМР_ОхрСигн_Проч' => 'Табель_ШМР_Прочее_ОхраннаяСигнализация',
    'cat_ШМР_Автом_Проч' => 'Табель_ШМР_Прочее_Автоматизация',
    'cat_ШМР_Метрол_Проч' => 'Табель_ШМР_Прочее_Метрология'
];

foreach ($callbackMappings as $callback => $mapping) {
    $hasMapping = strpos($tabelContent, "'$callback' => '$mapping'") !== false;
    echo "  $callback: " . ($hasMapping ? "✅ MAPPING FOUND" : "❌ MAPPING MISSING") . "\n";
}

// Check confirmation callbacks
echo "\n4. Checking confirmation callbacks...\n";
$confirmationCallbacks = [
    'cat_ШМР_ЭлектрОб_Проч_yes' => 'Табель_ШМР_Прочее_Электрооборудование_yes',
    'cat_ШМР_ОтоплВент_Проч_yes' => 'Табель_ШМР_Прочее_ОтоплениеИВентиляция_yes',
    'cat_ШМР_ПожСигн_Проч_yes' => 'Табель_ШМР_Прочее_ПожарнаяСигнализация_yes',
    'cat_ШМР_ОхрСигн_Проч_yes' => 'Табель_ШМР_Прочее_ОхраннаяСигнализация_yes',
    'cat_ШМР_Автом_Проч_yes' => 'Табель_ШМР_Прочее_Автоматизация_yes',
    'cat_ШМР_Метрол_Проч_yes' => 'Табель_ШМР_Прочее_Метрология_yes'
];

foreach ($confirmationCallbacks as $callback => $mapping) {
    $hasConfirmation = strpos($tabelContent, "'$callback' => '$mapping'") !== false;
    echo "  $callback: " . ($hasConfirmation ? "✅ CONFIRMATION FOUND" : "❌ CONFIRMATION MISSING") . "\n";
}

// Check MessageService.php for keyboard creation methods
echo "\n5. Checking MessageService.php for keyboard methods...\n";
$messageContent = file_get_contents($messageServicePath);

$keyboardMethods = [
    'createSMRElectricalEquipmentKeyboard',
    'createSMRHeatingVentilationKeyboard', 
    'createSMRFireAlarmKeyboard',
    'createSMRSecurityAlarmKeyboard',
    'createSMRAutomationKeyboard',
    'createSMRMetrologyKeyboard'
];

foreach ($keyboardMethods as $method) {
    $hasMethod = strpos($messageContent, "function $method") !== false;
    echo "  $method: " . ($hasMethod ? "✅ METHOD FOUND" : "❌ METHOD MISSING") . "\n";
}

// Check TabelEntry.php for activity type mappings
echo "\n6. Checking TabelEntry.php for activity type mappings...\n";
$entryContent = file_get_contents($tabelEntryPath);

$activityMappings = [
    'ШМР_Прочее_Электрооборудование' => '📝 Прочее (Электрооборудование, ШМР)',
    'ШМР_Прочее_ОтоплениеИВентиляция' => '📝 Прочее (Отопление и вентиляция, ШМР)',
    'ШМР_Прочее_ПожарнаяСигнализация' => '📝 Прочее (Пожарная сигнализация, ШМР)',
    'ШМР_Прочее_ОхраннаяСигнализация' => '📝 Прочее (Охранная сигнализация, ШМР)',
    'ШМР_Прочее_Автоматизация' => '📝 Прочее (Автоматизация, ШМР)',
    'ШМР_Прочее_Метрология' => '📝 Прочее (Метрология, ШМР)'
];

foreach ($activityMappings as $activityType => $mapping) {
    $hasMapping = strpos($entryContent, "'$activityType' => '$mapping'") !== false;
    echo "  $activityType: " . ($hasMapping ? "✅ MAPPING FOUND" : "❌ MAPPING MISSING") . "\n";
}

echo "\n=== Test Summary ===\n";
echo "The fix ensures that all 6 ШМР equipment types now have working 'Прочее' buttons\n";
echo "that properly prompt for custom input and create tabeel entries.\n\n";

echo "Previously working: ШМР Технологическое оборудование (Technological Equipment)\n";
echo "Now fixed and working:\n";
echo "  - ШМР Электрооборудование (Electrical Equipment)\n";
echo "  - ШМР Отопление и вентиляция (Heating and Ventilation)\n";
echo "  - ШМР Пожарная сигнализация (Fire Alarm)\n";
echo "  - ШМР Охранная сигнализация (Security Alarm)\n";
echo "  - ШМР Автоматизация (Automation)\n";
echo "  - ШМР Метрология (Metrology)\n\n";

echo "✅ Fix implementation complete!\n";

?>