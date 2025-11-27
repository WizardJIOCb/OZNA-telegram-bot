<?php
/**
 * Debug script for handleOtherWorkTypes method flow
 */

echo "Debugging handleOtherWorkTypes method flow...\n\n";

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

$category = extractCategoryFromCallback($callbackData);
echo "2. Extracted category: {$category}\n";

// Simulate the handleOtherWorkTypes method flow
echo "3. Processing in handleOtherWorkTypes:\n";

// Handle works by schedule for electrical equipment (must come before general electrical equipment check)
if ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || 
    $category === 'cat_ТабЭлОб_РабПоГр') {
    echo "   Matched: works by schedule for electrical equipment\n";
    exit;
}

// Handle ID formation for electrical equipment (must come before general electrical equipment check)
if ($category === 'Табель_ФормированиеИД_Электрооборудование' || 
    $category === 'cat_ТабЭлОб_ФормИД') {
    echo "   Matched: ID formation for electrical equipment\n";
    exit;
}

// Handle milestone confirmation for electrical equipment (must come before general electrical equipment check)
if ($category === 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование' || 
    $category === 'Табель_ПодтвДостВехи_ЭлектрОб' ||
    $category === 'cat_ТабЭлОб_ПодтВех') {
    echo "   Matched: milestone confirmation for electrical equipment\n";
    exit;
}

// Handle additional works for electrical equipment (must come before general electrical equipment check)
if ($category === 'Табель_ДопРаб_ЭлектрОб' || 
    $category === 'Табель_ДополнительныеРаботы_Электрооборудование' ||
    $category === 'cat_ТабЭлОб_ДопРаб') {
    echo "   ✅ Matched: additional works for electrical equipment\n";
    echo "   Should call handleAdditionalWorksElectricalMenu()\n";
    exit;
}

// Handle scheme changes for electrical equipment (must come before general electrical equipment check)
if ($category === 'Табель_ИзменениеСхемПодключения_Электрооборудование' || 
    $category === 'Табель_ИзмСхемПодкл_ЭлектрОб' ||
    $category === 'cat_ТабЭлОб_ИзмСхПд') {
    echo "   Matched: scheme changes for electrical equipment\n";
    exit;
}

// Handle cable installation for electrical equipment (must come before general electrical equipment check)
if ($category === 'Табель_МонтажКабельныхЛиний_Электрооборудование' || 
    $category === 'Табель_МонтажКабЛиний_ЭлектрОб' ||
    $category === 'cat_ТабЭлОб_МонтКбЛн') {
    echo "   Matched: cable installation for electrical equipment\n";
    exit;
}

// Handle cable wiring for electrical equipment (must come before general electrical equipment check)
if ($category === 'Табель_РасключениеКабельныхЛиний_Электрооборудование' || 
    $category === 'Табель_РасклКабЛиний_ЭлектрОб' ||
    $category === 'cat_ТабЭлОб_РаскКбЛн') {
    echo "   Matched: cable wiring for electrical equipment\n";
    exit;
}

// Handle other works for electrical equipment - prompt for custom input (must come before general electrical equipment check)
if ($category === 'Табель_Прочее_Электрооборудование' || 
    $category === 'cat_ТабЭлОб_Проч') {
    echo "   Matched: other works for electrical equipment\n";
    exit;
}

// Handle electrical equipment menu with new short callback format (general check - must come AFTER specific checks)
if ($category === 'Табель_Электрооборудование' || 
    $category === 'cat_ТабЭлОб' ||
    $category === 'cat_ТабЭлОб_Наз') {
    echo "   Matched: electrical equipment menu\n";
    exit;
}

// Handle back button from additional works electrical equipment menu
if ($category === 'Табель_ЭлектрОб') {
    echo "   Matched: back button from additional works electrical equipment menu\n";
    exit;
}

// Handle back button from electrical equipment menu
if ($category === 'Табель_ПусконаладочныеРаботы') {
    echo "   Matched: back button from electrical equipment menu\n";
    exit;
}

// Handle electrical equipment menu - This check must come BEFORE other checks
// Use strict comparison and also check for exact match after 'Табель_'
if ($category === 'Табель_Электрооборудование' || 
    (strpos($category, 'Табель_') === 0 && substr($category, 8) === 'Электрооборудование')) {
    echo "   Matched: electrical equipment menu (strict comparison)\n";
    exit;
}

// Additional check for electrical equipment in category
if (strpos($category, 'Электрооборудование') !== false) {
    echo "   Category contains 'Электрооборудование'\n";
    // If it contains электрооборудование, handle it as electrical equipment
    if (strpos($category, 'Табель_Электрооборудование') !== false || $category === 'Электрооборудование') {
        echo "   Would redirect to electrical equipment menu\n";
        exit;
    }
    // If it contains электрооборудование but is not exactly 'Табель_Электрооборудование', log the difference
    if ($category !== 'Табель_Электрооборудование') {
        echo "   Category is not exactly 'Табель_Электрооборудование', actual value: {$category}\n";
    }
}

// Other conditions...
echo "   Continuing with other conditions...\n";

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
echo "   Stripped category: {$strippedCategory}\n";

// Activity types array (simplified)
$activityTypes = [
    'ДопРаб_ЭлектрОб',  // This is in the array
    'РаботыПоГрафику_Электрооборудование',
    'ФормированиеИД_Электрооборудование'
];

if (in_array($strippedCategory, $activityTypes) && !in_array($strippedCategory, $submenuCategories)) {
    echo "   ❌ Would be treated as activity confirmation (this is the bug!)\n";
    echo "   Because '{$strippedCategory}' is in activityTypes but not in submenuCategories\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Analysis complete!\n";
echo "The debug shows that the 'Дополнительные работы' condition SHOULD match,\n";
echo "but if it's not matching in the actual code, there might be a subtle bug.\n";
echo str_repeat("=", 50) . "\n";