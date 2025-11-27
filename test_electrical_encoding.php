<?php
// Test script to understand encoding issues with electrical equipment callback data
echo "Testing electrical equipment callback data encoding\n";

// The correct UTF-8 representation
$correct = 'category_Табель_Электрооборудование';
echo "Correct UTF-8: $correct\n";
echo "Correct UTF-8 hex: " . bin2hex($correct) . "\n";

// Simulate what might happen with double encoding
$double_encoded = mb_convert_encoding($correct, 'UTF-8', 'UTF-8');
echo "Double encoded: $double_encoded\n";
echo "Double encoded hex: " . bin2hex($double_encoded) . "\n";

// Try to fix double encoding
$fixed = mb_convert_encoding($double_encoded, 'UTF-8', 'auto');
echo "Fixed: $fixed\n";
echo "Fixed hex: " . bin2hex($fixed) . "\n";

// Check if they match
echo "Match: " . ($correct === $fixed ? 'Yes' : 'No') . "\n";

// Check what we have in the CallbackHandlerFactory
$pattern = 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ';
echo "Pattern in CallbackHandlerFactory: $pattern\n";
echo "Pattern hex: " . bin2hex($pattern) . "\n";

// Try to decode the pattern
$decoded_pattern = mb_convert_encoding($pattern, 'UTF-8', 'auto');
echo "Decoded pattern: $decoded_pattern\n";
echo "Decoded pattern hex: " . bin2hex($decoded_pattern) . "\n";

// Check if this matches our correct string
echo "Pattern matches correct: " . ($correct === $decoded_pattern ? 'Yes' : 'No') . "\n";

// Test the strpos check
echo "strpos check for 'category_Табель_Электрооборудование': " . (strpos($pattern, 'category_Табель_Электрооборудование') === 0 ? 'true' : 'false') . "\n";
echo "strpos check for 'Электрооборудование': " . (strpos($pattern, 'Электрооборудование') !== false ? 'true' : 'false') . "\n";
?>