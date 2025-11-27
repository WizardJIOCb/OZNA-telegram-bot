<?php

echo "====================================\n";
echo "ТЕСТ ПАРСИНГА РК И ГАРАНТИИ\n";
echo "====================================\n\n";

$testMessages = [
    [
        'type' => 'Гарантия (новый формат)',
        'message' => "✅25.10.2025, Проект <b>ОИ766</b>: Добавлена запись в табель: <b>⚡ Работы по гарантии (Электрооборудование)</b>"
    ],
    [
        'type' => 'РК (новый формат)',
        'message' => "✅24.09.2025, Проект <b>ОИ711</b>: Добавлена запись в табель: <b>⚙️ Устранения замечаний РК (Раздел ТХ)</b>"
    ],
    [
        'type' => 'РК (старый формат)',
        'message' => "✅ Запись в табель от <b>22.09.2025</b>, в проект <b>ОИ795</b> о <b>🔧 Устранение замечаний РК (Шеф-монтаж) добавлена</b>"
    ]
];

foreach ($testMessages as $idx => $test) {
    echo "========================================\n";
    echo "ТЕСТ #" . ($idx + 1) . ": {$test['type']}\n";
    echo "========================================\n";
    echo "Сообщение:\n{$test['message']}\n\n";
    
    // Regex для нового формата (из строки 3506)
    if (preg_match('/✅(\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: .*? <b>(.*?)<\/b>/', $test['message'], $matches)) {
        echo "✅ REGEX СОВПАЛ (новый формат)\n";
        echo "  Дата: {$matches[1]}\n";
        echo "  Проект: {$matches[2]}\n";
        echo "  Описание: {$matches[3]}\n";
        
        // Удаляем emoji
        $description = preg_replace('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $matches[3]);
        $description = trim($description);
        echo "  Описание без emoji: {$description}\n";
        
        // Парсим основную часть
        if (preg_match('/^([^(]+)(?:\s*\((.+)\))?$/u', $description, $mainMatches)) {
            $main = trim($mainMatches[1]);
            $subs = isset($mainMatches[2]) ? $mainMatches[2] : '';
            echo "  Main: '{$main}'\n";
            echo "  Subs: '{$subs}'\n";
        }
    } else {
        echo "❌ REGEX НЕ СОВПАЛ (новый формат)\n";
    }
    
    // Проверяем старый формат
    if (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>, в проект <b>(.*?)<\/b> о <b>(.*?) добавлена<\/b>/', $test['message'], $matches)) {
        echo "✅ REGEX СОВПАЛ (старый формат РК)\n";
        echo "  Дата: {$matches[1]}\n";
        echo "  Проект: {$matches[2]}\n";
        echo "  Описание: {$matches[3]}\n";
    }
    
    echo "\n";
}
