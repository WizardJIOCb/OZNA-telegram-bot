<?php
// Простой тест парсинга

echo "====================================\n";
echo "ТЕСТ ПАРСИНГА ТЕКУЩЕЙ ДЕЯТЕЛЬНОСТИ\n";
echo "====================================\n\n";

// Тестовое сообщение текущей деятельности
$message = "<b>Зангаров Денис Исламович</b>\n✅Запись в табель от <b>25.10.2025</b>: Текущая деятельность (<b>📊 Общая деятельность</b>) добавлена";

// Regex из parseReportEntry
if (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>: Текущая деятельность \(<b>(.*?)<\/b>\) добавлена/', $message, $matches)) {
    echo "✅ Regex СОВПАЛ\n";
    echo "Дата: {$matches[1]}\n";
    echo "Описание: {$matches[2]}\n\n";
    
    $description = $matches[2];
    
    // Удаляем emoji (простая версия)
    $description = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $description);
    $description = trim($description);
    
    echo "Описание без emoji: {$description}\n\n";
    
    // Проверяем наличие скобок
    if (strpos($description, '(') === false) {
        echo "✅ Нет подкатегорий - main является основной активностью\n";
        echo "vid = {$description}\n";
        echo "osnovnaya = {$description}\n";
    } else {
        echo "❌ Есть подкатегорий\n";
    }
} else {
    echo "❌ Regex НЕ СОВПАЛ\n";
}

echo "\n====================================\n";
echo "ТЕСТ ПАРСИНГА ПРОЕКТНОЙ ДЕЯТЕЛЬНОСТИ\n";
echo "====================================\n\n";

// Тестовое сообщение проектной деятельности с проектом
$message2 = "<b>Сибагатуллин Ирек Нургаязович</b>\n✅24.10.2025, Проект <b>ОИ833</b>: Добавлена запись в табель: <b>📝 Прочее (Проектная деятельность) (Ознакомление с проектом. Подготовка к командировке.)</b>";

if (preg_match('/✅(\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: .*? <b>(.*?)<\/b>/', $message2, $matches)) {
    echo "✅ Regex СОВПАЛ\n";
    echo "Дата: {$matches[1]}\n";
    echo "Проект: {$matches[2]}\n";
    echo "Описание: {$matches[3]}\n\n";
    
    $description = $matches[3];
    
    // Удаляем emoji (простая версия)
    $description = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $description);
    $description = trim($description);
    
    echo "Описание без emoji: {$description}\n";
    
    // Простой парсинг: "Прочее (Проектная деятельность) (детали)"
    if (preg_match('/^([^(]+)\s*\((.+)\)$/u', $description, $mainMatches)) {
        echo "main: {$mainMatches[1]}\n";
        echo "rest: {$mainMatches[2]}\n";
        
        if ($mainMatches[1] === 'Прочее') {
            echo "✅ Это 'Прочее' - нужна специальная обработка\n";
        }
    }
} else {
    echo "❌ Regex НЕ СОВПАЛ\n";
}
