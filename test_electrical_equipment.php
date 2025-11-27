<?php

// Test the electrical equipment callback handling
$callbackData = 'category_Табель_Электрооборудование';

// Test the condition that should match
$condition1 = strpos($callbackData, 'Электрооборудование') !== false;
$condition2 = strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false;
$condition3 = strpos($callbackData, 'category_Табель_Электрооборудование') === 0;
$condition4 = strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0;

echo "Condition 1 (strpos(\$callbackData, 'Электрооборудование') !== false): " . ($condition1 ? 'true' : 'false') . "\n";
echo "Condition 2 (strpos(\$callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false): " . ($condition2 ? 'true' : 'false') . "\n";
echo "Condition 3 (strpos(\$callbackData, 'category_Табель_Электрооборудование') === 0): " . ($condition3 ? 'true' : 'false') . "\n";
echo "Condition 4 (strpos(\$callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0): " . ($condition4 ? 'true' : 'false') . "\n";

// Test the exact match condition
$condition5 = $callbackData === 'category_Табель_Электрооборудование';
echo "Condition 5 (\$callbackData === 'category_Табель_Электрооборудование'): " . ($condition5 ? 'true' : 'false') . "\n";

?>