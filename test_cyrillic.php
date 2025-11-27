<?php
// Test script to see what corrupted Cyrillic characters look like
$original = 'Табель_Электрооборудование';
echo "Original: " . $original . "\n";
echo "Original hex: " . bin2hex($original) . "\n";

// Try to simulate what corrupted Cyrillic might look like
$corrupted = iconv('UTF-8', 'CP1251', $original);
echo "As CP1251: " . $corrupted . "\n";
echo "As CP1251 hex: " . bin2hex($corrupted) . "\n";

// Try to convert back
$converted = iconv('CP1251', 'UTF-8', $corrupted);
echo "Converted back: " . $converted . "\n";
echo "Converted back hex: " . bin2hex($converted) . "\n";

// Check what the current pattern in CallbackHandlerFactory looks like
$pattern = 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ';
echo "Current pattern: " . $pattern . "\n";
echo "Current pattern hex: " . bin2hex($pattern) . "\n";
?>