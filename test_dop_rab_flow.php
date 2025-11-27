<?php
/**
 * Test script for "Дополнительные работы" flow
 */

echo "Testing 'Дополнительные работы' flow...\n\n";

// Simulate the callback data
$callbackData = 'cat_ТабЭлОб_ДопРаб';
echo "1. Original callback data: {$callbackData}\n";

// Simulate the extractCategoryFromCallback method
function extractCategoryFromCallback(string $callbackData): string
{
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for electrical equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_РабПоГр' => 'Табель_РаботыПоГрафику_Электрооборудование',
            'cat_ТабЭлОб_ФормИД' => 'Табель_ФормированиеИД_Электрооборудование',
            'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзменениеСхемПодключения_Электрооборудование',
            'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтажКабельныхЛиний_Электрооборудование',
            'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РасключениеКабельныхЛиний_Электрооборудование',
            'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
            'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
            'cat_ТабЭлОб_Проч' => 'Табель_Прочее_Электрооборудование'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

// Extract the actual category
$actualCategory = extractCategoryFromCallback($callbackData);
echo "2. Extracted actual category: {$actualCategory}\n";

// Simulate the handleOtherWorkTypes method
function handleOtherWorkTypes(string $category): string
{
    echo "3. Processing category: {$category}\n";
    
    // Handle works by schedule for electrical equipment (must come before general electrical equipment check)
    if ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || 
        $category === 'cat_ТабЭлОб_РабПоГр') {
        return "handleWorksBySchedule";
    }

    // Handle ID formation for electrical equipment (must come before general electrical equipment check)
    if ($category === 'Табель_ФормированиеИД_Электрооборудование' || 
        $category === 'cat_ТабЭлОб_ФормИД') {
        return "handleIDFormation";
    }

    // Handle milestone confirmation for electrical equipment (must come before general electrical equipment check)
    if ($category === 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование' || 
        $category === 'Табель_ПодтвДостВехи_ЭлектрОб' ||
        $category === 'cat_ТабЭлОб_ПодтВех') {
        return "handleMilestoneConfirmation";
    }

    // Handle additional works for electrical equipment (must come before general electrical equipment check)
    if ($category === 'Табель_ДопРаб_ЭлектрОб' || 
        $category === 'Табель_ДополнительныеРаботы_Электрооборудование' ||
        $category === 'cat_ТабЭлОб_ДопРаб') {
        echo "   ✅ Matched additional works condition\n";
        return "handleAdditionalWorksElectricalMenu";
    }

    // Handle scheme changes for electrical equipment (must come before general electrical equipment check)
    if ($category === 'Табель_ИзменениеСхемПодключения_Электрооборудование' || 
        $category === 'Табель_ИзмСхемПодкл_ЭлектрОб' ||
        $category === 'cat_ТабЭлОб_ИзмСхПд') {
        return "handleSchemeChanges";
    }

    // Handle cable installation for electrical equipment (must come before general electrical equipment check)
    if ($category === 'Табель_МонтажКабельныхЛиний_Электрооборудование' || 
        $category === 'Табель_МонтажКабЛиний_ЭлектрОб' ||
        $category === 'cat_ТабЭлОб_МонтКбЛн') {
        return "handleCableInstallation";
    }

    // Handle cable wiring for electrical equipment (must come before general electrical equipment check)
    if ($category === 'Табель_РасключениеКабельныхЛиний_Электрооборудование' || 
        $category === 'Табель_РасклКабЛиний_ЭлектрОб' ||
        $category === 'cat_ТабЭлОб_РаскКбЛн') {
        return "handleCableWiring";
    }

    // Handle other works for electrical equipment - prompt for custom input (must come before general electrical equipment check)
    if ($category === 'Табель_Прочее_Электрооборудование' || 
        $category === 'cat_ТабЭлОб_Проч') {
        return "handleOtherWorks";
    }

    // Handle electrical equipment menu with new short callback format (general check - must come AFTER specific checks)
    if ($category === 'Табель_Электрооборудование' || 
        $category === 'cat_ТабЭлОб' ||
        $category === 'cat_ТабЭлОб_Наз') {
        return "handleElectricalEquipmentMenu";
    }
    
    // Handle back button from additional works electrical equipment menu
    if ($category === 'Табель_ЭлектрОб') {
        return "handleElectricalEquipmentMenu";
    }

    // Handle back button from electrical equipment menu
    if ($category === 'Табель_ПусконаладочныеРаботы') {
        return "handleCommissioningWorksMenu";
    }
    
    // Handle electrical equipment menu - This check must come BEFORE other checks
    // Use strict comparison and also check for exact match after 'Табель_'
    if ($category === 'Табель_Электрооборудование' || 
        (strpos($category, 'Табель_') === 0 && substr($category, 8) === 'Электрооборудование')) {
        return "handleElectricalEquipmentMenu";
    }
    
    // Additional check for electrical equipment in category
    if (strpos($category, 'Электрооборудование') !== false) {
        // If it contains электрооборудование, handle it as electrical equipment
        if (strpos($category, 'Табель_Электрооборудование') !== false || $category === 'Электрооборудование') {
            return "handleElectricalEquipmentMenu";
        }
    }
    
    // Other conditions...
    if ($category === 'Табель_Пусконаладка') {
        return "handleCommissioningDateSelection";
    }
    
    if ($category === 'Табель_Шеф_монтаж') {
        return "handleSupervisionDateSelection";
    }
    
    // Handle Mobile Brigade
    if ($category === 'Табель_Мобильная_Бригада') {
        return "handleMobileBrigadeDateSelection";
    }
    
    // Handle Current Activity (Текущая деятельность)
    if ($category === 'Табель_Текущая') {
        return "handleCurrentActivityDateSelection";
    }
    
    // Handle commissioning works menu
    if ($category === 'Табель_ПусконаладочныеРаботы') {
        return "handleCommissioningWorksMenu";
    }
    
    // Categories that should show submenus instead of confirmation
    $submenuCategories = [
        'Электрооборудование',
        'ПожарнойСигнализации',
        'ОтоплениеИВентиляции',
        'ТехнОборудование',
        'Метрология',
        'КомплОпробование',
        'РежимнаяНаладка'
    ];
    
    // Handle work type buttons that appear after date and project selection
    // These are the activity types that should be confirmed
    $strippedCategory = str_replace('Табель_', '', $category);
    
    // Activity types array (simplified)
    $activityTypes = [
        'ДопРаб_ЭлектрОб',  // This is in the array
        'РаботыПоГрафику_Электрооборудование',
        'ФормированиеИД_Электрооборудование'
    ];
    
    // Only treat as activity confirmation if it's not a submenu category
    if (in_array($strippedCategory, $activityTypes) && !in_array($strippedCategory, $submenuCategories)) {
        return "handleActivityConfirmation";
    }
    
    return "unknownCommand";
}

$result = handleOtherWorkTypes($actualCategory);
echo "4. Result: {$result}\n";

if ($result === "handleAdditionalWorksElectricalMenu") {
    echo "✅ Correctly handled as additional works\n";
} else {
    echo "❌ Incorrectly handled as: {$result}\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test complete!\n";
echo str_repeat("=", 50) . "\n";