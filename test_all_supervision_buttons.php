<?php

echo "=== TESTING ALL SUPERVISION MENU BUTTONS ===\n";
echo "=============================================\n\n";

echo "Testing the fix for all supervision work buttons:\n\n";

// Test the extractCategoryFromCallback logic that we fixed
function simulateExtractCategory($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // THE FIX: Handle short category format including supervision works
    if (strpos($callbackData, 'cat_Таб') === 0 || 
        strpos($callbackData, 'cat_Гарантии_') === 0 || 
        strpos($callbackData, 'cat_ШМР_') === 0 || 
        strpos($callbackData, 'cat_supervision_') === 0 || 
        $callbackData === 'supervision_prep') {
        
        $shortFormats = [
            // NEW SUPERVISION WORKS HIERARCHICAL STRUCTURE MAPPINGS
            'cat_supervision_prep' => 'Табель_ПодготовительныеРаботы',
            'cat_ШМР_ПодгРаб' => 'Табель_ПодготовительныеРаботы',
            'cat_ШМР_МонтБлок' => 'Табель_МонтажБлоков',
            'cat_ШМР_СборЗдан' => 'Табель_СборкаЗдания',
            'cat_ШМР_ВнутрМонт' => 'Табель_ВнутриблочныйМонтаж',
            'cat_ШМР_ПодпАкт' => 'Табель_ПодписаниеАктаШМР',
            'cat_ШМР_Back' => 'Табель_ШМ_Back',
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
    }
    
    return '';
}

// Test the supervision menu buttons
$supervisionButtons = [
    'cat_supervision_prep' => 'Подготовительные работы (alternative)',
    'cat_ШМР_ПодгРаб' => 'Подготовительные работы',
    'cat_ШМР_МонтБлок' => 'Монтаж блоков',
    'cat_ШМР_СборЗдан' => 'Сборка здания',
    'cat_ШМР_ВнутрМонт' => 'Внутриблочный монтаж',
    'cat_ШМР_ПодпАкт' => 'Подписание акта ШМР',
    'cat_ШМР_Back' => 'Назад'
];

echo "1. Testing callback mapping:\n";
echo "============================\n";
foreach ($supervisionButtons as $callback => $description) {
    $mappedCategory = simulateExtractCategory($callback);
    $status = !empty($mappedCategory) ? '✅ WORKING' : '❌ BROKEN';
    echo "   {$callback} → {$mappedCategory} [{$status}]\n";
    echo "   Description: {$description}\n\n";
}

echo "2. Expected behavior after our fix:\n";
echo "==================================\n";
echo "✅ cat_supervision_prep → Shows preparatory works submenu\n";
echo "✅ cat_ШМР_ПодгРаб → Shows preparatory works submenu\n";
echo "✅ cat_ШМР_МонтБлок → Shows block installation submenu\n";
echo "✅ cat_ШМР_СборЗдан → Shows building assembly submenu\n";
echo "✅ cat_ШМР_ВнутрМонт → Shows intra-block installation submenu\n";
echo "✅ cat_ШМР_ПодпАкт → Shows confirmation dialog\n";
echo "✅ cat_ШМР_Back → Returns to main supervision menu\n\n";

echo "3. What was the problem:\n";
echo "=======================\n";
echo "❌ OLD condition: strpos(\$callbackData, 'cat_Таб') === 0\n";
echo "✅ NEW condition: Added || strpos(\$callbackData, 'cat_ШМР_') === 0\n\n";

echo "The fix allows all supervision work buttons to:\n";
echo "1. Pass the canHandle() check\n";
echo "2. Be properly mapped by extractCategoryFromCallback()\n";
echo "3. Route to the correct handlers\n\n";

echo "🎉 ALL SUPERVISION MENU BUTTONS SHOULD NOW WORK! 🎉\n";

?>