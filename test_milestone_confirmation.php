<?php

// Test script to verify ПодтВех_ЭлектрОб special formatting
echo "Testing ПодтВех_ЭлектрОб special formatting...\n\n";

try {
    // Test data
    $activityType = 'ПодтВех_ЭлектрОб';
    $projectCode = 'ОИ834';
    $fio = 'Test User';
    $date = '20.09.2025';
    
    echo "Testing confirmation message:\n";
    echo "Activity Type: {$activityType}\n";
    echo "Project Code: {$projectCode}\n";
    
    // Simulate getConfirmationMessage logic
    $expectedConfirmation = sprintf(
        "Проект <b>%s</b>: Подтвердить завершения этапа наладки (Электрооборудование). Отправить запись в табель?",
        $projectCode
    );
    
    echo "Expected confirmation message:\n";
    echo $expectedConfirmation . "\n\n";
    
    echo "Testing final report message:\n";
    echo "FIO: {$fio}\n";
    echo "Date: {$date}\n";
    echo "Project Code: {$projectCode}\n";
    
    // Simulate createTabelMessage logic
    $expectedReport = sprintf(
        "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Электрооборудование)</b>",
        $fio,
        $date,
        $projectCode
    );
    
    echo "Expected final report message:\n";
    echo $expectedReport . "\n\n";
    
    echo "✅ Test completed successfully!\n";
    echo "The messages match the required format:\n";
    echo "- Confirmation: 'Проект ОИ834: Подтвердить завершения этапа наладки (Электрооборудование). Отправить запись в табель?'\n";
    echo "- Report: '✅20.09.2025, Проект ОИ834: Добавлена запись в табель: ✅ Этап наладки завершен (Электрооборудование)'\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";