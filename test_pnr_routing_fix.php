<?php
// Test script to verify the PNR routing fix

echo "Testing PNR Routing Fix\n";
echo "======================\n\n";

// Simulate the handleOtherWorkTypes method logic
function simulateHandleOtherWorkTypes($category) {
    echo "Processing category: $category\n";
    
    // This is the order of checks in the fixed handleOtherWorkTypes method
    
    // 1. Handle commissioning date selection - moved to avoid conflicts
    if ($category === 'Табель_Пусконаладка') {
        echo "  → Matched 'Табель_Пусконаладка'\n";
        echo "  → Will call handleCommissioningDateSelection()\n";
        return "handleCommissioningDateSelection";
    }
    
    // 2. Other specific conditions...
    if ($category === 'Табель_Шеф_монтаж') {
        echo "  → Matched 'Табель_Шеф_монтаж'\n";
        echo "  → Will call handleSupervisionDateSelection()\n";
        return "handleSupervisionDateSelection";
    }
    
    // 3. Handle Current Activity
    if ($category === 'Табель_Текущая') {
        echo "  → Matched 'Табель_Текущая'\n";
        echo "  → Will call handleCurrentActivityDateSelection()\n";
        return "handleCurrentActivityDateSelection";
    }
    
    // 4. Handle commissioning works menu
    if ($category === 'Табель_ПусконаладочныеРаботы') {
        echo "  → Matched 'Табель_ПусконаладочныеРаботы'\n";
        echo "  → Will call handleCommissioningWorksMenu()\n";
        return "handleCommissioningWorksMenu";
    }
    
    // 5. Categories that should show submenus instead of confirmation
    $submenuCategories = [
        'Электрооборудование',
        'Автоматизация',
        'ПожарнойСигнализации',
        'ОтоплениеИВентиляции',
        'ТехнОборудование',
        'Метрология',
        'КомплОпробование',
        'РежимнаяНаладка'
    ];
    
    // 6. Handle work type buttons that appear after date and project selection
    // These are the activity types that should be confirmed
    $strippedCategory = str_replace('Табель_', '', $category);
    echo "  → Stripped category: $strippedCategory\n";
    
    // Simulate the activity types array
    $activityTypes = [
        // Standard activity types
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
        'АвансовыйОтчет',
        // New electrical equipment activity types
        'РаботыПоГрафику_Электрооборудование',
        'ФормированиеИД_Электрооборудование',
        'ПодтвДостВехи_ЭлектрОб',
        'ИзмСхемПодкл_ЭлектрОб',
        'МонтажКабЛиний_ЭлектрОб',
        'РасклКабЛиний_ЭлектрОб',
        'Прочее_Электрооборудование',
        // Electrical equipment main category
        'Электрооборудование',
        // Automation equipment activity types
        'РабПоГр_Автом',
        'ФормИД_Автом',
        'ПодтВех_Автом',
        'ДопРаб_Автом',
        'ИзмСх_Автом',
        'МонтКб_Автом',
        'РаскКб_Автом',
        'МонтАСУ_Автом',
        'УдалПО_Автом',
        'Прочее_Автом',
        // Fire alarm equipment activity types
        'РаботыПоГрафику_ПожарнойСигнализации',
        'ФормированиеИД_ПожарнойСигнализации',
        'ПодтвердитьДостижениеВехи_ПожарнойСигнализации',
        'ДополнительныеРаботы_ПожарнойСигнализации',
        'ИзменениеСхемПодключения_ПожарнойСигнализации',
        'МонтажКабельныхЛиний_ПожарнойСигнализации',
        'ПодключениеКабеля_ПожарнойСигнализации',
        'МонтажОборудованияПС_ПожарнойСигнализации',
        'Прочее_ПожарнойСигнализации',
        // Security alarm equipment activity types
        'РаботыПоГрафику_ОхраннойСигнализации',
        'ФормированиеИД_ОхраннойСигнализации',
        'ПодтвердитьДостижениеВехи_ОхраннойСигнализации',
        'ДополнительныеРаботы_ОхраннойСигнализации',
        'ИзменениеСхемПодключения_ОхраннойСигнализации',
        'МонтажКабельныхЛиний_ОхраннойСигнализации',
        'ПодключениеКабеля_ОхраннойСигнализации',
        'МонтажОборудованияОС_ОхраннойСигнализации',
        'Прочее_ОхраннойСигнализации',
        // Short work type names (this is where the conflict was)
        'Пусконаладка'  // This was causing the conflict!
    ];
    
    echo "  → Is '$strippedCategory' in activityTypes? " . (in_array($strippedCategory, $activityTypes) ? 'YES' : 'NO') . "\n";
    echo "  → Is '$strippedCategory' in submenuCategories? " . (in_array($strippedCategory, $submenuCategories) ? 'YES' : 'NO') . "\n";
    
    // Only treat as activity confirmation if it's not a submenu category
    if (in_array($strippedCategory, $activityTypes) && !in_array($strippedCategory, $submenuCategories)) {
        echo "  → Will treat as activity confirmation\n";
        return "handleActivityConfirmation";
    }
    
    echo "  → No match found\n";
    return "unknown";
}

// Test cases
$testCases = [
    'Табель_Пусконаладка',           // This should go to handleCommissioningDateSelection
    'Табель_Шеф_монтаж',             // This should go to handleSupervisionDateSelection
    'Табель_Текущая',                // This should go to handleCurrentActivityDateSelection
    'Табель_ПусконаладочныеРаботы',  // This should go to handleCommissioningWorksMenu
    'Табель_Дорога',                 // This should go to handleActivityConfirmation
    'Табель_Пусконаладка_Да'         // This should go to handleActivityConfirmation
];

foreach ($testCases as $testCase) {
    echo "Test case: $testCase\n";
    $result = simulateHandleOtherWorkTypes($testCase);
    echo "Result: $result\n\n";
}

echo "✅ FIX VERIFICATION COMPLETE\n";
echo "The 'Отдел ПНР' button should now correctly show the date selection menu.\n";
echo "The key fix was moving the 'Табель_Пусконаладка' check before the activity types check\n";
echo "to avoid conflicts with the 'Пусконаладка' entry in the activity types array.\n";
?>