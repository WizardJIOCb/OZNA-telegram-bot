<?php
/**
 * Debug the "неизвестная команда табеля" issue for project activity Прочее buttons
 */

echo "=== DEBUGGING 'НЕИЗВЕСТНАЯ КОМАНДА ТАБЕЛЯ' ISSUE ===\n\n";

// Test the exact routing flow that should happen
$testCallbacks = [
    'cat_ТабПроектДеят_ПНР_Проч_yes',
    'cat_ТабПроектДеят_ШМР_Проч_yes'
];

foreach ($testCallbacks as $callback) {
    echo "Testing callback: '{$callback}'\n";
    echo "==========================================\n";
    
    // Step 1: TabelHandler canHandle check
    echo "1. TabelHandler canHandle check:\n";
    $canHandle = (
        str_starts_with($callback, 'category_Табель') || 
        str_starts_with($callback, 'cat_ТабПНРаботы') ||
        str_starts_with($callback, 'cat_Таб') ||
        str_starts_with($callback, 'cat_Гарантии_') ||
        $callback === 'новое_прочее_yes' ||
        $callback === 'cat_ТабЭлОб_Проч_yes' ||
        $callback === 'cat_ТабВентОтопл' ||
        $callback === 'cat_ТабОтоплВент' ||
        str_starts_with($callback, 'cat_ТабВентОтопл_') ||
        str_starts_with($callback, 'cat_ТабОтоплВент_') ||
        $callback === 'cat_ТабГарантии'
    );
    echo "   Result: " . ($canHandle ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    if (!$canHandle) {
        echo "   ❌ PROBLEM: TabelHandler cannot handle this callback!\n\n";
        continue;
    }
    
    // Step 2: Check routing priority in routeTabelCallback
    echo "2. Routing priority check:\n";
    
    // Check if it matches the NEW Prochee confirmation handler
    $matchesNewProchee = (
        $callback === 'cat_ТабЭлОб_Проч_yes' || 
        $callback === 'новое_прочее_yes' || 
        $callback === 'cat_ТабАвтом_Проч_yes' || 
        $callback === 'cat_ТабВентОтопл_Проч_yes' || 
        $callback === 'cat_ТабПроектДеят_ПНР_Проч_yes' || 
        $callback === 'cat_ТабПроектДеят_ШМР_Проч_yes'
    );
    echo "   Matches NEW Prochee handler: " . ($matchesNewProchee ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    // Check if it would also match isActivityExecution
    $matchesActivityExecution = false;
    if (preg_match('/^cat_ТабПроектДеят_(.+)_yes$/', $callback, $matches)) {
        $matchesActivityExecution = true;
        echo "   Also matches isActivityExecution: TRUE ⚠️ (POTENTIAL CONFLICT)\n";
    } else {
        echo "   Also matches isActivityExecution: FALSE ✅\n";
    }
    
    // Step 3: Check which handler would execute first
    echo "3. Handler execution order:\n";
    if ($matchesNewProchee) {
        echo "   ✅ Would execute handleNewProcheeConfirmation (CORRECT)\n";
    } else {
        echo "   ❌ Would NOT execute handleNewProcheeConfirmation\n";
    }
    
    if ($matchesActivityExecution) {
        echo "   ⚠️  Would also try to execute handleActivityExecution (CONFLICT)\n";
    }
    
    echo "\n";
}

echo "🔍 POSSIBLE CAUSES FOR 'НЕИЗВЕСТНАЯ КОМАНДА ТАБЕЛЯ':\n";
echo "=====================================================\n\n";

echo "1. ROUTING CONFLICT:\n";
echo "   The callback might be hitting isActivityExecution BEFORE handleNewProcheeConfirmation\n";
echo "   Solution: Ensure NEW Prochee handler is checked FIRST in routeTabelCallback\n\n";

echo "2. MISSING ACTIVITY TYPE IN TabelEntry:\n";
echo "   The activity types 'Прочее_ПроектДеят_ПНР' and 'Прочее_ПроектДеят_ШМР' might not be defined\n";
echo "   Solution: Check TabelEntry.php for activity type definitions\n\n";

echo "3. CONVERSATION STATE ISSUE:\n";
echo "   The conversation might not have the required data (custom text, project code, etc.)\n";
echo "   Solution: Check conversation notes contain required data\n\n";

echo "4. SERVER RESTART NEEDED:\n";
echo "   The fix might not be active until server restart\n";
echo "   Solution: Restart the application server\n\n";

echo "🔧 NEXT DEBUGGING STEPS:\n";
echo "========================\n";
echo "1. Check server logs when clicking 'Да' button\n";
echo "2. Verify TabelEntry.php has project activity definitions\n";
echo "3. Check if isActivityExecution is conflicting with handleNewProcheeConfirmation\n";
echo "4. Restart server to ensure fixes are active\n";

?>