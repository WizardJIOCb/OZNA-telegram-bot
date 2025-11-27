<?php

echo "=== ПРОВЕРКА ИСПРАВЛЕНИЯ МАППИНГА ДЛЯ ШЕФ-НАЛАДОЧНЫХ РАБОТ ===\n\n";

echo "🔍 АНАЛИЗ ФАЙЛА TABELENTRY.PHP:\n";
echo "===============================\n\n";

// Читаем содержимое файла
$fileContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

echo "1. ПОИСК МАППИНГА 'ШефНаладочныеРаботы':\n";
echo "----------------------------------------\n";

// Ищем строку с маппингом
if (strpos($fileContent, "'ШефНаладочныеРаботы' =>") !== false) {
    echo "✅ МАППИНГ НАЙДЕН\n";
    
    // Извлекаем строку с маппингом
    $pattern = "/'ШефНаладочныеРаботы'\s*=>\s*'([^']+)'/";
    if (preg_match($pattern, $fileContent, $matches)) {
        $mappedValue = $matches[1];
        echo "   Значение: '$mappedValue'\n";
        
        // Проверяем наличие эмоджи
        if (strpos($mappedValue, '🔧') !== false) {
            echo "   ✅ Содержит эмоджи: 🔧\n";
        } else {
            echo "   ❌ НЕ содержит эмоджи\n";
        }
        
        // Проверяем наличие полного названия
        if (strpos($mappedValue, 'Шеф-наладочные работы') !== false) {
            echo "   ✅ Содержит полное название: 'Шеф-наладочные работы'\n";
        } else {
            echo "   ❌ НЕ содержит полное название\n";
        }
        
    } else {
        echo "   ❌ ОШИБКА: Не удалось извлечь значение маппинга\n";
    }
} else {
    echo "❌ МАППИНГ НЕ НАЙДЕН\n";
}

echo "\n2. СРАВНЕНИЕ С ЭТАЛОНОМ (УчастиеВОПИ):\n";
echo "=====================================\n";

// Ищем маппинг для УчастиеВОПИ
$opiPattern = "/'УчастиеВОПИ'\s*=>\s*'([^']+)'/";
if (preg_match($opiPattern, $fileContent, $opiMatches)) {
    $opiValue = $opiMatches[1];
    echo "УчастиеВОПИ: '$opiValue'\n";
    
    if (preg_match($pattern, $fileContent, $shefMatches)) {
        $shefValue = $shefMatches[1];
        echo "ШефНаладочныеРаботы: '$shefValue'\n";
        
        // Проверяем структурное соответствие
        $opiHasEmoji = preg_match('/[\x{1F000}-\x{1F9FF}]/u', $opiValue);
        $shefHasEmoji = preg_match('/[\x{1F000}-\x{1F9FF}]/u', $shefValue);
        
        if ($opiHasEmoji && $shefHasEmoji) {
            echo "✅ Оба маппинга содержат эмоджи\n";
        } else {
            echo "❌ Несоответствие в наличии эмоджи\n";
        }
    }
} else {
    echo "❌ Не удалось найти эталонный маппинг для УчастиеВОПИ\n";
}

echo "\n3. ПРОВЕРКА ПОЛОЖЕНИЯ В ФАЙЛЕ:\n";
echo "==============================\n";

// Проверяем, что маппинг находится в правильном месте
$activityTypeMappingsStart = strpos($fileContent, 'private array $activityTypeMappings');
$shefMappingPos = strpos($fileContent, "'ШефНаладочныеРаботы' =>");

if ($activityTypeMappingsStart !== false && $shefMappingPos !== false) {
    if ($shefMappingPos > $activityTypeMappingsStart) {
        echo "✅ Маппинг находится в правильном массиве\n";
    } else {
        echo "❌ Маппинг находится в неправильном месте\n";
    }
} else {
    echo "❌ Не удалось определить положение маппинга\n";
}

echo "\n4. ОЖИДАЕМЫЙ РЕЗУЛЬТАТ В ОТЧЕТЕ:\n";
echo "================================\n";

if (preg_match($pattern, $fileContent, $matches)) {
    $expectedValue = $matches[1];
    echo "Теперь в отчете должно отображаться:\n";
    echo "\"✅03.10.2025, Проект ХХХ: Добавлена запись в табель: $expectedValue\"\n\n";
    echo "Вместо:\n";
    echo "\"✅03.10.2025, Проект ХХХ: Добавлена запись в табель: ШефНаладочныеРаботы\"\n";
} else {
    echo "❌ Не удалось определить ожидаемый результат\n";
}

echo "\n🎯 ЗАКЛЮЧЕНИЕ:\n";
echo "==============\n";
echo "Если все проверки показывают ✅, то исправление выполнено успешно.\n";
echo "Теперь отчет будет содержать эмоджи и полное описательное название.\n";

?>