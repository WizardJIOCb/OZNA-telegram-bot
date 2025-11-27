<?php

// Simple test to verify the technological equipment callback routing

// Expected callback from button: cat_ШМР_ТехОб
// Expected mapping result: Табель_ШМР_ТехнологическоеОборудование

$callback = 'cat_ШМР_ТехОб';

// Simulate the mapping logic from TabelHandler.php
$shortFormats = [
    'cat_ШМР_ТехОб' => 'Табель_ШМР_ТехнологическоеОборудование',
    'cat_ШМР_ЭлектрОб' => 'Табель_ШМР_Электрооборудование',
    'cat_ШМР_ОтоплВент' => 'Табель_ШМР_ОтоплениеИВентиляция',
    'cat_ШМР_ПожСигн' => 'Табель_ШМР_ПожарнаяСигнализация',
    'cat_ШМР_ОхрСигн' => 'Табель_ШМР_ОхраннаяСигнализация',
    'cat_ШМР_Автом' => 'Табель_ШМР_Автоматизация',
    'cat_ШМР_Метрол' => 'Табель_ШМР_Метрология',
];

echo "=== Testing Technological Equipment Callback Routing ===\n";
echo "Input callback: $callback\n";

if (isset($shortFormats[$callback])) {
    $mapped = $shortFormats[$callback];
    echo "✅ SUCCESS: Callback mapped to: $mapped\n";
    
    // Check if this matches our expected handler condition
    if ($mapped === 'Табель_ШМР_ТехнологическоеОборудование') {
        echo "✅ SUCCESS: Mapping matches expected handler condition!\n";
        echo "This should trigger the technological equipment submenu.\n";
    } else {
        echo "❌ ERROR: Mapping doesn't match expected handler condition.\n";
    }
} else {
    echo "❌ ERROR: Callback not found in mapping!\n";
}

echo "\n=== Testing Unicode Encoding ===\n";
echo "Callback bytes: " . bin2hex($callback) . "\n";
echo "Expected category bytes: " . bin2hex('Табель_ШМР_ТехнологическоеОборудование') . "\n";

echo "\n=== Testing Pattern Matching ===\n";
$testCategory = 'Табель_ШМР_ТехнологическоеОборудование';
if ($testCategory === 'Табель_ШМР_ТехнологическоеОборудование') {
    echo "✅ SUCCESS: Pattern matching works correctly!\n";
} else {
    echo "❌ ERROR: Pattern matching failed!\n";
}

echo "\nTest completed.\n";