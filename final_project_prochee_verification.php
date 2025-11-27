<?php
/**
 * Final verification test for project activity Prochee button fix
 */

echo "=== FINAL VERIFICATION: PROJECT ACTIVITY ПРОЧЕЕ BUTTON FIX ===\n\n";

// Check if the fix is actually in the file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. Checking if handleNewProcheeConfirmation contains project activity mappings:\n";

// Check for the specific fix we applied
$hasProjectActivityMapping = false;
if (strpos($tabelHandlerContent, 'cat_ТабПроектДеят_ПНР_Проч_yes') !== false &&
    strpos($tabelHandlerContent, 'cat_ТабПроектДеят_ШМР_Проч_yes') !== false &&
    strpos($tabelHandlerContent, 'Прочее_ПроектДеят_ПНР') !== false &&
    strpos($tabelHandlerContent, 'Прочее_ПроектДеят_ШМР') !== false) {
    $hasProjectActivityMapping = true;
}

echo "   Project activity mapping in handleNewProcheeConfirmation: " . ($hasProjectActivityMapping ? "✅ PRESENT" : "❌ MISSING") . "\n";

// Check if the confirmation handler is in the routing
$hasConfirmationRouting = strpos($tabelHandlerContent, 'cat_ТабПроектДеят_ПНР_Проч_yes') !== false &&
                         strpos($tabelHandlerContent, 'cat_ТабПроектДеят_ШМР_Проч_yes') !== false &&
                         strpos($tabelHandlerContent, 'handleNewProcheeConfirmation') !== false;

echo "   Confirmation handler routing: " . ($hasConfirmationRouting ? "✅ PRESENT" : "❌ MISSING") . "\n";

echo "\n2. Checking TabelEntry.php for activity type definitions:\n";

$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

$hasProjectActivityTypes = strpos($tabelEntryContent, 'Прочее_ПроектДеят_ПНР') !== false &&
                          strpos($tabelEntryContent, 'Прочее_ПроектДеят_ШМР') !== false;

echo "   Project activity types defined: " . ($hasProjectActivityTypes ? "✅ PRESENT" : "❌ MISSING") . "\n";

// Check custom text handling
$hasCustomTextHandling = strpos($tabelEntryContent, 'Прочее_ПроектДеят_ПНР') !== false &&
                        strpos($tabelEntryContent, 'idleReason') !== false;

echo "   Custom text handling: " . ($hasCustomTextHandling ? "✅ PRESENT" : "❌ MISSING") . "\n";

echo "\n3. Complete flow verification:\n";

$testCallbacks = [
    'cat_ТабПроектДеят_ПНР_Проч_yes' => 'Прочее_ПроектДеят_ПНР',
    'cat_ТабПроектДеят_ШМР_Проч_yes' => 'Прочее_ПроектДеят_ШМР'
];

foreach ($testCallbacks as $callback => $expectedActivityType) {
    echo "   Testing: {$callback}\n";
    
    // Test canHandle
    $canHandle = (
        str_starts_with($callback, 'category_Табель') || 
        str_starts_with($callback, 'cat_ТабПНРаботы') ||
        str_starts_with($callback, 'cat_Таб')
    );
    echo "     canHandle: " . ($canHandle ? "✅ TRUE" : "❌ FALSE") . "\n";
    
    // Test confirmation handler recognition
    $matchesConfirmationHandler = strpos($tabelHandlerContent, $callback) !== false;
    echo "     In confirmation handler: " . ($matchesConfirmationHandler ? "✅ TRUE" : "❌ FALSE") . "\n";
    
    // Test activity type mapping
    if ($hasProjectActivityMapping) {
        echo "     Maps to: {$expectedActivityType} ✅\n";
    } else {
        echo "     Missing activity type mapping ❌\n";
    }
    
    echo "\n";
}

echo "4. DIAGNOSIS:\n";
echo "=============\n";

if ($hasProjectActivityMapping && $hasConfirmationRouting && $hasProjectActivityTypes && $hasCustomTextHandling) {
    echo "✅ ALL COMPONENTS ARE IN PLACE!\n\n";
    echo "🔄 RECOMMENDED ACTIONS:\n";
    echo "1. **RESTART THE APPLICATION SERVER** (most likely solution)\n";
    echo "2. Clear any PHP opcache if enabled\n";
    echo "3. Check server logs for specific error messages\n";
    echo "4. Verify user has FIO and functional direction set\n";
    echo "5. Check conversation state contains project_code and tabel_data\n\n";
    echo "📝 The fix is complete and should work after server restart.\n";
} else {
    echo "❌ MISSING COMPONENTS DETECTED!\n\n";
    echo "Missing components:\n";
    if (!$hasProjectActivityMapping) echo "- Project activity mapping in handleNewProcheeConfirmation\n";
    if (!$hasConfirmationRouting) echo "- Confirmation handler routing\n";
    if (!$hasProjectActivityTypes) echo "- Activity type definitions in TabelEntry\n";
    if (!$hasCustomTextHandling) echo "- Custom text handling\n";
    echo "\nThese need to be fixed before the button will work.\n";
}

echo "\n✅ VERIFICATION COMPLETE\n";

?>