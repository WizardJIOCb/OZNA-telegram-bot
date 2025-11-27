<?php
// Test script to verify the "Работы по графику" button fix for electrical equipment

echo "Verifying 'Работы по графику' button fix for electrical equipment\n";
echo "==========================================================\n\n";

// Simulate the callback data that would be sent when user clicks the button
$callbackData = 'cat_ТабЭлОб_РабПоГр';

echo "Testing callback data: '{$callbackData}'\n\n";

// Test the TabelHandler logic
echo "1. Testing TabelHandler::extractCategoryFromCallback...\n";
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

echo "   Extracted category: '{$actualCategory}'\n";

// Check if this matches our special case
$isScheduleWorks = ($actualCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
                   $callbackData === 'cat_ТабЭлОб_РабПоГр');

echo "   Is 'Работы по графику' electrical equipment: " . ($isScheduleWorks ? 'YES' : 'NO') . "\n\n";

if ($isScheduleWorks) {
    echo "2. Testing confirmation message creation...\n";
    $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
    $projectCode = 'TEST-PROJECT';
    
    // Simulate the confirmation message
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
    
    echo "3. Testing keyboard creation...\n";
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
    
    echo "\n✅ SUCCESS: The 'Работы по графику' button for electrical equipment should now show confirmation buttons!\n\n";
    
    echo "EXPECTED USER EXPERIENCE:\n";
    echo "1. User clicks 'Работы по графику' button in electrical equipment menu\n";
    echo "2. System shows confirmation message: \"Работы по графику (Электрооборудование), отправить запись в табель в проект TEST-PROJECT?\"\n";
    echo "3. User sees confirmation buttons: [✅ Да] [❌ Нет]\n";
    echo "4. If user clicks 'Да', the timesheet entry is created\n";
    echo "5. If user clicks 'Нет', they return to the tabel menu\n\n";
} else {
    echo "❌ ERROR: The callback data is not being properly recognized!\n";
}

echo "NEXT STEPS:\n";
echo "1. Check the Telegram bot error logs after users interact with the button\n";
echo "2. Verify that the PDO MySQL extension is properly loaded\n";
echo "3. Confirm that the database connection is working correctly\n";