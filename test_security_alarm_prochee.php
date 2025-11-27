<?php
// Simple test to verify Security Alarm Equipment "Прочее" activity mapping

echo "Testing Security Alarm Equipment 'Прочее' activity mapping...\n";

// Simulate the activity mapping lookup
$activityTypeMappings = [
    'Прочее_ОхрСигн' => '📝 Прочее (Охранная сигнализация, Дополнительные работы)',
    'Прочее_ПожСигн' => '📝 Прочее (Пожарная сигнализация, Дополнительные работы)',
];

$activityType = 'Прочее_ОхрСигн';
$idleReason = 'Настройка дополнительного оборудования';

echo "Activity Type: {$activityType}\n";
echo "Custom Description: {$idleReason}\n";

$activityText = $activityTypeMappings[$activityType] ?? $activityType;
echo "Activity Text from mapping: {$activityText}\n";

// Add custom description if provided
if ($activityType === 'Прочее_ОхрСигн' && !empty($idleReason)) {
    $activityText .= " ({$idleReason})";
    echo "Activity Text with custom description: {$activityText}\n";
}

// Simulate the final message format
$fio = "Тестовый Пользователь";
$date = "20.09.2025";  
$projectCode = "ОИ834";

$finalMessage = sprintf(
    "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "\nExpected Final Message:\n";
echo "{$finalMessage}\n";

echo "\nTest completed!\n";
?>