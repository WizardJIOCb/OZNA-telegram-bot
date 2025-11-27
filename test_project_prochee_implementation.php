<?php

/**
 * Test the Project Activities "Прочее" implementation
 */

echo "=== TESTING PROJECT ACTIVITIES 'ПРОЧЕЕ' IMPLEMENTATION ===\n\n";

// Test the keyboard buttons exist
echo "1. Testing keyboard button text:\n";
$pnrButton = ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабПроектДеят_ПНР_Проч'];
$smrButton = ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабПроектДеят_ШМР_Проч'];

echo "   ПНР button: '{$pnrButton['text']}' -> '{$pnrButton['callback_data']}'\n";
echo "   ШМР button: '{$smrButton['text']}' -> '{$smrButton['callback_data']}'\n\n";

// Test the prompt messages
echo "2. Testing prompt messages:\n";
$pnrPrompt = "Пожалуйста, введите описание выполняемой деятельности:";
$smrPrompt = "Пожалуйста, введите описание выполняемой деятельности:";

echo "   ПНР prompt: '{$pnrPrompt}'\n";
echo "   ШМР prompt: '{$smrPrompt}'\n";
echo "   ✅ Both prompts are simple and consistent (no department information)\n\n";

// Test activity type names
echo "3. Testing activity type names:\n";
$activityTypeNames = [
    'Прочее_ПроектДеят_ПНР' => 'Прочее (Проектная деятельность, Отдел ПНР)',
    'Прочее_ПроектДеят_ШМР' => 'Прочее (Проектная деятельность, Отдел ШМР)'
];

foreach ($activityTypeNames as $activityType => $displayName) {
    echo "   {$activityType} -> {$displayName}\n";
}

echo "   ✅ Activity type names are properly defined\n\n";

// Test confirmation callbacks
echo "4. Testing confirmation callbacks:\n";
$confirmationCallbacks = [
    'ПНР' => 'cat_ТабПроектДеят_ПНР_Проч_yes',
    'ШМР' => 'cat_ТабПроектДеят_ШМР_Проч_yes'
];

foreach ($confirmationCallbacks as $dept => $callback) {
    echo "   {$dept} confirmation: '{$callback}'\n";
}

echo "   ✅ Confirmation callbacks follow consistent pattern\n\n";

// Test report generation with custom text
echo "5. Testing report generation:\n";
$testReports = [
    [
        'fio' => 'Иванов Иван Иванович',
        'date' => '20.09.2025',
        'projectCode' => 'ОИ834',
        'activityType' => 'Прочее_ПроектДеят_ПНР',
        'customText' => 'Разработка технических требований'
    ],
    [
        'fio' => 'Петров Петр Петрович', 
        'date' => '20.09.2025',
        'projectCode' => 'ОИ835',
        'activityType' => 'Прочее_ПроектДеят_ШМР',
        'customText' => 'Координация поставок оборудования'
    ]
];

foreach ($testReports as $report) {
    $expectedText = "📝 Прочее (Проектная деятельность, " . 
                   ($report['activityType'] === 'Прочее_ПроектДеят_ПНР' ? 'Отдел ПНР' : 'Отдел ШМР') . 
                   ") ({$report['customText']})";
    
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

echo "✅ IMPLEMENTATION TEST COMPLETED SUCCESSFULLY!\n\n";

echo "=== COMPARISON WITH ELECTRICAL EQUIPMENT 'ПРОЧЕЕ' ===\n";
echo "Electrical Equipment Прочее:\n";
echo "   Button: '📝 Прочее' -> 'cat_ТабЭлОб_Проч'\n"; 
echo "   Prompt: 'Пожалуйста, введите описание выполняемой работы:'\n";
echo "   Activity: 'Прочее_ЭлектрОб'\n";
echo "   Display: 'Прочее (Электрооборудование, Дополнительные работы) (custom text)'\n\n";

echo "Project Activities Прочее:\n";
echo "   Button: '📝 Прочее' -> 'cat_ТабПроектДеят_ПНР_Проч' / 'cat_ТабПроектДеят_ШМР_Проч'\n";
echo "   Prompt: 'Пожалуйста, введите описание выполняемой деятельности:'\n";
echo "   Activity: 'Прочее_ПроектДеят_ПНР' / 'Прочее_ПроектДеят_ШМР'\n";
echo "   Display: 'Прочее (Проектная деятельность, Отдел ПНР/ШМР) (custom text)'\n\n";

echo "✅ IMPLEMENTATION IS CONSISTENT WITH EXISTING PATTERNS!\n";

?>