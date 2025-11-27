<?php
/**
 * Test the specific "Да" button issue for project activity "Прочее" buttons
 */

echo "=== TESTING PROJECT ACTIVITY ПРОЧЕЕ 'ДА' BUTTON ISSUE ===\n\n";

// Test the exact callbacks that are being generated
$testCallbacks = [
    'cat_ТабПроектДеят_ПНР_Проч_yes',
    'cat_ТабПроектДеят_ШМР_Проч_yes'
];

foreach ($testCallbacks as $callback) {
    echo "Testing callback: '{$callback}'\n";
    echo "======================================\n";
    
    // Test 1: TabelHandler canHandle
    echo "1. TabelHandler canHandle test:\n";
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
    
    // Test 2: Check which condition matches
    echo "2. Matching conditions:\n";
    if (str_starts_with($callback, 'category_Табель')) {
        echo "   ✅ Matches: str_starts_with('category_Табель')\n";
    }
    if (str_starts_with($callback, 'cat_ТабПНРаботы')) {
        echo "   ✅ Matches: str_starts_with('cat_ТабПНРаботы')\n";
    }
    if (str_starts_with($callback, 'cat_Таб')) {
        echo "   ✅ Matches: str_starts_with('cat_Таб')\n";
    }
    
    // Test 3: Check if it matches the confirmation handler condition
    echo "3. Confirmation handler check:\n";
    $matchesConfirmationHandler = (
        $callback === 'cat_ТабЭлОб_Проч_yes' || 
        $callback === 'новое_прочее_yes' || 
        $callback === 'cat_ТабАвтом_Проч_yes' || 
        $callback === 'cat_ТабВентОтопл_Проч_yes' || 
        $callback === 'cat_ТабПроектДеят_ПНР_Проч_yes' || 
        $callback === 'cat_ТабПроектДеят_ШМР_Проч_yes'
    );
    echo "   Matches confirmation handler: " . ($matchesConfirmationHandler ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    // Test 4: Check if it matches isActivityExecution patterns
    echo "4. isActivityExecution check:\n";
    $isActivityExecution = false;
    
    // Check for electrical equipment pattern
    if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $callback, $matches)) {
        $isActivityExecution = true;
        echo "   ✅ Matches electrical equipment pattern\n";
    }
    
    // Check for automation equipment pattern  
    if (preg_match('/^cat_ТабАвтом_(.+)_yes$/', $callback, $matches)) {
        $isActivityExecution = true;
        echo "   ✅ Matches automation equipment pattern\n";
    }
    
    // Check for project activity pattern (THIS IS THE KEY!)
    if (preg_match('/^cat_ТабПроектДеят_(.+)_yes$/', $callback, $matches)) {
        $isActivityExecution = true;
        echo "   ✅ Matches project activity pattern (NEW!)\n";
    }
    
    echo "   Final isActivityExecution result: " . ($isActivityExecution ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    echo "\n";
}

echo "🎯 DIAGNOSIS:\n";
echo "=============\n";
echo "The project activity 'Прочее' confirmation callbacks:\n";
echo "1. ✅ ARE recognized by TabelHandler canHandle (via 'cat_Таб' pattern)\n";
echo "2. ✅ ARE matched by the confirmation handler condition\n";
echo "3. ❓ Need to check if isActivityExecution also recognizes them\n\n";

echo "🔧 LIKELY ISSUE:\n";
echo "================\n";
echo "The callbacks are being routed to handleNewProcheeConfirmation(),\n";
echo "but something in that method might be failing or not sending the report.\n";
echo "Need to check:\n";
echo "1. handleNewProcheeConfirmation method implementation\n";
echo "2. Whether it's properly retrieving custom text from conversation\n";
echo "3. Whether it's calling tabelEntry->addEntry correctly\n";
echo "4. Whether the success message is being sent\n";

?>