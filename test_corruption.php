<?php
// Test script to understand how Cyrillic characters get corrupted
echo "Testing Cyrillic corruption patterns\n";

// The correct UTF-8 representation
$correct = 'Электрооборудование';
echo "Correct UTF-8: $correct\n";
echo "Correct UTF-8 hex: " . bin2hex($correct) . "\n";

// Simulate corruption by converting to Windows-1251 and back incorrectly
$cp1251 = iconv('UTF-8', 'CP1251//IGNORE', $correct);
echo "As CP1251: $cp1251\n";
echo "As CP1251 hex: " . bin2hex($cp1251) . "\n";

// Now if this gets interpreted as UTF-8, it would be corrupted
$corrupted = iconv('CP1251', 'UTF-8//IGNORE', $cp1251);
echo "Corrupted: $corrupted\n";
echo "Corrupted hex: " . bin2hex($corrupted) . "\n";

// Try to decode the corrupted string
$decoded = mb_convert_encoding($corrupted, 'UTF-8', 'auto');
echo "Decoded: $decoded\n";
echo "Decoded hex: " . bin2hex($decoded) . "\n";

// Check what we currently have in CallbackHandlerFactory
$pattern = 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ';
echo "Current pattern: $pattern\n";
echo "Current pattern hex: " . bin2hex($pattern) . "\n";

// Check if they match
echo "Corrupted matches pattern: " . ($corrupted === $pattern ? 'Yes' : 'No') . "\n";

// Let's also check the full callback data
$full_correct = 'category_Табель_Электрооборудование';
echo "Full correct: $full_correct\n";
echo "Full correct hex: " . bin2hex($full_correct) . "\n";

$full_cp1251 = iconv('UTF-8', 'CP1251//IGNORE', $full_correct);
echo "Full as CP1251: $full_cp1251\n";
echo "Full as CP1251 hex: " . bin2hex($full_cp1251) . "\n";

$full_corrupted = iconv('CP1251', 'UTF-8//IGNORE', $full_cp1251);
echo "Full corrupted: $full_corrupted\n";
echo "Full corrupted hex: " . bin2hex($full_corrupted) . "\n";

echo "Full corrupted matches pattern: " . (strpos($full_corrupted, $pattern) !== false ? 'Yes' : 'No') . "\n";
?>