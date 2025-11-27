<?php
/**
 * Test script to verify the equipment menu fix
 */

echo "=== EQUIPMENT MENU FIX VERIFICATION ===\n\n";

// Read the TabelHandler file
$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$content = file_get_contents($tabelHandlerPath);

if (!$content) {
    echo "❌ ERROR: Could not read TabelHandler.php\n";
    exit(1);
}

echo "✅ Successfully read TabelHandler.php\n\n";

// Check that all equipment menu methods have the correct signature
$equipmentMethods = [
    'handleElectricalEquipmentMenu',
    'handleAutomationEquipmentMenu', 
    'handleFireAlarmEquipmentMenu',
    'handleSecurityAlarmEquipmentMenu',
    'handleHeatingVentilationEquipmentMenu',
    'handleTechnologicalEquipmentMenu'
];

echo "1. CHECKING METHOD SIGNATURES:\n";
echo "==============================\n";

$allSignaturesCorrect = true;
foreach ($equipmentMethods as $method) {
    $pattern = '/function ' . $method . '\(string \$chatId, \?Conversation \$conversation = null\)/';
    if (preg_match($pattern, $content)) {
        echo "✅ {$method} - Correct signature\n";
    } else {
        echo "❌ {$method} - Incorrect signature\n";
        $allSignaturesCorrect = false;
    }
}

echo "\n";

// Check that all calls to equipment menu methods pass the conversation parameter
echo "2. CHECKING METHOD CALLS:\n";
echo "========================\n";

$allCallsCorrect = true;

// Check for calls that should NOT exist (without conversation parameter)
$incorrectPatterns = [
    '/return \$this->handleElectricalEquipmentMenu\(\$chatId\);/',
    '/return \$this->handleAutomationEquipmentMenu\(\$chatId\);/',
    '/return \$this->handleFireAlarmEquipmentMenu\(\$chatId\);/',
    '/return \$this->handleSecurityAlarmEquipmentMenu\(\$chatId\);/',
    '/return \$this->handleHeatingVentilationEquipmentMenu\(\$chatId\);/',
    '/return \$this->handleTechnologicalEquipmentMenu\(\$chatId\);/'
];

foreach ($incorrectPatterns as $pattern) {
    if (preg_match($pattern, $content)) {
        echo "❌ Found incorrect call: {$pattern}\n";
        $allCallsCorrect = false;
    }
}

// Check for calls that should exist (with conversation parameter)
$correctPatterns = [
    '/return \$this->handleElectricalEquipmentMenu\(\$chatId, \$conversation\);/',
    '/return \$this->handleAutomationEquipmentMenu\(\$chatId, \$conversation\);/',
    '/return \$this->handleFireAlarmEquipmentMenu\(\$chatId, \$conversation\);/',
    '/return \$this->handleSecurityAlarmEquipmentMenu\(\$chatId, \$conversation\);/',
    '/return \$this->handleHeatingVentilationEquipmentMenu\(\$chatId, \$conversation\);/',
    '/return \$this->handleTechnologicalEquipmentMenu\(\$chatId, \$conversation\);/'
];

$correctCallCount = 0;
foreach ($correctPatterns as $pattern) {
    $matches = [];
    if (preg_match_all($pattern, $content, $matches)) {
        $count = count($matches[0]);
        echo "✅ Found {$count} correct calls with conversation parameter\n";
        $correctCallCount += $count;
    }
}

echo "\n";

// Summary
echo "3. SUMMARY:\n";
echo "===========\n";

if ($allSignaturesCorrect) {
    echo "✅ All method signatures are correct\n";
} else {
    echo "❌ Some method signatures are incorrect\n";
}

if ($allCallsCorrect && $correctCallCount > 0) {
    echo "✅ All method calls pass the conversation parameter\n";
    echo "✅ Found {$correctCallCount} correct method calls\n";
} else {
    echo "❌ Some method calls are missing the conversation parameter\n";
}

if ($allSignaturesCorrect && $allCallsCorrect && $correctCallCount > 0) {
    echo "\n🎉 SUCCESS: Equipment menu fix has been applied correctly!\n";
    echo "The electrical equipment button should now work properly.\n";
} else {
    echo "\n❌ FAILURE: Equipment menu fix is incomplete.\n";
}

echo "\n";

// Additional check: Verify that the specific issue mentioned by the user is fixed
echo "4. SPECIFIC ISSUE VERIFICATION:\n";
echo "==============================\n";

// Check if the immediate electrical equipment handling in routeTabelCallback passes conversation
$immediateElectricalPattern = '/if \(\$category === \'cat_ТабЭлОб\' \|\| \$category === \'Табель_Электрооборудование\'\) \{[^}]*return \$this->handleElectricalEquipmentMenu\(\$chatId, \$conversation\);/s';
if (preg_match($immediateElectricalPattern, $content)) {
    echo "✅ Immediate electrical equipment handling correctly passes conversation parameter\n";
} else {
    echo "❌ Immediate electrical equipment handling may not correctly pass conversation parameter\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>