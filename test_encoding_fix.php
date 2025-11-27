<?php
// Test script to verify encoding fixes

// Load composer
require __DIR__ . '/vendor/autoload.php';

// Load our custom autoloader for services and handlers
require_once __DIR__ . '/custom_autoload.php';

// Load custome classes
require_once __DIR__ . '/Custome/DB.php';

echo "Testing encoding fixes...\n";

// Test UTF-8 encoding
$testString = 'category_УровеньДоступа_Управление';
echo "Original string: " . $testString . "\n";

// Test encoding conversion
$encodedString = mb_convert_encoding($testString, 'UTF-8', 'auto');
echo "Encoded string: " . $encodedString . "\n";

// Test if they match
if ($testString === $encodedString) {
    echo "Encoding test PASSED\n";
} else {
    echo "Encoding test FAILED\n";
}

// Test with corrupted string from logs
$corruptedString = 'category_РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°_РЈРїСЂР°РІР»РµРЅРёРµ';
echo "Corrupted string: " . $corruptedString . "\n";

// Try to fix corrupted string
$fixedString = mb_convert_encoding($corruptedString, 'UTF-8', 'auto');
echo "Fixed string: " . $fixedString . "\n";

echo "Test completed.\n";
?>