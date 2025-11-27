<?php
// Debug script to test handleActivityExecution flow

echo "Debugging handleActivityExecution flow\n\n";

// Simulate the callback data
$category = 'cat_ТабЭлОб_РабПоГр_yes';
echo "Testing category: {$category}\n";

// Test the regex pattern for short callback format
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $category, $matches)) {
    echo "✅ Pattern matches!\n";
    echo "Matched group: {$matches[1]}\n";
    
    // Map short format to full activity type
    $shortToFullMap = [
        'РабПоГр' => 'РабПоГр_ЭлектрОб',
        'ФормИД' => 'ФормИД_ЭлектрОб',
        'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
        'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
        'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
        'ДопРаб' => 'ДопРаб_ЭлектрОб',
        'ПодтВех' => 'ПодтВех_ЭлектрОб',
        'Проч' => 'Прочее_ЭлектрОб'
    ];
    
    $shortActivityKey = $matches[1];
    echo "Short activity key: {$shortActivityKey}\n";
    
    if (isset($shortToFullMap[$shortActivityKey])) {
        $activityType = $shortToFullMap[$shortActivityKey];
        echo "✅ Mapped to activity type: {$activityType}\n";
        
        // Check if this activity type is in our activityTypes array
        $activityTypes = [
            // New electrical equipment activity types (short format)
            'РабПоГр_ЭлектрОб',
            'ФормИД_ЭлектрОб',
            'ПодтВех_ЭлектрОб',
            'ИзмСхПд_ЭлектрОб',
            'МонтКбЛн_ЭлектрОб',
            'РаскКбЛн_ЭлектрОб',
            'Прочее_ЭлектрОб',
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
            'Прочее_Автом'
        ];
        
        if (in_array($activityType, $activityTypes)) {
            echo "✅ Activity type found in activityTypes array\n";
            
            // Simulate creating the message
            $fio = "Test User";
            $date = "01.01.2023";
            $projectCode = "TEST001";
            $idleReason = "";
            
            // Create the message
            $activityText = $activityType; // Simplified for testing
            
            // For electrical equipment activities
            if (strpos($activityType, '_') !== false && strpos($activityType, 'ЭлектрОб') !== false) {
                $activityNames = [
                    'РабПоГр_ЭлектрОб' => '📅 Работы по графику (Электрооборудование)',
                    'ФормИД_ЭлектрОб' => '📄 Формирование ИД (Электрооборудование)',
                    'ПодтВех_ЭлектрОб' => '✅ Подтвердить достижение вехи (Электрооборудование)',
                    'ИзмСхПд_ЭлектрОб' => '🔀 Изменение схемы подключения (Электрооборудование, Дополнительные работы)',
                    'МонтКбЛн_ЭлектрОб' => '🔌 Прокладка кабеля (Электрооборудование, Дополнительные работы)',
                    'РаскКбЛн_ЭлектрОб' => '📊 Подключение кабеля (Электрооборудование, Дополнительные работы)',
                    'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)'
                ];
                
                $activityText = $activityNames[$activityType] ?? $activityType;
                echo "✅ Using activity name: {$activityText}\n";
            }
            
            $message = sprintf(
                "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
            
            echo "✅ Message created: {$message}\n";
            echo "\n🎉 The flow should work correctly!\n";
            echo "If the report is not being sent, the issue might be in:\n";
            echo "1. DatabaseService::addUserTabel method\n";
            echo "2. MessageService::sendToHistoryChatByFunctionalDirection method\n";
            echo "3. Telegram API call to send the message\n";
        } else {
            echo "❌ Activity type NOT found in activityTypes array\n";
        }
    } else {
        echo "❌ No mapping found for short activity key: {$shortActivityKey}\n";
    }
} else {
    echo "❌ Pattern does not match!\n";
}
?>