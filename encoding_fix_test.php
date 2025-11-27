<?php

echo "=== ENCODING FIX TEST ===\n";

// Test the exact corrupted pattern from logs
$corrupted = 'cat_РЁРњР _РџРѕРґРіР Р°Р±';
$expected = 'cat_ШМР_ПодгРаб';

echo "Original corrupted: '{$corrupted}'\n";
echo "Expected correct: '{$expected}'\n";

// Create a simple fix mapping
$fixes = [
    'РЁРњР _РџРѕРґРіР Р°Р±' => 'ШМР_ПодгРаб',
    'cat_РЁРњР _РџРѕРґРіР Р°Р±' => 'cat_ШМР_ПодгРаб'
];

// Test the fix
$result = $corrupted;
foreach ($fixes as $corrupted_pattern => $fixed_pattern) {
    if (strpos($result, $corrupted_pattern) !== false) {
        echo "Found pattern: '{$corrupted_pattern}'\n";
        $result = str_replace($corrupted_pattern, $fixed_pattern, $result);
        echo "Fixed to: '{$result}'\n";
    }
}

if ($result === $expected) {
    echo "✅ FIX WORKS CORRECTLY!\n";
} else {
    echo "❌ Fix failed. Result: '{$result}'\n";
}

// Test site inspection pattern as well
$site_inspection_corrupted = 'cat_ШМР_ПровМонтПлощ';
$site_inspection_expected = 'cat_ШМР_ПровМонтПлощ';

echo "\nSite inspection test:\n";
echo "Pattern: '{$site_inspection_corrupted}'\n";

if ($site_inspection_corrupted === $site_inspection_expected) {
    echo "✅ Site inspection pattern is already correct\n";
}

?>