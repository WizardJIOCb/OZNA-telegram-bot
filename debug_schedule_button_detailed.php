<?php
// Detailed debug script to identify the exact issue with the "Работы по графику" button

echo "DETAILED DEBUGGING 'РАБОТЫ ПО ГРАФИКУ' BUTTON ISSUE\n";
echo "==================================================\n\n";

// Simulate the flow when user clicks the button
echo "1. User clicks 'Работы по графику' button in electrical equipment menu\n";
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "   Callback data sent: {$callbackData}\n";

// Simulate callback mapping in TabelHandler extractCategoryFromCallback method
echo "\n2. TabelHandler extractCategoryFromCallback method processes callback\n";
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
    'cat_ТабЭлОб_Проч' => 'Табель_Прочее_Электрооборудование',
    // Handle confirmation callbacks as well
    'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РаботыПоГрафику_Электрооборудование_yes',
    'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормированиеИД_Электрооборудование_yes',
    'cat_ТабЭлОб_ИзмСхПд_yes' => 'Табель_ИзменениеСхемПодключения_Электрооборудование_yes',
    'cat_ТабЭлОб_МонтКбЛн_yes' => 'Табель_МонтажКабельныхЛиний_Электрооборудование_yes',
    'cat_ТабЭлОб_РаскКбЛн_yes' => 'Табель_РасключениеКабельныхЛиний_Электрооборудование_yes',
    'cat_ТабЭлОб_ДопРаб_yes' => 'Табель_ДопРаб_ЭлектрОб_yes',
    'cat_ТабЭлОб_ПодтВех_yes' => 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование_yes',
    'cat_ТабЭлОб_Проч_yes' => 'Табель_Прочее_Электрооборудование_yes'
];

$category = '';
if (isset($shortFormats[$callbackData])) {
    $category = $shortFormats[$callbackData];
    echo "   Mapped to category: {$category}\n";
} else {
    echo "   ERROR: Callback not found in mapping!\n";
    exit(1);
}

// Simulate handle method processing order
echo "\n3. TabelHandler handle method processes category\n";

// Check if this is an electrical equipment menu category
$electricalEquipmentMenus = [
    'Табель_Электрооборудование',
    'Табель_РаботыПоГрафику_Электрооборудование',
    'Табель_ФормированиеИД_Электрооборудование',
    'Табель_ИзменениеСхемПодключения_Электрооборудование',
    'Табель_МонтажКабельныхЛиний_Электрооборудование',
    'Табель_РасключениеКабельныхЛиний_Электрооборудование',
    'Табель_ДопРаб_ЭлектрОб',
    'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
    'Табель_Прочее_Электрооборудование'
];

$isElectricalEquipmentMenu = in_array($category, $electricalEquipmentMenus);
echo "   isElectricalEquipmentMenu: " . ($isElectricalEquipmentMenu ? 'true' : 'false') . "\n";

// Define activity types for validation (from TabelHandler)
$activityTypes = [
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
    'ДопРаб_ЭлектрОб',
    'ИзмСхемПодкл_ЭлектрОб',
    'МонтажКабЛиний_ЭлектрОб',
    'РасклКабЛиний_ЭлектрОб',
    'Прочее_Электрооборудование',
    // Electrical equipment main category
    'Электрооборудование'
];

// Check if this is an activity execution
$isActivityExecution = false;
if (str_contains($category, '_yes')) {
    // Extract the activity type from the category
    $pattern = '/^Табель_(.+)_yes$/';
    if (preg_match($pattern, $category, $matches)) {
        $activityType = $matches[1];
        $isActivityExecution = in_array($activityType, $activityTypes);
        echo "   isActivityExecution (with _yes): category={$category}, activityType={$activityType}, in_array=" . ($isActivityExecution ? 'true' : 'false') . "\n";
    }
} else {
    echo "   isActivityExecution: false (no _yes suffix)\n";
}

// Simulate handleOtherWorkTypes method
echo "\n4. handleOtherWorkTypes processes category\n";

// Check if this is the schedule works category
if ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || $callbackData === 'cat_ТабЭлОб_РабПоГр') {
    echo "   Matched 'Работы по графику' handling\n";
    
    // Simulate what should happen
    $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
    $projectCode = 'TEST-PROJECT';
    
    echo "   Stripped category: {$strippedCategory}\n";
    echo "   Project code: {$projectCode}\n";
    
    // Get confirmation message (from TabelEntry)
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
        
        echo "\n5. User clicks 'Да' to confirm\n";
        $confirmCallback = $confirmCallbackData; // 'cat_ТабЭлОб_РабПоГр_yes'
        echo "   Confirm callback: {$confirmCallback}\n";
        
        // Process confirmation callback
        if (isset($shortFormats[$confirmCallback])) {
            $confirmCategory = $shortFormats[$confirmCallback];
            echo "   Mapped confirm callback to category: {$confirmCategory}\n";
            
            // Check if this is an activity execution
            $isConfirmActivityExecution = false;
            if (str_contains($confirmCategory, '_yes')) {
                $pattern = '/^Табель_(.+)_yes$/';
                if (preg_match($pattern, $confirmCategory, $matches)) {
                    $activityType = $matches[1];
                    $isConfirmActivityExecution = in_array($activityType, $activityTypes);
                    echo "   Confirm isActivityExecution: category={$confirmCategory}, activityType={$activityType}, in_array=" . ($isConfirmActivityExecution ? 'true' : 'false') . "\n";
                }
            }
            
            if ($isConfirmActivityExecution) {
                echo "   Will process as activity execution\n";
                echo "   Extracted activity type: РаботыПоГрафику_Электрооборудование\n";
                echo "   Will call handleActivityExecution method\n";
            } else {
                echo "   ERROR: Confirm callback not recognized as activity execution\n";
            }
        } else {
            echo "   ERROR: Confirm callback not found in mapping!\n";
        }
        
        echo "\n6. Expected behavior:\n";
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
    echo "   Actual category: {$category}\n";
    echo "   Expected: Табель_РаботыПоГрафику_Электрооборудование\n";
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
echo "7. handleActivityExecution not handling electrical equipment activities correctly\n";