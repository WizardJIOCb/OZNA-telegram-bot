<?php

// Simple test for the corruption patterns
$corruptedCallback = 'cat_РЁРњР _РџРѕРґРіР Р°Р±';
echo "Original corrupted callback: '$corruptedCallback'\n";
echo "Hex: " . bin2hex($corruptedCallback) . "\n\n";

// Test early exit patterns
echo "Early exit patterns:\n";
echo "cat_Таб: " . (strpos($corruptedCallback, 'cat_Таб') === 0 ? 'MATCH' : 'NO MATCH') . "\n";
echo "cat_РЁРњР : " . (strpos($corruptedCallback, 'cat_РЁРњР ') === 0 ? 'MATCH' : 'NO MATCH') . "\n\n";

// Manual replacement test
$replacements = [
    'РЁРњР ' => 'ШМР',
    'cat_РЁРњР ' => 'cat_ШМР',
    'cat_РЁРњР _' => 'cat_ШМР_',
    'РџРѕРґРіР Р°Р±' => 'ПодгРаб',
    'cat_РЁРњР _РџРѕРґРіР Р°Р±' => 'cat_ШМР_ПодгРаб'
];

$fixedCallback = $corruptedCallback;
foreach ($replacements as $corrupted => $fixed) {
    if (strpos($fixedCallback, $corrupted) !== false) {
        echo "Found pattern '$corrupted' -> replacing with '$fixed'\n";
        $fixedCallback = str_replace($corrupted, $fixed, $fixedCallback);
    }
}

echo "\nFixed callback: '$fixedCallback'\n";
echo "Expected: 'cat_ШМР_ПодгРаб'\n";
echo "Success: " . ($fixedCallback === 'cat_ШМР_ПодгРаб' ? 'YES' : 'NO') . "\n";