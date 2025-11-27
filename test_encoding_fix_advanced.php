<?php
// Advanced test script to verify encoding fixes

// Load composer
require __DIR__ . '/vendor/autoload.php';

// Load our custom autoloader for services and handlers
require_once __DIR__ . '/custom_autoload.php';

// Load custome classes
require_once __DIR__ . '/Custome/DB.php';

echo "Testing advanced encoding fixes...\n";

// Test with corrupted string from logs (double-encoded UTF-8)
$corruptedString = 'category_РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°_РЈРїСЂР°РІР»РµРЅРёРµ';
echo "Corrupted string: " . $corruptedString . "\n";
echo "Corrupted string bytes: " . bin2hex($corruptedString) . "\n";

// Try to detect and fix double-encoded UTF-8
function fixDoubleEncodedUtf8($str) {
    // First, try to detect if it's double-encoded
    $decoded = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
    if ($decoded === false) {
        // If iconv fails, try mb_convert_encoding
        $decoded = @mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    }
    
    // If that doesn't work, try to decode from Windows-1251 to UTF-8
    if ($decoded === false || $decoded === $str) {
        $decoded = @mb_convert_encoding($str, 'UTF-8', 'Windows-1251');
    }
    
    return $decoded ?: $str;
}

$fixedString = fixDoubleEncodedUtf8($corruptedString);
echo "Fixed string: " . $fixedString . "\n";

// Test with a proper UTF-8 string
$properString = 'category_УровеньДоступа_Управление';
echo "Proper string: " . $properString . "\n";

// Test our handler matching
function testHandlerMatching($callbackData) {
    echo "Testing handler matching for: " . $callbackData . "\n";
    
    // Check for category-based access level callbacks
    $isCategoryAccessLevel = strpos($callbackData, 'category_УровеньДоступа') === 0;
    echo "Is category access level: " . ($isCategoryAccessLevel ? 'true' : 'false') . "\n";
    
    // Check for new access level management callbacks
    $isAccessLevelManagement = (
        $callbackData === 'acc_lvl_users' ||
        $callbackData === 'acc_lvl_manage_users' ||
        strpos($callbackData, 'acc_lvl_user_details_') === 0 ||
        strpos($callbackData, 'acc_lvl_set_level_') === 0
    );
    echo "Is access level management: " . ($isAccessLevelManagement ? 'true' : 'false') . "\n";
    
    $canHandle = $isCategoryAccessLevel || $isAccessLevelManagement;
    echo "Can handle: " . ($canHandle ? 'true' : 'false') . "\n\n";
    
    return $canHandle;
}

// Test with proper string
testHandlerMatching($properString);

// Test with fixed string
testHandlerMatching($fixedString);

echo "Test completed.\n";
?>