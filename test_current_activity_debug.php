<?php

/**
 * Debug test to find why Current Activities are showing project format
 */

echo "=== CURRENT ACTIVITY DEBUG TEST ===\n\n";

// Test the exact scenarios reported by the user
$testCases = [
    [
        'activityType' => 'МедОсмотр',
        'projectCode' => 'Текущая деятельность',
        'description' => 'Medical exam with Current Activity project code'
    ],
    [
        'activityType' => 'Аттестация', 
        'projectCode' => 'Текущая деятельность',
        'description' => 'Certification with Current Activity project code'
    ],
    [
        'activityType' => 'ПодготовкаТКП_ОПНВГ',
        'projectCode' => 'Текущая деятельность', 
        'description' => 'TKP OPNVG with Current Activity project code'
    ],
    [
        'activityType' => 'ПодготовкаТКП_МСО',
        'projectCode' => 'Текущая деятельность',
        'description' => 'TKP MSO with Current Activity project code'
    ]
];

// Activity names mapping (from TabelEntry)
$activityNames = [
    'МедОсмотр' => 'Медицинский осмотр',
    'Аттестация' => 'Аттестация', 
    'ПодготовкаТКП_ОПНВГ' => 'Подготовка материала к ТКП, БН ОПНВГ',
    'ПодготовкаТКП_НКО' => 'Подготовка материала к ТКП, БН НКО',
    'ПодготовкаТКП_МСО' => 'Подготовка материала к ТКП, БН МСО',
    'ПодготовкаТКП_АСН' => 'Подготовка материала к ТКП, БН АСН',
];

foreach ($testCases as $index => $testCase) {
    $testNum = $index + 1;
    echo "TEST {$testNum}: {$testCase['description']}\n";
    echo str_repeat("=", 50) . "\n";
    
    $activityType = $testCase['activityType'];
    $projectCode = $testCase['projectCode'];
    $activityName = $activityNames[$activityType] ?? $activityType;
    
    echo "Activity Type: {$activityType}\n";
    echo "Project Code: {$projectCode}\n";
    echo "Activity Name: {$activityName}\n\n";
    
    // Test which condition would be matched in getConfirmationMessage
    echo "TESTING CONFIRMATION MESSAGE CONDITIONS:\n";
    echo "---------------------------------------\n";
    
    // Check if it's in commissioning activities
    $commissioningActivities = [
        'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
        'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология'
    ];
    
    if (in_array($activityType, $commissioningActivities)) {
        echo "❌ CAUGHT BY: Commissioning activities condition\n";
        echo "Format: 'Проект {$projectCode}: {$activityName}. Отправить запись в табель?'\n";
        continue;
    }
    
    // Check if it's in project activities
    $projectActivities = [
        'УчастиеВЗПИ', 'РазрПрограммы', 'РазрГрафика', 'ОргДопоставки', 'УчастиеВОПИ'
    ];
    
    if (in_array($activityType, $projectActivities)) {
        echo "❌ CAUGHT BY: Project activities condition\n";
        echo "Format: 'Проект {$projectCode}: {$activityName}. Отправить запись в табель?'\n";
        continue;
    }
    
    // Check various equipment activities arrays
    $equipmentArrays = [
        'electrical' => ['РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 'МонтОб_ЭлектрОб', 'Прочее_ЭлектрОб'],
        'automation' => ['РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом', 'ДопРаб_Автом', 'ИзмСх_Автом', 'МонтКб_Автом', 'РаскКб_Автом', 'МонтАСУ_Автом', 'УдалПО_Автом', 'Прочее_Автом'],
        'fireAlarm' => ['РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн', 'ИзмСх_ПожСигн', 'МонтКб_ПожСигн', 'РаскКб_ПожСигн', 'МонтОб_ПожСигн', 'Прочее_ПожСигн'],
        'security' => ['РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн', 'ИзмСх_ОхрСигн', 'МонтКб_ОхрСигн', 'РаскКб_ОхрСигн', 'МонтОб_ОхрСигн', 'Прочее_ОхрСигн'],
        'heating' => ['РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл', 'ДопРаб_ВентОтопл', 'ИзмСх_ВентОтопл', 'МонтКб_ВентОтопл', 'РаскКб_ВентОтопл', 'МонтАСУ_ВентОтопл', 'УдалПО_ВентОтопл', 'Прочее_ВентОтопл'],
        'technological' => ['РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ПодтВех_ТехОб', 'ДопРаб_ТехОб', 'ДемАрм_ТехОб', 'ДемОб_ТехОб', 'Прочее_ТехОб'],
        'metrology' => ['РабПоГр_Метрол', 'ФормИД_Метрол', 'ПодтВех_Метрол', 'ДопРаб_Метрол', 'ИзмКонф_Метрол', 'КалПов_Метрол', 'КМХ_Метрол', 'КоррХар_Метрол', 'Проч_Метрол'],
        'rkComments' => ['УстрЗам_ЭС', 'УстрЗам_АТХ', 'УстрЗам_ОВКВ', 'УстрЗам_ПСОС', 'УстрЗам_ТХ', 'УстрЗам_Прочее']
    ];
    
    $caughtBy = null;
    foreach ($equipmentArrays as $arrayName => $activities) {
        if (in_array($activityType, $activities)) {
            $caughtBy = $arrayName;
            break;
        }
    }
    
    if ($caughtBy) {
        echo "❌ CAUGHT BY: {$caughtBy} equipment activities condition\n";
        echo "Format: 'Проект {$projectCode}: {$activityName}. Отправить запись в табель?'\n";
        continue;
    }
    
    // Check current activities condition
    if (in_array($activityType, ['МедОсмотр', 'Аттестация', 'ПодготовкаТКП', 'Общая']) || 
        in_array($activityType, ['ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН']) || 
        $projectCode === 'Текущая деятельность') {
        echo "✅ SHOULD BE CAUGHT BY: Current activities condition\n";
        echo "Expected format: '{$activityName}. Отправить запись в табель?'\n";
    } else {
        echo "❌ FALLS THROUGH TO: Default condition\n";
        echo "Format: '{$activityName}, отправить запись в табель в проект {$projectCode}?'\n";
    }
    
    echo "\n";
}

echo "=== ANALYSIS ===\n";
echo "Based on the code logic, all test cases should be caught by the Current Activities condition.\n";
echo "If they're still showing project format, there might be an issue with:\n";
echo "1. Code not being reloaded (server restart needed)\n";
echo "2. Different execution path being taken\n";
echo "3. Code logic error somewhere else\n";

?>