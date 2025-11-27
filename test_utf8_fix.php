<?php
// Test script to verify UTF-8 encoding fixes

echo "=== Testing UTF-8 Encoding Fixes ===\n";

// Test Russian text
$russianText = "Профиль_Просмотр";
echo "Original Russian text: " . $russianText . "\n";
echo "UTF-8 valid: " . (mb_check_encoding($russianText, 'UTF-8') ? 'Yes' : 'No') . "\n";

// Simulate corrupted text (what we were seeing in logs)
$corruptedText = "Ð\x9fÑ\x80Ð¾Ñ\x84Ð¸Ð»Ñ\x8c_Ð\x9fÑ\x80Ð¾Ñ\x81Ð¼Ð¾Ñ\x82Ñ\x80";
echo "Corrupted text: " . $corruptedText . "\n";

// Try to fix corrupted text
$fixedText = mb_convert_encoding($corruptedText, 'UTF-8', 'auto');
echo "After mb_convert_encoding: " . $fixedText . "\n";

// Test with specific encoding
$fixedText2 = mb_convert_encoding($corruptedText, 'UTF-8', 'UTF-8');
echo "After UTF-8 to UTF-8 conversion: " . $fixedText2 . "\n";

// Test callback data creation
$callbackData = "category_" . $russianText;
echo "Callback data: " . $callbackData . "\n";

// Test with AdminHandler pattern
$adminCallback = "category_Админка_Заявки";
echo "Admin callback: " . $adminCallback . "\n";
echo "UTF-8 valid: " . (mb_check_encoding($adminCallback, 'UTF-8') ? 'Yes' : 'No') . "\n";

echo "=== Test Complete ===\n";
?>