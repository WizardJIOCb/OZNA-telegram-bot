<?php

// Test the short callback format handling flow
$callbackData = 'cat_ТабЭлОб_РабПоГр';
$actualCategory = 'Табель_РабПоГр_ЭлектрОб'; // This is what extractCategoryFromCallback should return

echo "Testing callback data: $callbackData\n";
echo "Testing actual category: $actualCategory\n\n";

// Test the condition at lines 167-174 in TabelHandler
$condition = (strpos($actualCategory, 'Табель_РабПоГр_ЭлектрОб') === 0 ||
    $callbackData === 'cat_ТабЭлОб_РабПоГр' ||
    strpos($actualCategory, 'Табель_ФормИД_ЭлектрОб') === 0 ||
    $callbackData === 'cat_ТабЭлОб_ФормИД' ||
    strpos($actualCategory, 'Табель_ПодтВех_ЭлектрОб') === 0 ||
    $callbackData === 'cat_ТабЭлОб_ПодтВех' ||
    $callbackData === 'cat_ТабЭлОб_Проч');

echo "Condition result: " . ($condition ? 'true' : 'false') . "\n";

// Check which part of the condition matches
echo "\nChecking individual conditions:\n";
echo "strpos(\$actualCategory, 'Табель_РабПоГр_ЭлектрОб') === 0: " . (strpos($actualCategory, 'Табель_РабПоГр_ЭлектрОб') === 0 ? 'true' : 'false') . "\n";
echo "\$callbackData === 'cat_ТабЭлОб_РабПоГр': " . ($callbackData === 'cat_ТабЭлОб_РабПоГр' ? 'true' : 'false') . "\n";
echo "strpos(\$actualCategory, 'Табель_ФормИД_ЭлектрОб') === 0: " . (strpos($actualCategory, 'Табель_ФормИД_ЭлектрОб') === 0 ? 'true' : 'false') . "\n";
echo "\$callbackData === 'cat_ТабЭлОб_ФормИД': " . ($callbackData === 'cat_ТабЭлОб_ФормИД' ? 'true' : 'false') . "\n";
echo "strpos(\$actualCategory, 'Табель_ПодтВех_ЭлектрОб') === 0: " . (strpos($actualCategory, 'Табель_ПодтВех_ЭлектрОб') === 0 ? 'true' : 'false') . "\n";
echo "\$callbackData === 'cat_ТабЭлОб_ПодтВех': " . ($callbackData === 'cat_ТабЭлОб_ПодтВех' ? 'true' : 'false') . "\n";
echo "\$callbackData === 'cat_ТабЭлОб_Проч': " . ($callbackData === 'cat_ТабЭлОб_Проч' ? 'true' : 'false') . "\n";

?>