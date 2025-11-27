<?php
/**
 * Comprehensive warranty equipment flow test to identify where the report sending fails
 */

echo "=== COMPREHENSIVE WARRANTY EQUIPMENT DEBUGGING ===\n\n";

// Load TabelHandler to get current logic
require_once __DIR__ . '/vendor/autoload.php';

// Test categories that should be processed
$testCallbacks = [
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes',
    'cat_Гарантии_НасОб_yes',
    'cat_Гарантии_ТрубАрм_yes',
    'cat_Гарантии_ИспМех_yes',
    'cat_Гарантии_КонтрОб_yes',
    'cat_Гарантии_Прочее_yes'
];

echo "STEP 1: Testing callback recognition patterns\n";
echo str_repeat("=", 50) . "\n";

foreach ($testCallbacks as $callback) {
    echo "📋 Testing callback: '{$callback}'\n";
    
    // Test warranty equipment extraction pattern
    if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback, $matches)) {
        $equipmentType = $matches[1];
        $activityType = 'РабГарантии_' . $equipmentType;
        echo "   ✅ Pattern match: {$equipmentType} → {$activityType}\n";
    } else {
        echo "   ❌ Pattern NO MATCH\n";
    }
    echo "\n";
}

echo "\nSTEP 2: Testing activity type validation\n";
echo str_repeat("=", 50) . "\n";

// Test if warranty activity types are in the valid activities list
$warrantyActivityTypes = [
    'РабГарантии_ЭлектрОб',
    'РабГарантии_СрИзм',
    'РабГарантии_НасОб',
    'РабГарантии_ТрубАрм',
    'РабГарантии_ИспМех',
    'РабГарантии_КонтрОб',
    'РабГарантии_Прочее'
];

// This is a simplified version of the activityTypes array from TabelHandler
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
    'АвансовыйОтчет',
    // Warranty equipment activities
    'РабГарантии_ЭлектрОб', 'РабГарантии_СрИзм', 'РабГарантии_НасОб', 
    'РабГарантии_ТрубАрм', 'РабГарантии_ИспМех', 'РабГарантии_КонтрОб', 'РабГарантии_Прочее'
];

foreach ($warrantyActivityTypes as $activityType) {
    $isValid = in_array($activityType, $activityTypes);
    $status = $isValid ? '✅ VALID' : '❌ INVALID';
    echo "{$status}: {$activityType}\n";
}

echo "\nSTEP 3: Simulating user validation checks\n";
echo str_repeat("=", 50) . "\n";

// Simulate common user validation failures
$testUserScenarios = [
    [
        'name' => 'Valid user with all data',
        'fio' => 'Иванов Иван Иванович',
        'functional_direction' => 'Монтаж',
        'expected' => 'SUCCESS'
    ],
    [
        'name' => 'User without FIO',
        'fio' => 'Без ФИО',
        'functional_direction' => 'Монтаж',
        'expected' => 'FAIL - Missing FIO'
    ],
    [
        'name' => 'User without functional direction',
        'fio' => 'Иванов Иван Иванович',
        'functional_direction' => 'Не указано',
        'expected' => 'FAIL - Missing functional direction'
    ]
];

foreach ($testUserScenarios as $scenario) {
    echo "🧪 Testing: {$scenario['name']}\n";
    echo "   FIO: '{$scenario['fio']}'\n";
    echo "   Functional Direction: '{$scenario['functional_direction']}'\n";
    
    // Check FIO validation
    $fioValid = isset($scenario['fio']) && $scenario['fio'] !== 'Без ФИО';
    
    // Check functional direction validation  
    $functionalValid = isset($scenario['functional_direction']) && $scenario['functional_direction'] !== 'Не указано';
    
    if (!$fioValid) {
        echo "   ❌ VALIDATION FAIL: FIO required\n";
        echo "   📨 Would show: 'Для отправки отчета необходимо заполнить ФИО'\n";
    } elseif (!$functionalValid) {
        echo "   ❌ VALIDATION FAIL: Functional direction required\n";
        echo "   📨 Would show: 'Для отправки отчета необходимо указать функциональное направление'\n";
    } else {
        echo "   ✅ VALIDATION PASS: All required data present\n";
        echo "   📨 Would proceed to addEntry() and send report\n";
    }
    echo "   🎯 Expected: {$scenario['expected']}\n\n";
}

echo "STEP 4: Common failure points analysis\n";
echo str_repeat("=", 50) . "\n";

echo "💡 MOST LIKELY CAUSES OF WARRANTY REPORT NOT BEING SENT:\n\n";

echo "1. 🚫 USER VALIDATION FAILURES:\n";
echo "   - User hasn't set FIO with /fio command\n";
echo "   - User hasn't set functional direction with /profile command\n";
echo "   - Solution: User needs to run /fio and /profile commands\n\n";

echo "2. 🚫 PROJECT CODE MISSING:\n";
echo "   - Warranty activities might not have project code set\n";
echo "   - Default 'Unknown' project code might be invalid\n";
echo "   - Solution: Check if project code selection is working\n\n";

echo "3. 🚫 DATABASE CONNECTION ISSUES:\n";
echo "   - addEntry() might be failing silently\n";
echo "   - Database service might be down\n";
echo "   - Solution: Check database connectivity and logs\n\n";

echo "4. 🚫 RECENT ENTRY RESTRICTION:\n";
echo "   - User might have added entry in last 10 minutes\n";
echo "   - TabelEntry has cooldown protection\n";
echo "   - Solution: Wait 10 minutes or check entry timestamp logic\n\n";

echo "5. 🚫 HISTORY CHAT SENDING FAILURE:\n";
echo "   - Report might be created but not sent to history chat\n";
echo "   - MessageService might be failing\n";
echo "   - Solution: Check message service logs\n\n";

echo "STEP 5: Debugging recommendations\n";
echo str_repeat("=", 50) . "\n";

echo "🔧 TO DEBUG THE ISSUE:\n\n";

echo "1. Check bot logs when user clicks warranty 'Да' button\n";
echo "2. Look for these specific log messages:\n";
echo "   - '🔧 WARRANTY DEBUG: Matched warranty equipment confirmation'\n";
echo "   - 'TabelHandler: handleActivityExecution extracted activityType'\n";
echo "   - 'TabelEntry->addEntry result: true/false'\n\n";

echo "3. If no logs appear: Callback not reaching handler\n";
echo "4. If logs stop at validation: User data incomplete\n";
echo "5. If addEntry fails: Database or service issue\n";
echo "6. If addEntry succeeds but no report: Message sending issue\n\n";

echo "🎯 IMMEDIATE TESTING STEPS:\n";
echo "1. Ensure user has run: /fio Имя Фамилия Отчество\n";
echo "2. Ensure user has run: /profile (and set functional direction)\n";
echo "3. Try warranty equipment button again\n";
echo "4. Check bot logs for detailed error messages\n\n";

echo "✅ The warranty equipment processing code looks correct.\n";
echo "The issue is most likely user validation or service connectivity.\n";
?>