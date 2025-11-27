<?php

// Debug the metrology callback routing issue

echo "=== DEBUGGING METROLOGY CALLBACK ROUTING ===\n\n";

// Simulate the exact callback that should be sent when user clicks "Работы по графику" in metrology
$originalCallback = 'cat_ТабМетрол_РабПоГр';
echo "1. Original callback from button: '{$originalCallback}'\n";

// Simulate the category extraction logic from TabelHandler::extractCategoryFromCallback
function extractCategoryFromCallback(string $callbackData): string 
{
    error_log("TabelHandler: extractCategoryFromCallback called with: '{$callbackData}'");
    
    // Category mapping for short callback formats
    $callbackMappings = [
        'cat_ТабМетрол' => 'Табель_Метрология',
        'cat_ТабМетрол_РабПоГр' => 'Табель_РабПоГр_Метрол',
        'cat_ТабМетрол_ФормИД' => 'Табель_ФормИД_Метрол',
        'cat_ТабМетрол_ПодтВех' => 'Табель_ПодтВех_Метрол',
        'cat_ТабМетрол_ДопРаб' => 'Табель_ДопРаб_Метрол',
        // Additional works sub-items for metrology
        'cat_ТабМетрол_ИзмКонф' => 'Табель_ИзмКонф_Метрол',
        'cat_ТабМетрол_КалПов' => 'Табель_КалПов_Метрол',
        'cat_ТабМетрол_КМХ' => 'Табель_КМХ_Метрол',
        'cat_ТабМетрол_КоррХар' => 'Табель_КоррХар_Метрол',
        'cat_ТабМетрол_Проч' => 'Табель_Проч_Метрол',
    ];
    
    // Check for mapped callback first
    if (isset($callbackMappings[$callbackData])) {
        $mapped = $callbackMappings[$callbackData];
        error_log("TabelHandler: Mapped '{$callbackData}' to '{$mapped}'");
        return $mapped;
    }
    
    // Remove 'cat_' prefix if present
    if (str_starts_with($callbackData, 'cat_')) {
        $withoutPrefix = substr($callbackData, 4);
        error_log("TabelHandler: Removed 'cat_' prefix: '{$withoutPrefix}'");
        return $withoutPrefix;
    }
    
    error_log("TabelHandler: No transformation applied to: '{$callbackData}'");
    return $callbackData;
}

$extractedCategory = extractCategoryFromCallback($originalCallback);
echo "2. Extracted category: '{$extractedCategory}'\n";

echo "\n3. Checking what handleOtherWorkTypes would receive:\n";

// Check if this matches the metrology handlers
$metrologyHandlers = [
    'ТабМетрол_РабПоГр',
    'cat_ТабМетрол_РабПоГр', 
    'Табель_РабПоГр_Метрол'
];

echo "   Handler looks for: " . implode(', ', $metrologyHandlers) . "\n";
echo "   Extracted category: '{$extractedCategory}'\n";

$found = false;
foreach ($metrologyHandlers as $handler) {
    if ($extractedCategory === $handler) {
        echo "   ✅ MATCH FOUND: '{$handler}'\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "   ❌ NO MATCH - This would cause 'Неизвестная команда табеля'\n";
    echo "\n4. Debugging the issue:\n";
    echo "   - The callback 'cat_ТабМетрол_РабПоГр' gets mapped to 'Табель_РабПоГр_Метрол'\n";
    echo "   - The handler checks for 'ТабМетрол_РабПоГр', 'cat_ТабМетрол_РабПоГр', or 'Табель_РабПоГр_Метрол'\n";
    echo "   - So it should match 'Табель_РабПоГр_Метрол'\n";
    echo "   - If it's not working, there might be an encoding issue or the handler is not being reached\n";
} else {
    echo "   ✅ This should work correctly!\n";
}

echo "\n=== END DEBUG ===\n";