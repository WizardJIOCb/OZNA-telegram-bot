<?php
// Script to analyze the actual callback data that might be received
echo "Analyzing callback data patterns\n";

// The correct UTF-8 representation
$correct = 'category_Табель_Электрооборудование';
echo "Correct UTF-8: $correct\n";
echo "Correct UTF-8 hex: " . bin2hex($correct) . "\n\n";

// Let's try to identify what the actual corrupted pattern might be
// by looking at the hex representation of the corrupted data
$corrupted_hex = '63617465676f72795fd0a0d19ed0a0c2b0d0a0c2b1d0a0c2b5d0a0c2bbd0a1d08a5fd0a0c2add0a0c2bbd0a0c2b5d0a0d194d0a1e2809ad0a1d082d0a0d195d0a0d195d0a0c2b1d0a0d195d0a1d082d0a1d193d0a0d291d0a0d195d0a0d086d0a0c2b0d0a0d085d0a0d191d0a0c2b5';
$corrupted = hex2bin($corrupted_hex);
echo "Corrupted pattern from test: $corrupted\n";
echo "Corrupted pattern hex: " . bin2hex($corrupted) . "\n\n";

// Try to decode the corrupted pattern
$decoded_corrupted = mb_convert_encoding($corrupted, 'UTF-8', 'auto');
echo "Decoded corrupted pattern: $decoded_corrupted\n";
echo "Decoded corrupted pattern hex: " . bin2hex($decoded_corrupted) . "\n\n";

// Let's try a different approach - let's create a pattern that matches what we see
// The corrupted pattern seems to be double-encoded UTF-8
$double_encoded = mb_convert_encoding($correct, 'UTF-8', 'UTF-8');
echo "Double encoded: $double_encoded\n";
echo "Double encoded hex: " . bin2hex($double_encoded) . "\n\n";

// Try to fix double encoding
$fixed = mb_convert_encoding($double_encoded, 'UTF-8', 'auto');
echo "Fixed: $fixed\n";
echo "Fixed hex: " . bin2hex($fixed) . "\n\n";

// Check if they match
echo "Match: " . ($correct === $fixed ? 'Yes' : 'No') . "\n\n";

// Let's try to create the exact pattern that might be coming from Telegram
// Based on the logs, it seems like the data might be coming with a different encoding
$telegram_pattern = 'category_Табель_Электрооборудование'; // This is what should come from Telegram
$telegram_encoded = mb_convert_encoding($telegram_pattern, 'UTF-8', 'UTF-8'); // Double encode it
echo "Telegram encoded pattern: $telegram_encoded\n";
echo "Telegram encoded pattern hex: " . bin2hex($telegram_encoded) . "\n\n";

// Try to decode the Telegram pattern
$telegram_decoded = mb_convert_encoding($telegram_encoded, 'UTF-8', 'auto');
echo "Telegram decoded pattern: $telegram_decoded\n";
echo "Telegram decoded pattern hex: " . bin2hex($telegram_decoded) . "\n\n";

// Check if they match
echo "Telegram pattern match: " . ($telegram_pattern === $telegram_decoded ? 'Yes' : 'No') . "\n\n";

// Let's try to create a replacement pattern that would fix this
$replacement_pattern = [
    bin2hex($telegram_encoded) => $correct
];
echo "Replacement pattern: " . json_encode($replacement_pattern) . "\n";
?>