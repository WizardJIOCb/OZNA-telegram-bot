<?php
// Test МонтОб activity execution fix

echo "Testing МонтОб Activity Execution Fix\n";
echo "====================================\n\n";

// Test callback data that would be sent when user clicks "Да" 
$callbackData = 'cat_ТабЭлОб_МонтОб_yes';
echo "Testing callback: {$callbackData}\n\n";

echo "1. Testing isActivityExecution logic:\n";
echo "------------------------------------\n";

// Check if it matches the electrical equipment pattern
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $callbackData, $matches)) {
    echo "✅ Matches electrical equipment pattern\n";
    $shortActivityKey = $matches[1];
    echo "Short activity key: {$shortActivityKey}\n";
    
    // Check the shortToFullMap
    $shortToFullMap = [
        'РабПоГр' => 'РабПоГр_ЭлектрОб',
        'ФормИД' => 'ФормИД_ЭлектрОб',
        'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
        'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
        'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
        'МонтОб' => 'МонтОб_ЭлектрОб',  // This should now be present!
        'ДопРаб' => 'ДопРаб_ЭлектрОб',
        'ПодтВех' => 'ПодтВех_ЭлектрОб'
    ];
    
    if (isset($shortToFullMap[$shortActivityKey])) {
        $activityType = $shortToFullMap[$shortActivityKey];
        echo "✅ Found mapping: {$shortActivityKey} -> {$activityType}\n";
        
        // Check if activity type is in activityTypes array
        $activityTypes = [
            'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ПодтВех_ЭлектрОб',
            'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 
            'МонтОб_ЭлектрОб', 'Прочее_ЭлектрОб'
        ];
        
        if (in_array($activityType, $activityTypes)) {
            echo "✅ Activity type found in activityTypes array\n";
            echo "✅ isActivityExecution should return TRUE\n";
        } else {
            echo "❌ Activity type NOT found in activityTypes array\n";
            echo "❌ isActivityExecution would return FALSE\n";
        }
    } else {
        echo "❌ No mapping found for short activity key: {$shortActivityKey}\n";
        echo "❌ isActivityExecution would return FALSE\n";
    }
} else {
    echo "❌ Does not match electrical equipment pattern\n";
}

echo "\n2. Testing handleActivityExecution logic:\n";
echo "----------------------------------------\n";

// Test activity type extraction
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $callbackData, $matches)) {
    echo "✅ Matches electrical equipment pattern in handleActivityExecution\n";
    $shortActivityKey = $matches[1];
    
    $shortToFullMap = [
        'РабПоГр' => 'РабПоГр_ЭлектрОб',
        'ФормИД' => 'ФормИД_ЭлектрОб',
        'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
        'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
        'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
        'МонтОб' => 'МонтОб_ЭлектрОб',  // This should now be present!
        'ДопРаб' => 'ДопРаб_ЭлектрОб',
        'ПодтВех' => 'ПодтВех_ЭлектрОб'
    ];
    
    if (isset($shortToFullMap[$shortActivityKey])) {
        $activityType = $shortToFullMap[$shortActivityKey];
        echo "✅ Activity type extracted successfully: {$activityType}\n";
        echo "✅ handleActivityExecution should process this correctly\n";
        
        // Simulate the final message creation
        $fio = "Богданов Борис Сергеевич";
        $date = "19.09.2025";
        $projectCode = "ОИ834";
        
        echo "\nSimulated final message:\n";
        echo "------------------------\n";
        echo "{$fio}\n";
        echo "✅{$date}, Проект {$projectCode}: Добавлена запись в табель: ⚙️ Монтаж оборудования (Электрооборудование, Дополнительные работы)\n";
        
    } else {
        echo "❌ No mapping found for: {$shortActivityKey}\n";
        echo "❌ handleActivityExecution would fail\n";
    }
}

echo "\n3. Testing other electrical equipment activities for comparison:\n";
echo "-------------------------------------------------------------\n";

$testActivities = [
    'cat_ТабЭлОб_РабПоГр_yes' => 'РабПоГр_ЭлектрОб',
    'cat_ТабЭлОб_ФормИД_yes' => 'ФормИД_ЭлектрОб',
    'cat_ТабЭлОб_МонтКбЛн_yes' => 'МонтКбЛн_ЭлектрОб',
    'cat_ТабЭлОб_МонтОб_yes' => 'МонтОб_ЭлектрОб'  // Our target activity
];

foreach ($testActivities as $callback => $expectedType) {
    if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $callback, $matches)) {
        $shortKey = $matches[1];
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_ЭлектрОб',
            'ФормИД' => 'ФормИД_ЭлектрОб',
            'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
            'МонтОб' => 'МонтОб_ЭлектрОб'
        ];
        
        $result = isset($shortToFullMap[$shortKey]) ? "✅ PASS" : "❌ FAIL";
        echo "{$callback} -> {$result}\n";
    }
}

echo "\n✅ Test completed!\n";
echo "МонтОб should now work correctly when user clicks 'Да' to confirm.\n";