<?php
// Script to find the correct corrupted pattern
$correct = 'Табель_Электрооборудование';
echo "Correct: $correct\n";

// Simulate corruption by double encoding
$utf8_encoded = utf8_encode($correct);
echo "UTF8 encoded: $utf8_encoded\n";

// Try to create the corrupted pattern we see in logs
// Based on the pattern we see, it looks like it's been double-encoded
$corrupted = utf8_decode($correct);
echo "Corrupted: $corrupted\n";

// Let's try another approach - what if it was converted incorrectly?
$corrupted2 = iconv('UTF-8', 'ISO-8859-1//IGNORE', $correct);
echo "Corrupted2: $corrupted2\n";

// And back to UTF-8
$fixed = iconv('ISO-8859-1', 'UTF-8//IGNORE', $corrupted2);
echo "Fixed: $fixed\n";

// Check if they match
echo "Match: " . ($correct === $fixed ? 'Yes' : 'No') . "\n";

// Let's also check what the current pattern in the code looks like
$pattern = 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ';
echo "Pattern: $pattern\n";
$pattern_hex = bin2hex($pattern);
echo "Pattern hex: $pattern_hex\n";

// Try to decode it
$decoded_pattern = iconv('UTF-8', 'ISO-8859-1//IGNORE', $pattern);
echo "Decoded pattern: $decoded_pattern\n";
$decoded_pattern_hex = bin2hex($decoded_pattern);
echo "Decoded pattern hex: $decoded_pattern_hex\n";

// Try to convert back
$pattern_fixed = iconv('ISO-8859-1', 'UTF-8//IGNORE', $decoded_pattern);
echo "Pattern fixed: $pattern_fixed\n";

// Check if this matches our correct string
echo "Pattern matches correct: " . ($correct === $pattern_fixed ? 'Yes' : 'No') . "\n";

// Let's also check the full callback data pattern
$full_correct = 'category_Табель_Электрооборудование';
echo "Full correct: $full_correct\n";

$full_pattern = 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ';
echo "Full pattern: $full_pattern\n";

$full_decoded = iconv('UTF-8', 'ISO-8859-1//IGNORE', $full_pattern);
echo "Full decoded: $full_decoded\n";

$full_fixed = iconv('ISO-8859-1', 'UTF-8//IGNORE', $full_decoded);
echo "Full fixed: $full_fixed\n";

echo "Full matches correct: " . ($full_correct === $full_fixed ? 'Yes' : 'No') . "\n";
?>