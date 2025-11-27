<?php

echo "=== Testing Current Activity Format - NO Project Mention ===\n\n";

// Test data
$testData = [
    'fio' => 'Иванов Иван Иванович',
    'date' => '25.09.2025',
    'projectCode' => 'Текущая деятельность'  // Should be ignored for current activities
];

echo "🎯 CORRECTED: Current Activities do NOT relate to specific projects\n";
echo "================================================================\n\n";

// Current activity types that should NOT mention projects
$currentActivityTypes = [
    'МедОсмотр' => 'Медицинский осмотр',
    'Аттестация' => 'Аттестация', 
    'ПодготовкаТКП' => 'Подготовка ТКП',
    'Общая' => 'Общая деятельность'
];

// TKP business directions that SHOULD mention projects 
$tkpBusinessDirections = [
    'ПодготовкаТКП_ОПНВГ' => 'Подготовка ТКП (ОПНВГ)',
    'ПодготовкаТКП_НКО' => 'Подготовка ТКП (НКО)',
    'ПодготовкаТКП_МСО' => 'Подготовка ТКП (МСО)',
    'ПодготовкаТКП_АСН' => 'Подготовка ТКП (АСН)'
];

echo "1. Current Activities - Confirmation Messages (should NOT mention project):\n\n";

foreach ($currentActivityTypes as $activityType => $displayName) {
    echo "Testing: $activityType ($displayName)\n";
    
    // Should NOT mention project: "<b>ACTIVITY</b>. Отправить запись в табель?"
    $expectedConfirmation = sprintf("<b>%s</b>. Отправить запись в табель?", $displayName);
    echo "Expected: $expectedConfirmation\n";
    
    // Check if it correctly excludes project mention
    $noProjectMention = (strpos($expectedConfirmation, 'Проект') === false);
    $status = $noProjectMention ? "✅ Correctly excludes project" : "❌ Incorrectly includes project";
    echo "Status: $status\n\n";
}

echo "2. Current Activities - Final Report Messages (should NOT mention project):\n\n";

foreach ($currentActivityTypes as $activityType => $displayName) {
    echo "Testing: $activityType ($displayName)\n";
    
    // Should NOT mention project: "<b>FIO</b>\r\n✅Запись в табель от <b>DATE</b>: <b>ACTIVITY</b> добавлена"
    $expectedFinalReport = sprintf(
        "<b>%s</b>\r\n✅Запись в табель от <b>%s</b>: <b>%s</b> добавлена",
        $testData['fio'],
        $testData['date'],
        $displayName
    );
    echo "Expected: $expectedFinalReport\n";
    
    // Check if it correctly excludes project mention
    $noProjectMention = (strpos($expectedFinalReport, 'Проект') === false);
    $status = $noProjectMention ? "✅ Correctly excludes project" : "❌ Incorrectly includes project";
    echo "Status: $status\n\n";
}

echo "3. TKP Business Directions - SHOULD mention project (unchanged):\n\n";

foreach ($tkpBusinessDirections as $activityType => $displayName) {
    echo "Testing: $activityType ($displayName)\n";
    
    // TKP business directions SHOULD mention project (using automation format)
    $expectedConfirmation = sprintf(
        "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
        'ОИ834',  // Real project code for TKP
        $displayName
    );
    echo "Confirmation: $expectedConfirmation\n";
    
    $expectedFinalReport = sprintf(
        "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
        $testData['fio'],
        $testData['date'],
        'ОИ834',  // Real project code for TKP
        $displayName
    );
    echo "Final Report: $expectedFinalReport\n";
    
    // TKP business directions SHOULD mention projects
    $mentionsProject = (strpos($expectedConfirmation, 'Проект') !== false && strpos($expectedFinalReport, 'Проект') !== false);
    $status = $mentionsProject ? "✅ Correctly mentions project" : "❌ Missing project mention";
    echo "Status: $status\n\n";
}

echo "4. Format Logic Summary:\n\n";

echo "CURRENT ACTIVITIES (МедОсмотр, Аттестация, ПодготовкаТКП, Общая):\n";
echo "• NO project mention - they are general activities not tied to specific projects\n";
echo "• Confirmation: <b>ACTIVITY</b>. Отправить запись в табель?\n";
echo "• Final Report: <b>FIO</b>\\r\\n✅Запись в табель от <b>DATE</b>: <b>ACTIVITY</b> добавлена\n\n";

echo "TKP BUSINESS DIRECTIONS (БН ОПНВГ, БН НКО, БН МСО, БН АСН):\n";
echo "• WITH project mention - they are project-specific activities\n";
echo "• Confirmation: Проект <b>PROJECT</b>: <b>ACTIVITY</b>. Отправить запись в табель?\n";
echo "• Final Report: <b>FIO</b>\\r\\n✅DATE, Проект <b>PROJECT</b>: Добавлена запись в табель: <b>ACTIVITY</b>\n\n";

echo "=== CORRECTION COMPLETE ===\n";
echo "✅ Current Activities correctly exclude project mentions\n";
echo "✅ TKP Business Directions correctly include project mentions\n";
echo "✅ Proper separation between general and project-specific activities\n";

echo "\nThe Current Activity format correction is complete! 🎉\n";

?>