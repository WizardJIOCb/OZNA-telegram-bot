<?php
/**
 * Test the fixed regex for parsing report entries
 */

// Test messages - one with space after ✅, one without
$messages = [
    // Гайнутдинов - БЕЗ пробела после ✅
    'without_space' => '<b>Гайнутдинов Роберт Рамильевич</b>
✅30.09.2025, Проект <b>ОИ849</b>: Добавлена запись в табель: <b>🏢 Участие в ЗПИ</b>',
    
    // Ночевный - С пробелом после ✅
    'with_space' => '<b>Ночевный Павел Александрович</b>
✅ 30.09.2025, Проект <b>ОИ849</b>: Добавлена запись в табель: <b>🏢 Участие в ЗПИ</b>',
    
    // Ахунов - С пробелом после ✅
    'with_space_2' => '<b>Ахунов Айдар Ришатович</b>
✅ 30.09.2025, Проект <b>ОИ849</b>: Добавлена запись в табель: <b>🏢 Участие в ЗПИ</b>',
];

echo "=== Тест исправленного regex ===\n\n";

// OLD regex (without \s*)
$oldRegex = '/✅(\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: .*? <b>(.*?)<\/b>/';

// NEW regex (with \s*)
$newRegex = '/✅\s*(\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: .*? <b>(.*?)<\/b>/';

foreach ($messages as $name => $message) {
    echo "=== Тест: {$name} ===\n";
    
    // Test OLD regex
    if (preg_match($oldRegex, $message, $matches)) {
        echo "СТАРЫЙ regex: ✅ Дата={$matches[1]}, Проект={$matches[2]}, Активность={$matches[3]}\n";
    } else {
        echo "СТАРЫЙ regex: ❌ НЕ СОВПАЛО\n";
    }
    
    // Test NEW regex
    if (preg_match($newRegex, $message, $matches)) {
        echo "НОВЫЙ regex:  ✅ Дата={$matches[1]}, Проект={$matches[2]}, Активность={$matches[3]}\n";
    } else {
        echo "НОВЫЙ regex:  ❌ НЕ СОВПАЛО\n";
    }
    
    echo "\n";
}

echo "=== Результат ===\n";
echo "Исправление добавляет \\s* после ✅, что позволяет парсить записи как с пробелом, так и без.\n";
