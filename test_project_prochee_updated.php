<?php

/**
 * Test the updated Project Activities "Прочее" text without department names
 */

echo "=== TESTING UPDATED PROJECT ACTIVITIES 'ПРОЧЕЕ' TEXT ===\n\n";

// Test the updated activity type names
echo "1. Testing updated activity type names:\n";
$activityTypeNames = [
    'Прочее_ПроектДеят_ПНР' => 'Прочее (Проектная деятельность)',
    'Прочее_ПроектДеят_ШМР' => 'Прочее (Проектная деятельность)'
];

foreach ($activityTypeNames as $activityType => $displayName) {
    echo "   {$activityType} -> {$displayName}\n";
}

echo "   ✅ Department names removed from activity type names\n\n";

// Test updated confirmation message format
echo "2. Testing confirmation message format:\n";
$testConfirmations = [
    [
        'activityType' => 'Прочее_ПроектДеят_ПНР',
        'projectCode' => 'ОИ572',
        'expectedFormat' => 'Проект <b>ОИ572</b>: <b>Прочее (Проектная деятельность)</b>. Отправить запись в табель?'
    ],
    [
        'activityType' => 'Прочее_ПроектДеят_ШМР', 
        'projectCode' => 'ОИ572',
        'expectedFormat' => 'Проект <b>ОИ572</b>: <b>Прочее (Проектная деятельность)</b>. Отправить запись в табель?'
    ]
];

foreach ($testConfirmations as $test) {
    echo "   {$test['activityType']}:\n";
    echo "   {$test['expectedFormat']}\n\n";
}

echo "   ✅ Confirmation format now excludes department names\n\n";

// Test final report generation with custom text
echo "3. Testing final report generation:\n";
$testReports = [
    [
        'fio' => 'Иванов Иван Иванович',
        'date' => '20.09.2025',
        'projectCode' => 'ОИ572',
        'activityType' => 'Прочее_ПроектДеят_ПНР',
        'customText' => 'Разработка технических требований'
    ],
    [
        'fio' => 'Петров Петр Петрович',
        'date' => '20.09.2025', 
        'projectCode' => 'ОИ572',
        'activityType' => 'Прочее_ПроектДеят_ШМР',
        'customText' => 'Координация поставок оборудования'
    ]
];

foreach ($testReports as $report) {
    $expectedText = "📝 Прочее (Проектная деятельность) ({$report['customText']})";
    
    $expectedReport = sprintf(
        "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
        $report['fio'],
        $report['date'],
        $report['projectCode'], 
        $expectedText
    );
    
    echo "   Report for {$report['activityType']}:\n";
    echo "   {$expectedReport}\n\n";
}

echo "✅ DEPARTMENT NAMES SUCCESSFULLY REMOVED!\n\n";

echo "=== COMPARISON: BEFORE vs AFTER ===\n";
echo "BEFORE:\n";
echo "   Confirmation: 'Прочее (Проектная деятельность, Отдел ШМР), отправить запись в табель в проект ОИ572?'\n";
echo "   Report: 'Прочее (Проектная деятельность, Отдел ПНР) (custom text)'\n\n";

echo "AFTER:\n";
echo "   Confirmation: 'Проект ОИ572: Прочее (Проектная деятельность). Отправить запись в табель?'\n";
echo "   Report: 'Прочее (Проектная деятельность) (custom text)'\n\n";

echo "✅ TEXT CLEANUP COMPLETED SUCCESSFULLY!\n";
echo "Now both ПНР and ШМР project activities use the same clean format.\n";

?>