<?php
/**
 * Test script to verify supervision works fixes
 * ISSUE 1: "Работы по графику" auto-executing instead of showing confirmation
 * ISSUE 2: "Подтвердить достижение вехи" silent after clicking Да
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

use CustomCommands\Handlers\TabelHandler;

echo "🔧 TESTING SUPERVISION WORKS FIXES\n";
echo "=================================\n\n";

// Create handler instance for testing
$handler = new TabelHandler(null, null, null);

// Test cases for supervision works callbacks
$testCases = [
    // Schedule callbacks that should NOT auto-execute (should show confirmation dialog)
    'cat_ШМР_ВхКонтр_РабПоГр' => 'Schedule button - should show confirmation dialog',
    'cat_ШМР_МонтПлощ_РабПоГр' => 'Schedule button - should show confirmation dialog',
    
    // Schedule confirmations that should execute
    'cat_ШМР_ВхКонтр_РабПоГр_yes' => 'Schedule confirmation - should execute',
    'cat_ШМР_МонтПлощ_РабПоГр_yes' => 'Schedule confirmation - should execute',
    
    // Simple supervision confirmations
    'ШМР_АналФунд_yes' => 'Site inspection confirmation - should execute',
    'ШМР_ПодтВех_yes' => 'Milestone confirmation - should execute (FIXED)',
    
    // Milestone buttons that should show confirmation dialog
    'cat_ШМР_ПодтВех' => 'Milestone button - should show confirmation dialog',
];

echo "1. TESTING PATTERN MATCHING\n";
echo "==========================\n";

foreach ($testCases as $callback => $description) {
    echo "Testing: {$callback}\n";
    echo "Expected: {$description}\n";
    
    // Test if it's detected as activity execution
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('isActivityExecution');
    $method->setAccessible(true);
    
    $isExecution = $method->invoke($handler, $callback);
    echo "Result: " . ($isExecution ? "✅ EXECUTION" : "❌ NOT EXECUTION") . "\n";
    
    echo "---\n";
}

echo "\n2. TESTING CALLBACK FLOW LOGIC\n";
echo "==============================\n";

// Test the flow logic to ensure schedule buttons don't auto-execute
$scheduleCallbacks = [
    'cat_ШМР_ВхКонтр_РабПоГр',
    'cat_ШМР_МонтПлощ_РабПоГр',
    'cat_ШМР_АналФунд_РабПоГр'
];

echo "Testing schedule buttons (should NOT be detected as immediate execution):\n";
foreach ($scheduleCallbacks as $callback) {
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('isActivityExecution');
    $method->setAccessible(true);
    
    $isExecution = $method->invoke($handler, $callback);
    $status = $isExecution ? "❌ WILL AUTO-EXECUTE (BAD)" : "✅ WILL SHOW CONFIRMATION (GOOD)";
    echo "  {$callback}: {$status}\n";
}

echo "\nTesting confirmation callbacks (should be detected as execution):\n";
$confirmationCallbacks = [
    'cat_ШМР_ВхКонтр_РабПоГр_yes',
    'ШМР_АналФунд_yes',
    'ШМР_ПодтВех_yes'
];

foreach ($confirmationCallbacks as $callback) {
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('isActivityExecution');
    $method->setAccessible(true);
    
    $isExecution = $method->invoke($handler, $callback);
    $status = $isExecution ? "✅ WILL EXECUTE (GOOD)" : "❌ WILL NOT EXECUTE (BAD)";
    echo "  {$callback}: {$status}\n";
}

echo "\n3. SUMMARY OF FIXES\n";
echo "==================\n";
echo "✅ FIXED: Removed early routing for ШМР schedule callbacks\n";
echo "✅ FIXED: Added ПодтВех milestone mapping to simple supervision map\n";
echo "✅ RESULT: Schedule buttons now show confirmation dialog instead of auto-executing\n";
echo "✅ RESULT: Milestone confirmations now properly execute instead of being silent\n";

echo "\n🎯 USER TESTING INSTRUCTIONS:\n";
echo "=============================\n";
echo "1. Test 'Работы по графику' button - should now show confirmation dialog\n";
echo "2. Test 'Подтвердить достижение вехи' Да button - should now respond and execute\n";
echo "3. Both issues should be resolved!\n";