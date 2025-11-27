<?php
// Debug script to understand why electrical equipment button shows "Не выбран пункт меню"

echo "=== DEBUGGING ELECTRICAL EQUIPMENT ROUTING ISSUE ===\n\n";

// Step 1: Simulate the callback when user clicks "Электрооборудование" button
$callbackData = 'cat_ТабЭлОб';
echo "1. User clicks 'Электрооборудование' button\n";
echo "   Callback data: '{$callbackData}'\n\n";

// Step 2: Simulate extractCategoryFromCallback
function extractCategoryFromCallback(string $callbackData): string
{
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_РабПоГр' => 'Табель_РабПоГр_ЭлектрОб',
            'cat_ТабЭлОб_ФормИД' => 'Табель_ФормИД_ЭлектрОб',
            'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзмСхПд_ЭлектрОб',
            'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтКбЛн_ЭлектрОб',
            'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РаскКбЛн_ЭлектрОб',
            'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
            'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтВех_ЭлектрОб',
            'cat_ТабЭлОб_Проч' => 'Табель_Прочее_ЭлектрОб',
            'cat_ТабАвтом' => 'Табель_Автоматизация',
            'cat_ТабПожСигн' => 'Табель_ПожарнаяСигнализация',
            // ... other mappings
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

$extractedCategory = extractCategoryFromCallback($callbackData);
echo "2. extractCategoryFromCallback result: '{$extractedCategory}'\n\n";

// Step 3: Simulate handleOtherWorkTypes routing logic
echo "3. handleOtherWorkTypes routing logic:\n";

$category = $extractedCategory;

// Check the exact conditions from the current method
$conditions = [
    "\$category === 'Табель_Пусконаладка'" => $category === 'Табель_Пусконаладка',
    "\$category === 'Табель_Шеф_монтаж'" => $category === 'Табель_Шеф_монтаж',
    "\$category === 'Табель_Мобильная_Бригада' || \$category === 'Табель_МобБриг'" => $category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг',
    "\$category === 'ТабПНРаботы'" => $category === 'ТабПНРаботы',
    "\$category === 'ТабЭлОб'" => $category === 'ТабЭлОб',
    "\$category === 'ТабАвтом'" => $category === 'ТабАвтом',
    "\$category === 'ТабПожСигн'" => $category === 'ТабПожСигн',
];

foreach ($conditions as $condition => $result) {
    echo "   {$condition}: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    if ($result) {
        echo "   ✅ This condition would match and route to specific handler\n";
        break;
    }
}

echo "\n4. ISSUE IDENTIFIED:\n";
echo "   The callback 'cat_ТабЭлОб' is extracted to 'Табель_Электрооборудование'\n";
echo "   But handleOtherWorkTypes expects 'ТабЭлОб' (without 'Табель_' prefix)\n";
echo "   This causes the callback to fall through to the 'Неизвестная команда табеля' default case\n\n";

echo "5. SOLUTION:\n";
echo "   Need to add routing for 'Табель_Электрооборудование' in handleOtherWorkTypes\n";
echo "   OR modify extractCategoryFromCallback to return 'ТабЭлОб' for 'cat_ТабЭлОб'\n\n";

echo "=== RECOMMENDED FIX ===\n";
echo "Add this condition to handleOtherWorkTypes BEFORE the activityTypes check:\n\n";
echo "if (\$category === 'Табель_Электрооборудование') {\n";
echo "    error_log(\"TabelHandler: routing to electrical equipment sub-menu\");\n";
echo "    return \$this->handleElectricalEquipmentMenu(\$chatId);\n";
echo "}\n";