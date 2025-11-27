<?php
/**
 * Test the special case handling in TabelHandler
 */

echo "=== Testing Special Case Handling ===\n";

$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "Callback data: {$callbackData}\n";

// Check the special case condition
$containsElectro = strpos($callbackData, 'Электрооборудование') !== false;
echo "Contains 'Электрооборудование': " . ($containsElectro ? 'YES' : 'NO') . "\n";

// Check if it contains part of электрооборудование
$containsTabElOb = strpos($callbackData, 'ТабЭлОб') !== false;
echo "Contains 'ТабЭлОб': " . ($containsTabElOb ? 'YES' : 'NO') . "\n";

// This is the problematic condition
if (strpos($callbackData, 'Электрооборудование') !== false || 
    strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
    strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
    strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
    echo "❌ SPECIAL CASE WOULD TRIGGER: Would show electrical equipment menu instead of handling schedule works directly\n";
    echo "This would cause 'Неизвестная команда табеля' to appear\n";
} else {
    echo "✅ Special case would NOT trigger\n";
}

echo "\n=== Test Complete ===\n";
?>