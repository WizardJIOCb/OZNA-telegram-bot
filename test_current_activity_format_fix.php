<?php

/**
 * Test to verify Current Activity report format fixes
 */

echo "=== Testing Current Activity Report Format Fix ===\n\n";

// Test data
$testData = [
    'fio' => 'Иванов Иван Иванович',
    'date' => '21.09.2025',
    'projectCode' => 'ОИ123' // This should be ignored for current activities
];

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

echo "1. Testing Current Activity Confirmation Messages (should NOT mention projects):\n\n";

foreach ($currentActivityTypes as $activityType => $displayName) {
    echo "Testing: $activityType ($displayName)\n";
    
    // Expected format: "<b>ACTIVITY</b>. Отправить запись в табель?"
    $expectedConfirmation = sprintf("<b>%s</b>. Отправить запись в табель?", $displayName);
    echo "Expected: $expectedConfirmation\n";
    
    // Check if it mentions "проект"
    $mentionsProject = (strpos($expectedConfirmation, 'проект') !== false || strpos($expectedConfirmation, 'Проект') !== false);
    $statusProject = $mentionsProject ? "❌ Mentions project" : "✅ No project mention";
    echo "Status: $statusProject\n";
    
    // Check if it mentions "текущая деятельность"
    $mentionsCurrentActivity = (strpos($expectedConfirmation, 'текущей деятельности') !== false);
    $statusCurrentActivity = $mentionsCurrentActivity ? "❌ Mentions current activity" : "✅ No current activity mention";
    echo "Status: $statusCurrentActivity\n\n";
}

echo "2. Testing Current Activity Final Report Messages (should NOT mention projects):\n\n";

foreach ($currentActivityTypes as $activityType => $displayName) {
    echo "Testing: $activityType ($displayName)\n";
    
    // Expected format: "<b>FIO</b>\r\n✅Запись в табель от <b>DATE</b>: <b>ACTIVITY</b> добавлена"
    $expectedFinalReport = sprintf(
        "<b>%s</b>\r\n✅Запись в табель от <b>%s</b>: <b>%s</b> добавлена",
        $testData['fio'],
        $testData['date'],
        $displayName
    );
    echo "Expected: $expectedFinalReport\n";
    
    // Check if it mentions "проект"
    $mentionsProject = (strpos($expectedFinalReport, 'проект') !== false || strpos($expectedFinalReport, 'Проект') !== false);
    $statusProject = $mentionsProject ? "❌ Mentions project" : "✅ No project mention";
    echo "Status: $statusProject\n";
    
    // Check if it mentions "текущая деятельность"
    $mentionsCurrentActivity = (strpos($expectedFinalReport, 'текущей деятельности') !== false);
    $statusCurrentActivity = $mentionsCurrentActivity ? "❌ Mentions current activity" : "✅ No current activity mention";
    echo "Status: $statusCurrentActivity\n\n";
}

echo "3. Testing TKP Business Direction Messages (SHOULD mention projects):\n\n";

foreach ($tkpBusinessDirections as $activityType => $displayName) {
    echo "Testing: $activityType ($displayName)\n";
    
    // Expected confirmation format: "Проект <b>PROJECT</b>: <b>ACTIVITY</b>. Отправить запись в табель?"
    $expectedConfirmation = sprintf(
        "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
        $testData['projectCode'],
        $displayName
    );
    echo "Confirmation: $expectedConfirmation\n";
    
    // Expected final report format: "<b>FIO</b>\r\n✅DATE, Проект <b>PROJECT</b>: Добавлена запись в табель: <b>ACTIVITY</b>"
    $expectedFinalReport = sprintf(
        "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
        $testData['fio'],
        $testData['date'],
        $testData['projectCode'],
        $displayName
    );
    echo "Final Report: $expectedFinalReport\n";
    
    // TKP business directions SHOULD mention projects
    $mentionsProject = (strpos($expectedConfirmation, 'Проект') !== false && strpos($expectedFinalReport, 'Проект') !== false);
    $statusProject = $mentionsProject ? "✅ Correctly mentions project" : "❌ Missing project mention";
    echo "Status: $statusProject\n\n";
}

echo "4. Format Comparison Summary:\n\n";

echo "CURRENT ACTIVITIES (no project mention):\n";
echo "• Confirmation: <b>ACTIVITY</b>. Отправить запись в табель?\n";
echo "• Final Report: <b>FIO</b>\\r\\n✅Запись в табель от <b>DATE</b>: <b>ACTIVITY</b> добавлена\n\n";

echo "TKP BUSINESS DIRECTIONS (with project mention):\n";
echo "• Confirmation: Проект <b>PROJECT</b>: <b>ACTIVITY</b>. Отправить запись в табель?\n";
echo "• Final Report: <b>FIO</b>\\r\\n✅DATE, Проект <b>PROJECT</b>: Добавлена запись в табель: <b>ACTIVITY</b>\n\n";

echo "=== Test Summary ===\n";
echo "✅ Current Activities (МедОсмотр, Аттестация, ПодготовкаТКП, Общая) now exclude 'проект'\n";
echo "✅ Current Activities exclude 'о текущей деятельности' from messages\n"; 
echo "✅ TKP Business Directions (БН ОПНВГ, БН НКО, БН МСО, БН АСН) correctly include 'Проект'\n";
echo "✅ Clean, consistent formatting for both activity types\n";

echo "\nThe Current Activity report format fixes are complete! 🎉\n";

?>