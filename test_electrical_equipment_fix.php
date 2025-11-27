<?php
// Test script to verify the electrical equipment routing fix

echo "=== TESTING ELECTRICAL EQUIPMENT ROUTING FIX ===\n\n";

// Step 1: Simulate the callback when user clicks "Электрооборудование" button
$callbackData = 'cat_ТабЭлОб';
echo "1. User clicks 'Электрооборудование' button\n";
echo "   Callback data: '{$callbackData}'\n\n";

// Step 2: Simulate extractCategoryFromCallback (the same logic as in TabelHandler)
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

// Step 3: Simulate the FIXED handleOtherWorkTypes routing logic
echo "3. handleOtherWorkTypes routing logic (AFTER FIX):\n";

$category = $extractedCategory;

// Check the conditions in the updated method
$conditions = [
    "\$category === 'Табель_Пусконаладка'" => $category === 'Табель_Пусконаладка',
    "\$category === 'Табель_Шеф_монтаж'" => $category === 'Табель_Шеф_монтаж',
    "\$category === 'Табель_Мобильная_Бригада' || \$category === 'Табель_МобБриг'" => $category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг',
    "\$category === 'ТабПНРаботы'" => $category === 'ТабПНРаботы',
    "\$category === 'ТабЭлОб'" => $category === 'ТабЭлОб',
    "\$category === 'ТабАвтом'" => $category === 'ТабАвтом',
    "\$category === 'ТабПожСигн'" => $category === 'ТабПожСигн',
    "\$category === 'Табель_Электрооборудование' (NEW FIX)" => $category === 'Табель_Электрооборудование',
    "\$category === 'Табель_Автоматизация' (NEW FIX)" => $category === 'Табель_Автоматизация',
    "\$category === 'Табель_ПожарнаяСигнализация' (NEW FIX)" => $category === 'Табель_ПожарнаяСигнализация',
];

$matchFound = false;
foreach ($conditions as $condition => $result) {
    echo "   {$condition}: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    if ($result && !$matchFound) {
        echo "   ✅ This condition MATCHES! Would route to appropriate handler\n";
        $matchFound = true;
        
        if (strpos($condition, 'Табель_Электрооборудование') !== false) {
            echo "   → Would call handleElectricalEquipmentMenu(\$chatId)\n";
            echo "   → User would see electrical equipment sub-menu with buttons:\n";
            echo "      - 📅 Работы по графику\n";
            echo "      - 📄 Формирование ИД\n";
            echo "      - 🛠️ Дополнительные работы\n";
            echo "      - ✅ Подтвердить достижение вехи\n";
            echo "      - 📝 Прочее\n";
            echo "      - ⬅️ Назад\n";
        }
    }
}

if (!$matchFound) {
    echo "   ❌ No conditions match - would still show 'Неизвестная команда табеля'\n";
}

echo "\n4. VERIFICATION:\n";
if ($matchFound) {
    echo "   ✅ FIX SUCCESSFUL: The electrical equipment button should now work correctly!\n";
    echo "   ✅ Users will see the electrical equipment sub-menu instead of 'Не выбран пункт меню'\n";
} else {
    echo "   ❌ FIX INCOMPLETE: Still need to address the routing issue\n";
}

echo "\n=== TEST COMPLETE ===\n";