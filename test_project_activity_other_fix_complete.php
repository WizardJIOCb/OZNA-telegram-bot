<?php
// Simple test to verify the complete fix is working
$activityNames = [
    // Current activities
    'МедОсмотр' => 'Медицинский осмотр',
    'Аттестация' => 'Аттестация',
    'ПодготовкаТКП' => 'Подготовка ТКП',
    'Общая' => 'Общая деятельность',
    // New current activity types
    'УчастиеВЗПИ' => 'Участие в ЗПИ',
    'РазрПрограммы' => 'Разработка программы работ',
    'РазрГрафика' => 'Разработка графика работ',
    'ОргДопоставки' => 'Организация допоставки',
    'УчастиеВОПИ' => 'Участие в ОПИ',
    'РазработкаПрограммыРабот' => 'Разработка программы работ',
    'РазработкаГрафикаРабот' => 'Разработка графика работ',
    'ПодготовкаОтчета' => 'Подготовка отчета',
    // Project activity "Other" type
    'Прочее_ПроектнаяДеятельность' => 'Прочее (Проектная деятельность)',
    // Mobile brigade
    'Электромонтажные' => 'Электромонтажные работы',
    'Сварочные' => 'Сварочные работы',
    'Слесарные' => 'Слесарные работы',
    'Теплоизоляция' => 'Теплоизоляция',
    'Документы' => 'Отчётные документы'
];

$activityType = 'Прочее_ПроектнаяДеятельность';
$activityName = $activityNames[$activityType] ?? $activityType;

echo "Activity type: " . $activityType . "\n";
echo "Mapped name: " . $activityName . "\n";

// Test with a project code like in the user's example
$projectCode = 'ОИ833';
$message = sprintf(
    "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
    $projectCode,
    $activityName
);

echo "Confirmation message: " . $message . "\n";

// Test the full message creation with custom description
$activityText = $activityNames[$activityType] ?? $activityType;
$idleReason = 'Тест';
if ($activityType === 'Прочее_ПроектнаяДеятельность' && !empty($idleReason)) {
    $activityText .= " ({$idleReason})";
}

$fio = 'Тестовый Пользователь';
$date = '29.09.2025';

$fullMessage = sprintf(
    "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "Full message: " . $fullMessage . "\n";
?>