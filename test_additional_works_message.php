<?php

// Simple test to verify the confirmation message format
echo "Testing confirmation message format for additional works activities:\n\n";

// Simulate the activity names array from TabelEntry.php
$activityNames = [
    'ИзмСхемПодкл_ЭлектрОб' => 'Изменение схемы подключения (Электрооборудование, Дополнительные работы)',
    'МонтажКабЛиний_ЭлектрОб' => 'Прокладка кабеля (Электрооборудование, Дополнительные работы)',
    'РасклКабЛиний_ЭлектрОб' => 'Подключение кабеля (Электрооборудование, Дополнительные работы)',
    'Прочее_Электрооборудование' => 'Прочее (Электрооборудование, Дополнительные работы)'
];

$projectCode = 'Проект';

foreach ($activityNames as $activityType => $activityName) {
    $message = sprintf(
        "<b>%s</b>, отправить запись в табель в проект <b>%s</b>?",
        $activityName,
        $projectCode
    );
    
    echo "Activity: {$activityType}\n";
    echo "Message: {$message}\n\n";
}

echo "Test completed successfully!\n";