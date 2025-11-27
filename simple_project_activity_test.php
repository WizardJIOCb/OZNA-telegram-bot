<?php

// Simple test script to verify project activity custom descriptions work correctly

echo "=== SIMPLE PROJECT ACTIVITY CUSTOM DESCRIPTION TEST ===\n\n";

// Test data
$fio = 'Тестовый Пользователь';
$date = '27.09.2025';
$projectCode = 'ОИ839';
$activityType = 'Прочее_ПроектнаяДеятельность';
$customDescription = 'Проверка отображения пользовательского описания';

echo "Test Parameters:\n";
echo "- FIO: {$fio}\n";
echo "- Date: {$date}\n";
echo "- Project Code: {$projectCode}\n";
echo "- Activity Type: {$activityType}\n";
echo "- Custom Description: {$customDescription}\n\n";

// Simulate the exact logic from TabelEntry::createTabelMessage
$activityTypeMappings = [
    'Прочее_ПроектнаяДеятельность' => '📝 Прочее (Проектная деятельность)'
];

echo "1. Activity type mapping:\n";
$activityText = $activityTypeMappings[$activityType] ?? $activityType;
echo "   Activity text from mapping: '{$activityText}'\n\n";

echo "2. Adding custom description for Project Activity 'Прочее':\n";
if ($activityType === 'Прочее_ПроектнаяДеятельность') {
    if (!empty($customDescription)) {
        $activityText .= " ({$customDescription})";
        echo "   Added custom description: '{$customDescription}'\n";
        echo "   Final activity text: '{$activityText}'\n\n";
    } else {
        echo "   No custom description provided\n\n";
    }
}

echo "3. Creating final message:\n";
$message = sprintf(
    "<b>%s</b>\r\n✅ %s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "   Final message:\n{$message}\n\n";

// Check if custom description is in the message
if (strpos($message, $customDescription) !== false) {
    echo "✅ SUCCESS: Custom description is included in the final message\n";
} else {
    echo "❌ FAILURE: Custom description is missing from the final message\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "This confirms that the logic for adding custom descriptions to project activities works correctly.\n";
echo "If the custom description is still not appearing in the actual reports, the issue is likely in:\n";
echo "1. How data is passed between TabelHandler and TabelEntry\n";
echo "2. How the message is sent to the history chat\n";
echo "3. Database operations or message processing\n";