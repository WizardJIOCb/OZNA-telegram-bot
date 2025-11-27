<?php
// Debug script to identify issues with the "Работы по графику" button

echo "DEBUGGING 'РАБОТЫ ПО ГРАФИКУ' BUTTON ISSUE\n";
echo "========================================\n\n";

// Simulate the flow when user clicks the button
echo "1. User clicks 'Работы по графику' button in electrical equipment menu\n";
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "   Callback data sent: {$callbackData}\n";

// Simulate callback mapping in TabelHandler
echo "\n2. TabelHandler processes callback\n";
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

$category = '';
if (isset($shortFormats[$callbackData])) {
    $category = $shortFormats[$callbackData];
    echo "   Mapped to category: {$category}\n";
} else {
    echo "   ERROR: Callback not found in mapping!\n";
    exit(1);
}

// Simulate handleOtherWorkTypes method
echo "\n3. handleOtherWorkTypes processes category\n";

// Check if this is the schedule works category
if ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || $callbackData === 'cat_ТабЭлОб_РабПоГр') {
    echo "   Matched 'Работы по графику' handling\n";
    
    // Simulate what should happen
    $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
    $projectCode = 'TEST-PROJECT';
    
    echo "   Stripped category: {$strippedCategory}\n";
    echo "   Project code: {$projectCode}\n";
    
    // Get confirmation message
    $activityNames = [
        'РаботыПоГрафику_Электрооборудование' => 'Работы по графику (Электрооборудование)',
        'ФормированиеИД_Электрооборудование' => 'Формирование ИД (Электрооборудование)',
        'ПодтвДостВехи_ЭлектрОб' => 'Подтвердить достижение вехи (Электрооборудование)',
        'ИзмСхемПодкл_ЭлектрОб' => 'Изменение схем подключения (Электрооборудование)',
        'МонтажКабЛиний_ЭлектрОб' => 'Монтаж кабельных линий (Электрооборудование)',
        'РасклКабЛиний_ЭлектрОб' => 'Расключение кабельных линий (Электрооборудование)',
        'Прочее_Электрооборудование' => 'Прочее (Электрооборудование)'
    ];
    
    if (isset($activityNames[$strippedCategory])) {
        $activityName = $activityNames[$strippedCategory];
        $confirmationText = "<b>{$activityName}</b>, отправить запись в табель в проект <b>{$projectCode}</b>?";
        echo "   Confirmation text: {$confirmationText}\n";
        
        // Create confirmation keyboard
        $confirmCallbackData = "cat_ТабЭлОб_РабПоГр_yes";
        $confirmationKeyboard = [
            [
                ['text' => '✅ Да', 'callback_data' => $confirmCallbackData],
                ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
            ]
        ];
        
        echo "   Confirmation keyboard created:\n";
        echo "     - '✅ Да' button with callback: {$confirmationKeyboard[0][0]['callback_data']}\n";
        echo "     - '❌ Нет' button with callback: {$confirmationKeyboard[0][1]['callback_data']}\n";
        
        echo "\n4. Expected behavior:\n";
        echo "   - Show confirmation message to user\n";
        echo "   - Show 'Да'/'Нет' buttons\n";
        echo "   - If user clicks 'Да', process cat_ТабЭлОб_РабПоГр_yes callback\n";
        echo "   - Create timesheet entry\n";
        echo "   - Show success message\n";
        
    } else {
        echo "   ERROR: Activity name not found for {$strippedCategory}\n";
    }
} else {
    echo "   ERROR: Category not matched for schedule works handling\n";
}

echo "\nDEBUG COMPLETE\n";
echo "===============\n";
echo "If the button is not working, possible issues:\n";
echo "1. Callback data not being sent correctly from Telegram\n";
echo "2. Mapping not working in extractCategoryFromCallback method\n";
echo "3. handleOtherWorkTypes not matching the category correctly\n";
echo "4. Confirmation message or keyboard not being sent properly\n";
echo "5. User not seeing the confirmation buttons\n";
echo "6. Yes/No callbacks not being processed correctly\n";