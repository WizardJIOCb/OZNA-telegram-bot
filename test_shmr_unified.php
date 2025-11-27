<?php
/**
 * Test unified structure - all final actions on Level 5
 */

echo "=== ШМР Unified Structure Test ===\n\n";

$testCases = [
    [
        'name' => 'Внутриблочный монтаж (с оборудованием)',
        'message' => '📋 Работы по графику (Подключение оборудования - Электрооборудование)',
        'expected' => ['Шеф-монтажные работы', 'Внутриблочный монтаж', 'Электрооборудование', 'Подключение оборудования', 'Работы по графику']
    ],
    [
        'name' => 'Подготовительные работы',
        'message' => '📅 Работы по графику (Входной контроль)',
        'expected' => ['Шеф-монтажные работы', 'Подготовительные работы', 'Входной контроль', '', 'Работы по графику']
    ],
    [
        'name' => 'Монтаж блоков',
        'message' => '📋 Работы по графику (Расстановка блоков)',
        'expected' => ['Шеф-монтажные работы', 'Монтаж блоков', 'Расстановка блоков', '', 'Работы по графику']
    ],
    [
        'name' => 'Сборка здания',
        'message' => '✅ Этап шеф-монтажа завершен (Сборка кровли)',
        'expected' => ['Шеф-монтажные работы', 'Сборка здания', 'Сборка кровли', '', 'Этап шеф-монтажа завершен']
    ]
];

echo "Unified CSV structure - All final actions at Level 5:\n";
echo "Уровень 1 | Уровень 2 | Уровень 3 | Уровень 4 | Уровень 5\n";
echo str_repeat("-", 80) . "\n\n";

foreach ($testCases as $test) {
    echo "{$test['name']}:\n";
    echo "  {$test['expected'][0]} → {$test['expected'][1]} → {$test['expected'][2]} → ";
    echo ($test['expected'][3] !== '' ? $test['expected'][3] : '(пусто)') . " → {$test['expected'][4]}\n\n";
}

echo "✅ Benefits:\n";
echo "• All final actions consistently at Level 5\n";
echo "• Easy filtering in Excel by action type\n";
echo "• Level 4 empty for simple activities (flexible structure)\n";
echo "• Uniform hierarchy depth across all ШМР sections\n\n";

echo "=== Test Complete ===\n";
