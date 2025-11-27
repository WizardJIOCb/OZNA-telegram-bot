<?php
require_once __DIR__ . '/vendor/autoload.php';

$testMessages = [
    // Текущая деятельность
    "<b>Зангаров Денис Исламович</b>\n✅Запись в табель от <b>25.10.2025</b>: Текущая деятельность (<b>📊 Общая деятельность</b>) добавлена",
    
    // Проектная деятельность с проектом
    "<b>Сибагатуллин Ирек Нургаязович</b>\n✅24.10.2025, Проект <b>ОИ833</b>: Добавлена запись в табель: <b>📝 Прочее (Проектная деятельность) (Ознакомление с проектом. Подготовка к командировке.)</b>",
    
    // Обычная запись с проектом
    "<b>Коземаслов Сергей Васильевич</b>\n✅25.10.2025, Проект <b>ОИ779</b>: Добавлена запись в табель: <b>📝 Прочее (Технологическое оборудование) (Монтаж НА)</b>",
];

foreach ($testMessages as $idx => $message) {
    echo "\n========================================\n";
    echo "ТЕСТ #" . ($idx + 1) . "\n";
    echo "========================================\n";
    echo "Сообщение:\n$message\n\n";
    
    // Regex 1: Project-based entry
    if (preg_match('/✅(\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: .*? <b>(.*?)<\/b>/', $message, $matches)) {
        echo "✅ REGEX 1 (Project): СОВПАЛ\n";
        echo "  Дата: {$matches[1]}\n";
        echo "  Проект: {$matches[2]}\n";
        echo "  Описание: {$matches[3]}\n";
    } else {
        echo "❌ REGEX 1 (Project): НЕ СОВПАЛ\n";
    }
    
    // Regex 2: INCIDENT format
    if (preg_match('/😨 ⚠️ (\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>.*?(Простой .*?)<\/b>/', $message, $matches)) {
        echo "✅ REGEX 2 (Incident): СОВПАЛ\n";
        echo "  Дата: {$matches[1]}\n";
        echo "  Проект: {$matches[2]}\n";
        echo "  Описание: {$matches[3]}\n";
    } else {
        echo "❌ REGEX 2 (Incident): НЕ СОВПАЛ\n";
    }
    
    // Regex 3: Current activity format
    if (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>: Текущая деятельность \(<b>(.*?)<\/b>\) добавлена/', $message, $matches)) {
        echo "✅ REGEX 3 (Current Activity): СОВПАЛ\n";
        echo "  Дата: {$matches[1]}\n";
        echo "  Описание: {$matches[2]}\n";
    } else {
        echo "❌ REGEX 3 (Current Activity): НЕ СОВПАЛ\n";
    }
}
