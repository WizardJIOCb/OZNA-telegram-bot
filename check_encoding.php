<?php
echo "Checking if mbstring extension is available...\n";
if (extension_loaded('mbstring')) {
    echo "✅ mbstring extension is loaded\n";
    
    // Test encoding functions
    $test_string = 'Профиль';
    echo "Test string: " . $test_string . "\n";
    echo "String length: " . strlen($test_string) . "\n";
    echo "MB string length: " . mb_strlen($test_string, 'UTF-8') . "\n";
    echo "Is valid UTF-8: " . (mb_check_encoding($test_string, 'UTF-8') ? 'yes' : 'no') . "\n";
    
    // Test conversion
    $converted = mb_convert_encoding($test_string, 'UTF-8', 'auto');
    echo "After conversion: " . $converted . "\n";
    echo "Conversion is valid UTF-8: " . (mb_check_encoding($converted, 'UTF-8') ? 'yes' : 'no') . "\n";
} else {
    echo "❌ mbstring extension is NOT loaded\n";
}
?>