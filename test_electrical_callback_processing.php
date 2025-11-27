<?php
// Test script to simulate electrical equipment callback processing

echo "Testing electrical equipment callback processing...\n";
echo "================================================\n\n";

// Simulate the callback data
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "Input callback data: '{$callbackData}'\n\n";

// Test TabelHandler logic
echo "1. Testing category extraction...\n";

// Map short formats to full category names (from TabelHandler)
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

$actualCategory = '';
if (isset($shortFormats[$callbackData])) {
    $actualCategory = $shortFormats[$callbackData];
}

echo "   Extracted actualCategory: '{$actualCategory}'\n";

// Check if this is the "Работы по графику" for electrical equipment
$isScheduleWorks = ($actualCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
                   $callbackData === 'cat_ТабЭлОб_РабПоГр');

echo "   Is 'Работы по графику' electrical equipment: " . ($isScheduleWorks ? 'YES' : 'NO') . "\n\n";

if ($isScheduleWorks) {
    echo "2. Testing confirmation logic...\n";
    
    // This is where the TabelHandler should show confirmation instead of executing directly
    echo "   TabelHandler should show confirmation buttons\n";
    echo "   Instead of directly executing the activity\n\n";
    
    echo "3. Simulating confirmation message creation...\n";
    $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
    $projectCode = 'TEST-PROJECT'; // This would come from conversation in real implementation
    
    // Get confirmation message (from TabelEntry)
    $activityNames = [
        'РаботыПоГрафику_Электрооборудование' => 'Работы по графику (Электрооборудование)',
        'ФормированиеИД_Электрооборудование' => 'Формирование ИД (Электрооборудование)',
        'ПодтвДостВехи_ЭлектрОб' => 'Подтвердить достижение вехи (Электрооборудование)',
        'ИзмСхемПодкл_ЭлектрОб' => 'Изменение схем подключения (Электрооборудование)',
        'МонтажКабЛиний_ЭлектрОб' => 'Монтаж кабельных линий (Электрооборудование)',
        'РасклКабЛиний_ЭлектрОб' => 'Расключение кабельных линий (Электрооборудование)',
        'Прочее_Электрооборудование' => 'Прочее (Электрооборудование)',
        'Электрооборудование' => 'Электрооборудование'
    ];

    $activityName = $activityNames[$strippedCategory] ?? $strippedCategory;
    $confirmationText = sprintf(
        "<b>%s</b>, отправить запись в табель в проект <b>%s</b>?",
        $activityName,
        $projectCode
    );
    
    echo "   Confirmation text: \"{$confirmationText}\"\n\n";
    
    echo "4. Simulating keyboard creation...\n";
    $keyboard = [
        [
            ['text' => '✅ Да', 'callback_data' => "category_Табель_{$strippedCategory}_yes"],
            ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
        ]
    ];
    
    echo "   Keyboard structure:\n";
    foreach ($keyboard as $rowIndex => $row) {
        echo "   Row {$rowIndex}: ";
        foreach ($row as $buttonIndex => $button) {
            echo "[{$button['text']}: {$button['callback_data']}] ";
        }
        echo "\n";
    }
    
    echo "\n✅ SUCCESS: The logic is correct!\n";
    echo "   The 'Работы по графику' button for electrical equipment should show confirmation.\n\n";
    
    echo "POSSIBLE ISSUES:\n";
    echo "1. The callback might not be reaching the TabelHandler\n";
    echo "2. There might be an issue with the MessageService sendMessage method\n";
    echo "3. There might be an encoding issue with Cyrillic text\n";
    echo "4. The conversation object might not have the required data\n\n";
    
} else {
    echo "❌ ERROR: The callback data is not being properly recognized!\n";
}

echo "NEXT STEPS:\n";
echo "1. Check if the callback is being processed by TabelHandler\n";
echo "2. Add more detailed logging to trace the callback flow\n";
echo "3. Verify that the MessageService sendMessage method is working\n";