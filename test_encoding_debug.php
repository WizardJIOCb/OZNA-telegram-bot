<?php

// Test script to debug encoding issues with Cyrillic text in callback data

echo "=== Encoding Debug Test ===\n";

// Test string that represents the corrupted Cyrillic text we're seeing
$corruptedText = "РџСЂРѕС„РёР»СЊ";
$expectedText = "Профиль";

echo "Corrupted text: " . $corruptedText . "\n";
echo "Expected text: " . $expectedText . "\n";
echo "Corrupted text length: " . strlen($corruptedText) . "\n";
echo "Expected text length: " . strlen($expectedText) . "\n";
echo "Corrupted text mb_length: " . mb_strlen($corruptedText, 'UTF-8') . "\n";
echo "Expected text mb_length: " . mb_strlen($expectedText, 'UTF-8') . "\n";

// Try to fix the encoding
echo "\n=== Attempting to fix encoding ===\n";

// Method 1: Direct replacement of known patterns
$fixed1 = str_replace('РџСЂРѕС„РёР»СЊ', 'Профиль', $corruptedText);
echo "Method 1 (direct replacement): " . $fixed1 . "\n";

// Method 2: Convert from UTF-8 to Windows-1251 and back
$fixed2 = @mb_convert_encoding($corruptedText, 'Windows-1251', 'UTF-8');
if ($fixed2 !== false) {
    $fixed2 = @mb_convert_encoding($fixed2, 'UTF-8', 'Windows-1251');
    echo "Method 2 (Windows-1251 conversion): " . $fixed2 . "\n";
} else {
    echo "Method 2 failed\n";
}

// Method 3: Try to decode as UTF-8 and then re-encode
$fixed3 = @mb_convert_encoding($corruptedText, 'UTF-8', 'UTF-8');
echo "Method 3 (UTF-8 re-encoding): " . $fixed3 . "\n";

// Method 4: Try ISO-8859-1 to UTF-8 conversion
$fixed4 = @mb_convert_encoding($corruptedText, 'UTF-8', 'ISO-8859-1');
echo "Method 4 (ISO-8859-1 conversion): " . $fixed4 . "\n";

// Method 5: Manual character-by-character replacement
echo "\n=== Manual character replacement ===\n";
$charMap = [
    'Рџ' => 'П',
    'СЂ' => 'р',
    'Рѕ' => 'о',
    'С„' => 'ф',
    'Рё' => 'и',
    'Р»' => 'л',
    'СЊ' => 'ь'
];

$fixed5 = $corruptedText;
foreach ($charMap as $corrupted => $fixed) {
    $fixed5 = str_replace($corrupted, $fixed, $fixed5);
}
echo "Method 5 (manual character replacement): " . $fixed5 . "\n";

// Method 6: Using iconv
echo "\n=== Using iconv ===\n";
$fixed6 = @iconv('UTF-8', 'UTF-8//IGNORE', $corruptedText);
echo "Method 6 (iconv): " . $fixed6 . "\n";

// Method 7: Using iconv with transliteration
$fixed7 = @iconv('UTF-8', 'UTF-8//TRANSLIT', $corruptedText);
echo "Method 7 (iconv with transliteration): " . $fixed7 . "\n";

echo "\n=== Hex dump for analysis ===\n";
echo "Corrupted text hex: " . bin2hex($corruptedText) . "\n";
echo "Expected text hex: " . bin2hex($expectedText) . "\n";

// Check encoding
echo "\n=== Encoding checks ===\n";
echo "Corrupted text encoding check: " . (mb_check_encoding($corruptedText, 'UTF-8') ? 'valid UTF-8' : 'invalid UTF-8') . "\n";
echo "Expected text encoding check: " . (mb_check_encoding($expectedText, 'UTF-8') ? 'valid UTF-8' : 'invalid UTF-8') . "\n";

?>