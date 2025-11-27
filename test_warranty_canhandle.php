<?php
/**
 * Test warranty equipment canHandle method to debug the flow
 */

echo "=== TESTING WARRANTY EQUIPMENT CANHANDLE ===\n\n";

// Test callbacks that should be handled
$testCallbacks = [
    'cat_Гарантии_ЭлектрОб',
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes',
    'category_Табель_РабГарантии_ЭлектрОб'
];

echo "Testing current TabelHandler canHandle logic:\n";
echo "=" . str_repeat("=", 50) . "\n";

foreach ($testCallbacks as $callback) {
    echo "Testing: '{$callback}'\n";
    
    // Test the conditions from canHandle method
    $condition1 = str_starts_with($callback, 'category_Табель');
    $condition2 = str_starts_with($callback, 'cat_ТабПНРаботы');
    $condition3 = str_starts_with($callback, 'cat_Таб');
    $condition4 = str_starts_with($callback, 'cat_Гарантии_');
    
    echo "  category_Табель: " . ($condition1 ? "✅" : "❌") . "\n";
    echo "  cat_ТабПНРаботы: " . ($condition2 ? "✅" : "❌") . "\n";
    echo "  cat_Таб: " . ($condition3 ? "✅" : "❌") . "\n";
    echo "  cat_Гарантии_: " . ($condition4 ? "✅" : "❌") . "\n";
    
    $canHandle = $condition1 || $condition2 || $condition3 || $condition4;
    echo "  RESULT: " . ($canHandle ? "✅ CAN HANDLE" : "❌ CANNOT HANDLE") . "\n";
    echo "\n";
}

echo "Testing CallbackqueryCommand early exit filter:\n";
echo "=" . str_repeat("=", 50) . "\n";

foreach ($testCallbacks as $callback) {
    echo "Testing: '{$callback}'\n";
    
    // Test the early exit conditions from CallbackqueryCommand
    $cat1 = strpos($callback, 'category_') === 0;
    $cat2 = strpos($callback, 'adm_req_') === 0;
    $cat3 = strpos($callback, 'acc_lvl_') === 0;
    $cat4 = strpos($callback, 'cat_Таб') === 0;
    $cat5 = strpos($callback, 'cat_Гарантии') === 0;
    
    echo "  category_: " . ($cat1 ? "✅" : "❌") . "\n";
    echo "  adm_req_: " . ($cat2 ? "✅" : "❌") . "\n";
    echo "  acc_lvl_: " . ($cat3 ? "✅" : "❌") . "\n";
    echo "  cat_Таб: " . ($cat4 ? "✅" : "❌") . "\n";
    echo "  cat_Гарантии: " . ($cat5 ? "✅" : "❌") . "\n";
    
    $passesFilter = $cat1 || $cat2 || $cat3 || $cat4 || $cat5;
    echo "  PASSES EARLY EXIT: " . ($passesFilter ? "✅ YES" : "❌ NO - EXITS HERE!") . "\n";
    echo "\n";
}

echo "=== ANALYSIS ===\n";
echo "If a callback doesn't pass the early exit filter in CallbackqueryCommand,\n";
echo "it never reaches TabelHandler, so canHandle is never called.\n";
echo "This could be why no logs are appearing for warranty equipment.\n";
?>