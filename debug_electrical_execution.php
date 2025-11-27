<?php
// Debug script to test electrical equipment activity execution flow

echo "Debugging electrical equipment activity execution flow\n\n";

// Test the callback data processing
$callbackData = 'cat_ТабЭлОб_РабПоГр_yes';
echo "Testing callbackData: {$callbackData}\n";

// Test isActivityExecution method logic
echo "\n1. Testing isActivityExecution method:\n";

// Check if it matches short callback format for electrical equipment
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $callbackData, $matches)) {
    echo "✅ Matches short callback format for electrical equipment\n";
    $shortActivityKey = $matches[1];
    echo "Short activity key: {$shortActivityKey}\n";
    
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
            echo "✅ isActivityExecution should return true\n";
        } else {
            echo "❌ Activity type NOT found in activityTypes array\n";
            echo "❌ isActivityExecution would return false\n";
        }
    } else {
        echo "❌ No mapping found for short activity key: {$shortActivityKey}\n";
    }
} else {
    echo "❌ Does not match short callback format\n";
}

// Test handleActivityExecution method logic
echo "\n2. Testing handleActivityExecution method:\n";

// Extract activity type using regex
$activityType = '';

// Handle short callback format for electrical equipment FIRST
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $callbackData, $matches)) {
    echo "✅ Matches short callback format for electrical equipment in handleActivityExecution\n";
    $shortActivityKey = $matches[1];
    echo "Short activity key: {$shortActivityKey}\n";
    
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
    
    if (isset($shortToFullMap[$shortActivityKey])) {
        $activityType = $shortToFullMap[$shortActivityKey];
        echo "✅ Mapped to activity type: {$activityType}\n";
    } else {
        echo "❌ No mapping found for short activity key: {$shortActivityKey}\n";
    }
} else {
    echo "❌ Does not match short callback format in handleActivityExecution\n";
}

echo "\n3. Testing TabelEntry creation:\n";

// Simulate creating the message
$fio = "Test User";
$date = "01.01.2023";
$projectCode = "TEST001";
$idleReason = "";

echo "User FIO: {$fio}\n";
echo "Date: {$date}\n";
echo "Project Code: {$projectCode}\n";
echo "Activity Type: {$activityType}\n";

// Check if activity type is in activityNames array
$activityNames = [
    // New electrical equipment activity types
    'РабПоГр_ЭлектрОб' => '📅 Работы по графику (Электрооборудование)',
    'ФормИД_ЭлектрОб' => '📄 Формирование ИД (Электрооборудование)',
    'ПодтВех_ЭлектрОб' => '✅ Подтвердить достижение вехи (Электрооборудование)',
    'ИзмСхПд_ЭлектрОб' => '🔀 Изменение схемы подключения (Электрооборудование, Дополнительные работы)',
    'МонтКбЛн_ЭлектрОб' => '🔌 Прокладка кабеля (Электрооборудование, Дополнительные работы)',
    'РаскКбЛн_ЭлектрОб' => '📊 Подключение кабеля (Электрооборудование, Дополнительные работы)',
    'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)'
];

if (isset($activityNames[$activityType])) {
    $activityText = $activityNames[$activityType];
    echo "✅ Activity text found: {$activityText}\n";
    
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
    echo "❌ Activity text NOT found for activity type: {$activityType}\n";
}

echo "\n4. Testing DatabaseService and MessageService flow:\n";
echo "The addUserTabel method should insert the message into the database\n";
echo "Then sendToHistoryChatByFunctionalDirection should send the message to the appropriate group\n";
?>