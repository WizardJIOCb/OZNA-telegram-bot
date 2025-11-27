<?php
// Debug script to simulate the complete electrical equipment "Работы по графику" flow

echo "DEBUG: Electrical Equipment 'Работы по графику' Flow\n";
echo "==================================================\n\n";

// Simulate the callback data
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "1. Received callback data: '{$callbackData}'\n";

// Simulate TabelHandler::extractCategoryFromCallback
echo "\n2. Extracting category from callback...\n";
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

echo "   Is 'Работы по графику' electrical equipment: " . ($isScheduleWorks ? 'YES' : 'NO') . "\n";

if ($isScheduleWorks) {
    echo "\n3. Processing 'Работы по графику' for electrical equipment...\n";
    
    // Simulate conversation data
    $conversation = new stdClass();
    $conversation->notes = [
        'project_code' => 'TEST-PROJECT-001'
    ];
    
    echo "   Project code from conversation: '{$conversation->notes['project_code']}'\n";
    
    // Simulate TabelEntry::getConfirmationMessage
    echo "\n4. Creating confirmation message...\n";
    $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
    $projectCode = $conversation->notes['project_code'];
    
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
    
    echo "   Confirmation text: \"{$confirmationText}\"\n";
    
    // Simulate MessageService::createConfirmationKeyboard
    echo "\n5. Creating confirmation keyboard...\n";
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
    
    // Simulate MessageService::sendMessage
    echo "\n6. Sending confirmation message...\n";
    $chatId = '123456789'; // Test chat ID
    
    echo "   Sending to chat ID: {$chatId}\n";
    echo "   Message text: " . mb_substr($confirmationText, 0, 50) . "...\n";
    echo "   Keyboard JSON: " . json_encode(['inline_keyboard' => $keyboard], JSON_UNESCAPED_UNICODE) . "\n";
    
    // In a real implementation, this would call the Telegram API
    echo "   Message sent: SUCCESS\n";
    
    echo "\n✅ COMPLETE FLOW EXECUTED SUCCESSFULLY!\n";
    echo "   The 'Работы по графику' button should show confirmation buttons.\n\n";
    
    echo "NEXT STEPS:\n";
    echo "1. Check the Telegram bot error logs for any issues\n";
    echo "2. Verify that the callback is reaching the TabelHandler\n";
    echo "3. Make sure the conversation object has the required data\n";
    
} else {
    echo "\n❌ ERROR: Callback data not recognized as 'Работы по графику' for electrical equipment!\n";
}

echo "\nDEBUG COMPLETE\n";