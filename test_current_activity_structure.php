<?php
/**
 * Test for Current Activity (Текущая деятельность) report structure
 */

echo "=== Текущая деятельность Report Structure Test ===\n\n";

$testCases = [
    [
        'name' => 'Медицинский осмотр',
        'message' => 'Медицинский осмотр',
        'expected' => ['Текущая деятельность', 'Медицинский осмотр', '', '', '']
    ],
    [
        'name' => 'Аттестация',
        'message' => 'Аттестация',
        'expected' => ['Текущая деятельность', 'Аттестация', '', '', '']
    ],
    [
        'name' => 'Общая деятельность',
        'message' => 'Общая деятельность',
        'expected' => ['Текущая деятельность', 'Общая деятельность', '', '', '']
    ],
    [
        'name' => 'Подготовка ТКП - ОПНВГ',
        'message' => 'Подготовка ТКП (ОПНВГ)',
        'expected' => ['Текущая деятельность', 'Подготовка ТКП', 'ОПНВГ', '', '']
    ],
    [
        'name' => 'Подготовка ТКП - НКО',
        'message' => 'Подготовка ТКП (НКО)',
        'expected' => ['Текущая деятельность', 'Подготовка ТКП', 'НКО', '', '']
    ]
];

echo "Unified CSV structure for all ФН - Текущая деятельность:\n";
echo "Уровень 1 | Уровень 2 | Уровень 3 | Уровень 4 | Уровень 5\n";
echo str_repeat("-", 80) . "\n\n";

foreach ($testCases as $test) {
    echo "{$test['name']}:\n";
    echo "  {$test['expected'][0]} → ";
    echo "{$test['expected'][1]} → ";
    echo ($test['expected'][2] !== '' ? $test['expected'][2] : '(пусто)') . " → ";
    echo ($test['expected'][3] !== '' ? $test['expected'][3] : '(пусто)') . " → ";
    echo ($test['expected'][4] !== '' ? $test['expected'][4] : '(пусто)') . "\n\n";
}

echo "✅ Key Points:\n";
echo "• Level 1: Always 'Текущая деятельность' (same for all ФН)\n";
echo "• Level 2: Activity type (Медицинский осмотр, Аттестация, etc.)\n";
echo "• Level 3: Sub-type if exists (for ТКП: ОПНВГ, НКО, МСО, АСН)\n";
echo "• Level 4: Empty (reserved for future expansion)\n";
echo "• Level 5: Empty (consistent with other activities)\n\n";

echo "📊 Example CSV Output:\n";
echo "Проект | Дата | Процесс (ФН) | Уровень 1 | Уровень 2 | Уровень 3 | Уровень 4 | Уровень 5 | Фамилия\n";
echo "       | 26.10.2025 | Пусконаладка | Текущая деятельность | Медицинский осмотр |  |  |  | Иванов И.И.\n";
echo "       | 26.10.2025 | Шеф-монтаж   | Текущая деятельность | Подготовка ТКП | ОПНВГ |  |  | Петров П.П.\n";
echo "       | 26.10.2025 | Мобильная бригада | Текущая деятельность | Аттестация |  |  |  | Сидоров С.С.\n\n";

echo "=== Test Complete ===\n";
