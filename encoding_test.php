<?php
// Test to check UTF-8 encoding without dependencies

$test = "category_Админка_ЗапросДоступ";
echo "Original string: " . $test . "\n";
echo "String length: " . strlen($test) . " bytes\n";
echo "Multibyte length: " . mb_strlen($test, 'UTF-8') . " characters\n";

// Check if it's valid UTF-8
$isUtf8 = mb_check_encoding($test, 'UTF-8');
echo "Is valid UTF-8: " . ($isUtf8 ? 'Yes' : 'No') . "\n";

// Check for common corruption patterns
$corruptionIndicators = array('Ð', 'Ð‘', 'Ð’', 'Ð“');
$hasCorruption = false;
foreach ($corruptionIndicators as $indicator) {
    if (strpos($test, $indicator) !== false) {
        echo "Found corruption indicator: " . $indicator . "\n";
        $hasCorruption = true;
    }
}

echo "Has visible corruption: " . ($hasCorruption ? 'Yes' : 'No') . "\n";

// Test str_starts_with function
function str_starts_with_custom($haystack, $needle) {
    return strpos($haystack, $needle) === 0;
}

$startsWith = str_starts_with_custom($test, 'category_Админка');
echo "Starts with 'category_Админка': " . ($startsWith ? 'Yes' : 'No') . "\n";

echo "Test completed.\n";
?>