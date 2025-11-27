<?php

// Debug heating ventilation callback mapping
$callbackData = 'cat_ТабВентОтопл';

echo "Original callback: {$callbackData}\n";

// Simulate the extractCategoryFromCallback logic
if (strpos($callbackData, 'cat_Таб') === 0) {
    $shortFormats = [
        'cat_ТабВентОтопл' => 'Табель_ОтоплениеИВентиляция',
    ];
    
    if (isset($shortFormats[$callbackData])) {
        $mappedCategory = $shortFormats[$callbackData];
        echo "Mapped category: {$mappedCategory}\n";
    } else {
        echo "No mapping found for: {$callbackData}\n";
        $fallback = substr($callbackData, strlen('cat_'));
        echo "Fallback category: {$fallback}\n";
    }
} else {
    echo "Callback doesn't start with 'cat_Таб'\n";
}

// Test if category matches handler conditions
$category = 'Табель_ОтоплениеИВентиляция';
echo "Testing handler conditions for: {$category}\n";

if ($category === 'cat_ТабВентОтопл') {
    echo "✓ Would match: cat_ТабВентОтопл handler\n";
} else {
    echo "✗ Would NOT match: cat_ТабВентОтопл handler\n";
}

if ($category === 'Табель_ОтоплениеИВентиляция') {
    echo "✓ Would match: Табель_ОтоплениеИВентиляция handler\n";
} else {
    echo "✗ Would NOT match: Табель_ОтоплениеИВентиляция handler\n";
}

?>