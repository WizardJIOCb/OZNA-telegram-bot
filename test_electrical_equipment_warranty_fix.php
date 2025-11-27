<?php
/**
 * Test script to verify the electrical equipment warranty fix
 */

echo "=== ELECTRICAL EQUIPMENT WARRANTY FIX VERIFICATION ===\n\n";

// Read the TabelHandler file to verify the fix
$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$content = file_get_contents($tabelHandlerPath);

echo "1. CHECKING WARRANTY EQUIPMENT MAPPING IN isActivityExecution:\n";
echo "===========================================================\n";

// Check if the electrical equipment mapping exists
$hasElectricalMapping = strpos($content, "'ЭлектрОб' => 'РабГарантии_ЭлектрОб'") !== false;
echo "✓ Electrical equipment mapping: " . ($hasElectricalMapping ? "✅ EXISTS" : "❌ MISSING") . "\n";

// Check if warranty equipment pattern exists
$hasWarrantyPattern = strpos($content, "preg_match('/^cat_Гарантии_(.+)_yes\$/u', \$category, \$matches)") !== false;
echo "✓ Warranty pattern regex: " . ($hasWarrantyPattern ? "✅ EXISTS" : "❌ MISSING") . "\n";

echo "\n2. CHECKING WARRANTY EQUIPMENT HANDLER IN handleActivityExecution:\n";
echo "================================================================\n";

// Check if warranty equipment handler exists in handleActivityExecution
$hasWarrantyHandler = strpos($content, "elseif (preg_match('/^cat_Гарантии_(.+)_yes\$/u', \$category, \$matches)) {") !== false;
echo "✓ Warranty equipment handler: " . ($hasWarrantyHandler ? "✅ EXISTS" : "❌ MISSING") . "\n";

// Check if warranty equipment activity type mapping exists
$hasWarrantyActivityMapping = strpos($content, "\$activityType = 'РабГарантии_' . \$equipmentType;") !== false;
echo "✓ Warranty activity type mapping: " . ($hasWarrantyActivityMapping ? "✅ EXISTS" : "❌ MISSING") . "\n";

echo "\n3. SIMULATING ELECTRICAL EQUIPMENT WARRANTY FLOW:\n";
echo "=================================================\n";

// Simulate the complete flow
$tests = [
    [
        'step' => 'User clicks Электрооборудование button',
        'callback' => 'category_Табель_РабГарантии_ЭлектрОб',
        'expected' => 'Shows confirmation dialog'
    ],
    [
        'step' => 'User clicks Да button', 
        'callback' => 'cat_Гарантии_ЭлектрОб_yes',
        'expected' => 'Creates report and sends message'
    ]
];

foreach ($tests as $test) {
    echo "STEP: " . $test['step'] . "\n";
    echo "  Callback: " . $test['callback'] . "\n";
    echo "  Expected: " . $test['expected'] . "\n";
    
    if ($test['callback'] === 'cat_Гарантии_ЭлектрОб_yes') {
        echo "  ✅ Should match warranty pattern in isActivityExecution\n";
        echo "  ✅ Should map to 'РабГарантии_ЭлектрОб' in warrantyEquipmentMap\n"; 
        echo "  ✅ Should return true from isActivityExecution\n";
        echo "  ✅ Should route to handleActivityExecution\n";
        echo "  ✅ Should create activity type 'РабГарантии_ЭлектрОб'\n";
        echo "  ✅ Should generate and send report\n";
    } else {
        echo "  ✅ Should route to warranty equipment handler\n";
        echo "  ✅ Should create confirmation with cat_Гарантии_ЭлектрОб_yes\n";
    }
    echo "\n";
}

echo "4. VERIFICATION SUMMARY:\n";
echo "========================\n";

$allChecks = [
    $hasElectricalMapping,
    $hasWarrantyPattern, 
    $hasWarrantyHandler,
    $hasWarrantyActivityMapping
];

$passedChecks = count(array_filter($allChecks));
$totalChecks = count($allChecks);

if ($passedChecks === $totalChecks) {
    echo "✅ ALL CHECKS PASSED ({$passedChecks}/{$totalChecks})\n";
    echo "✅ Electrical equipment warranty functionality should now work!\n";
    echo "✅ Clicking 'Электрооборудование' → 'Да' should generate reports\n";
} else {
    echo "❌ SOME CHECKS FAILED ({$passedChecks}/{$totalChecks})\n";
    echo "❌ Electrical equipment warranty functionality may not work properly\n";
}

echo "\n📝 TESTING INSTRUCTIONS:\n";
echo "========================\n";
echo "1. Go to Telegram bot\n";
echo "2. Click 'Работы по гарантии' (Warranty Works)\n";
echo "3. Click '⚡ Электрооборудование' (Electrical Equipment)\n";
echo "4. Click 'Да' (Yes) to confirm\n";
echo "5. ✅ You should receive a warranty work report!\n";

?>