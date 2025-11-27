<?php
// Targeted test script to fix the specific encoding issue

echo "Testing targeted encoding fixes...\n";

// Function to fix the specific double-encoding issue we're seeing
function fixCyrillicDoubleEncoding($str) {
    echo "Input string: " . $str . "\n";
    echo "Input bytes: " . bin2hex($str) . "\n";
    
    // The issue is that UTF-8 Cyrillic characters have been interpreted as Windows-1251 and re-encoded as UTF-8
    // For example, 'У' in UTF-8 is 0xD0 A3, but when interpreted as Windows-1251 and re-encoded as UTF-8,
    // it becomes a sequence of characters that represent those bytes
    
    // Try to convert from UTF-8 to Windows-1251 and then back to UTF-8
    $intermediate = @mb_convert_encoding($str, 'Windows-1251', 'UTF-8');
    echo "Intermediate (UTF-8 to Windows-1251): " . bin2hex($intermediate) . "\n";
    
    if ($intermediate !== false) {
        $fixed = @mb_convert_encoding($intermediate, 'UTF-8', 'Windows-1251');
        echo "Fixed (Windows-1251 to UTF-8): " . $fixed . "\n";
        echo "Fixed bytes: " . bin2hex($fixed) . "\n";
        return $fixed;
    }
    
    return $str;
}

// Test with corrupted string from logs
$corruptedString = 'category_РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°_РЈРїСЂР°РІР»РµРЅРёРµ';
echo "Testing with corrupted string:\n";
$fixedString = fixCyrillicDoubleEncoding($corruptedString);
echo "Final result: " . $fixedString . "\n";

// Test strpos with the fixed string
$isCategoryAccessLevel = strpos($fixedString, 'category_УровеньДоступа') === 0;
echo "Does it match category_УровеньДоступа: " . ($isCategoryAccessLevel ? 'true' : 'false') . "\n";

// Let's also try a different approach - manually fixing the known pattern
function fixKnownPattern($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        $str = str_replace($corrupted, $fixed, $str);
    }
    
    return $str;
}

echo "\nTesting known pattern fix:\n";
$patternFixed = fixKnownPattern($corruptedString);
echo "Pattern fixed result: " . $patternFixed . "\n";
$isCategoryAccessLevel2 = strpos($patternFixed, 'category_УровеньДоступа') === 0;
echo "Does it match category_УровеньДоступа: " . ($isCategoryAccessLevel2 ? 'true' : 'false') . "\n";

echo "\nTest completed.\n";
?>