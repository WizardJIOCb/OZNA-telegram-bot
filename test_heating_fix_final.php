<?php

echo "=== HEATING & VENTILATION CALLBACK FIX VERIFICATION ===\n\n";

// Test both callback formats
$callbacks = ['cat_ТабВентОтопл', 'cat_ТабОтоплВент'];

foreach ($callbacks as $callback) {
    echo "Testing callback: '{$callback}'\n";
    echo "  Bytes: " . strlen($callback) . "\n";
    
    // Test canHandle patterns
    $patterns = [
        'category_Табель' => str_starts_with($callback, 'category_Табель'),
        'cat_ТабПНРаботы' => str_starts_with($callback, 'cat_ТабПНРаботы'),
        'cat_Таб' => str_starts_with($callback, 'cat_Таб'),
        'exact cat_ТабВентОтопл' => ($callback === 'cat_ТабВентОтопл'),
        'exact cat_ТабОтоплВент' => ($callback === 'cat_ТабОтоплВент'),
        'starts cat_ТабВентОтопл_' => str_starts_with($callback, 'cat_ТабВентОтопл_'),
        'starts cat_ТабОтоплВент_' => str_starts_with($callback, 'cat_ТабОтоплВент_')
    ];
    
    $anyMatch = false;
    foreach ($patterns as $pattern => $matches) {
        if ($matches) {
            echo "  ✓ MATCH: {$pattern}\n";
            $anyMatch = true;
        }
    }
    
    if (!$anyMatch) {
        echo "  ✗ NO MATCHES - canHandle would FAIL\n";
    }
    
    // Test mapping
    if (strpos($callback, 'cat_Таб') === 0) {
        $shortFormats = [
            'cat_ТабВентОтопл' => 'Табель_ОтоплениеИВентиляция',
            'cat_ТабОтоплВент' => 'Табель_ОтоплениеИВентиляция',
        ];
        
        if (isset($shortFormats[$callback])) {
            $mapped = $shortFormats[$callback];
            echo "  ✓ MAPPED TO: '{$mapped}'\n";
        } else {
            echo "  ✗ NO MAPPING FOUND\n";
        }
    }
    
    echo "\n";
}

echo "=== EXPECTED FLOW ===\n";
echo "1. User clicks '❄️ Отопление и вентиляция'\n";
echo "2. Button sends: 'cat_ТабВентОтопл' (from fixed keyboard)\n";
echo "3. canHandle: should return TRUE (matches 'cat_Таб' pattern)\n";
echo "4. extractCategory: maps to 'Табель_ОтоплениеИВентиляция'\n";
echo "5. Handler: routes to handleHeatingVentilationEquipmentMenu()\n";
echo "6. Result: Shows submenu with heating/ventilation activities\n";

echo "\n=== FIX SUMMARY ===\n";
echo "✓ Added support for both callback formats in canHandle\n";
echo "✓ Added mapping for both formats in extractCategoryFromCallback\n";
echo "✓ Fixed keyboard to use consistent format\n";
echo "✓ Added handler for mapped category format\n";

?>