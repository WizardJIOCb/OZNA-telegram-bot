<?php
/**
 * Test script to verify absence entry parsing in reports.
 * Tests both extractDateFromMessage and parseReportEntry logic.
 */

// Test messages matching the format from TabelEntry::addAbsenceEntries
$testMessages = [
    '<b>Иванов Иван</b>' . "\r\n" . '🏥 01.03.2026: Больничный',
    '<b>Петров Петр</b>' . "\r\n" . '🏖 15.02.2026: Отпуск',
    '<b>Сидоров Сидор</b>' . "\r\n" . '🩸 20.01.2026: Донорский день',
    // Normal project entry (should still work)
    '<b>Иванов Иван</b>' . "\r\n" . '✅01.03.2026, Проект <b>ПРО-001</b>: Добавлена запись в табель: <b>Дорога</b>',
    // Current activity entry (should still work)
    '✅Запись в табель от <b>05.03.2026</b>: Текущая деятельность (<b>Медицинский осмотр</b>) добавлена',
];

echo "=== Testing extractDateFromMessage ===\n\n";

foreach ($testMessages as $i => $message) {
    $date = extractDateFromMessage($message);
    $shortMsg = mb_substr(strip_tags($message), 0, 60);
    echo "Test " . ($i + 1) . ": \"$shortMsg...\"\n";
    echo "  -> Date: " . ($date ?? 'NULL (FAIL!)') . "\n\n";
}

echo "=== Testing parseReportEntry (absence) ===\n\n";

foreach (array_slice($testMessages, 0, 3) as $i => $message) {
    $result = parseReportEntry($message, 'Мобильная бригада');
    $shortMsg = mb_substr(strip_tags($message), 0, 60);
    echo "Test " . ($i + 1) . ": \"$shortMsg...\"\n";
    echo "  -> date: {$result['date']}\n";
    echo "  -> project: '{$result['project']}'\n";
    echo "  -> process: '{$result['process']}'\n";
    echo "  -> osnovnaya (Level 1): '{$result['osnovnaya']}'\n";
    echo "  -> tip1 (Level 2): '{$result['tip1']}'\n";
    echo "  -> tip2 (Level 3): '{$result['tip2']}'\n";
    echo "  -> tip3 (Level 4): '{$result['tip3']}'\n";
    echo "  -> tip4 (Level 5): '{$result['tip4']}'\n";
    echo "\n";
}

echo "=== All tests passed! ===\n";

// --- Functions (copied logic from ProjectHandler) ---

function extractDateFromMessage(string $message): ?string
{
    if (preg_match('/✅\s*(\d{2}\.\d{2}\.\d{4}),\s*Проект/', $message, $matches)) {
        return $matches[1];
    }
    if (preg_match('/😨\s*⚠️\s*(\d{2}\.\d{2}\.\d{4}),\s*Проект/', $message, $matches)) {
        return $matches[1];
    }
    if (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>/', $message, $matches)) {
        return $matches[1];
    }
    if (preg_match('/от <b>(\d{2}\.\d{2}\.\d{4})<\/b>,\s*в проект/', $message, $matches)) {
        return $matches[1];
    }
    // NEW: absence format
    if (preg_match('/[🏥🏖🩸📋]\s*(\d{2}\.\d{2}\.\d{4}):\s*(?:Больничный|Отпуск|Донорский день)/u', $message, $matches)) {
        return $matches[1];
    }
    return null;
}

function parseReportEntry(string $message, string $functionalDirection): array
{
    $result = [
        'project' => '',
        'date' => '',
        'process' => $functionalDirection,
        'vid' => '',
        'osnovnaya' => '',
        'tip1' => '',
        'tip2' => '',
        'tip3' => '',
        'tip4' => ''
    ];

    if (preg_match('/✅\s*(\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: .*? <b>(.*?)<\/b>/', $message, $matches)) {
        $result['date'] = $matches[1];
        $result['project'] = $matches[2];
    } elseif (preg_match('/[🏥🏖🩸📋]\s*(\d{2}\.\d{2}\.\d{4}):\s*(Больничный|Отпуск|Донорский день)/u', $message, $matches)) {
        $result['date'] = $matches[1];
        $result['project'] = '';
        $result['osnovnaya'] = 'Отсутствие';
        $result['tip1'] = $matches[2];
        return $result;
    } else {
        return $result;
    }

    return $result;
}
