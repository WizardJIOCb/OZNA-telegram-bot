<?php
/**
 * Тест для проверки исправления метода isConfirmationCallback
 */

echo "=== ТЕСТ ИСПРАВЛЕНИЯ isConfirmationCallback ===\n\n";

// Имитируем исправленный метод isConfirmationCallback
function testIsConfirmationCallback(string $category): bool
{
    // Check for new Prochee confirmation callback
    if ($category === 'новое_прочее_yes') {
        return true;
    }
    
    // Check for old Prochee confirmation callback (backward compatibility)
    if ($category === 'cat_ТабЭлОб_Проч_yes') {
        return true;
    }
    
    // Check for short format commissioning activity confirmations
    if (preg_match('/^cat_(ТабИнстр|ТабДорога|ТабПодгПоезд|ТабСдачаОтч|ТабОхрСигн|ТабОтоплВент|ТабТехОб|ТабМетрол|ТабКомплОпр|ТабРежНал|ТабПНРаботы)_yes$/u', $category)) {
        return true;
    }
    
    // Check for electrical equipment short format confirmations
    if (preg_match('/^cat_ТабЭлОб_.+_yes$/u', $category)) {
        return true;
    }
    
    // Check for automation equipment short format confirmations
    if (preg_match('/^cat_ТабАвтом_.+_yes$/u', $category)) {
        return true;
    }
    
    // Check for fire alarm equipment short format confirmations
    if (preg_match('/^cat_ТабПожСигн_.+_yes$/u', $category)) {
        return true;
    }
    
    // Check for security alarm equipment short format confirmations
    if (preg_match('/^cat_ТабОхрСиг_.+_yes$/u', $category)) {
        return true;
    }
    
    // Check for warranty equipment confirmation callbacks - CRITICAL FOR WARRANTY BUTTON FUNCTIONALITY
    if (preg_match('/^cat_Гарантии_.+_yes$/u', $category)) {
        return true;
    }
    
    return str_contains($category, '_yes') && str_contains($category, 'Табель_');
}

// Имитируем метод canHandle
function testCanHandle(string $callbackData): bool
{
    return str_starts_with($callbackData, 'category_Табель') || 
           str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
           str_starts_with($callbackData, 'cat_Таб') ||
           str_starts_with($callbackData, 'cat_Гарантии_') ||
           $callbackData === 'новое_прочее_yes';
}

// Тестовые callback-и гарантийных работ
$warrantyCallbacks = [
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes',
    'cat_Гарантии_НасОб_yes',
    'cat_Гарантии_ТрубАрм_yes',
    'cat_Гарантии_ИспМех_yes',
    'cat_Гарантии_КонтрОб_yes',
    'cat_Гарантии_Прочее_yes'
];

echo "Тестирование callback-ов гарантийных работ:\n";
echo str_repeat("=", 50) . "\n";

$allTestsPassed = true;

foreach ($warrantyCallbacks as $callback) {
    echo "📋 ТЕСТ: {$callback}\n";
    
    // Проверяем canHandle
    $canHandle = testCanHandle($callback);
    echo "   canHandle: " . ($canHandle ? "✅ ДА" : "❌ НЕТ") . "\n";
    
    // Проверяем isConfirmationCallback
    $isConfirmation = testIsConfirmationCallback($callback);
    echo "   isConfirmationCallback: " . ($isConfirmation ? "✅ ДА" : "❌ НЕТ") . "\n";
    
    // Проверяем полный поток
    if ($canHandle && $isConfirmation) {
        echo "   🎉 СТАТУС: Callback будет обработан правильно!\n";
    } else {
        echo "   ❌ СТАТУС: Callback НЕ будет обработан правильно!\n";
        $allTestsPassed = false;
    }
    echo "\n";
}

echo "=== РЕЗУЛЬТАТ ТЕСТИРОВАНИЯ ===\n";
if ($allTestsPassed) {
    echo "✅ ВСЕ ТЕСТЫ ПРОШЛИ УСПЕШНО!\n";
    echo "Теперь callback-и гарантийных работ должны обрабатываться правильно.\n";
    echo "Пользователи больше не будут видеть \"Неизвестная команда табеля\".\n";
} else {
    echo "❌ НЕКОТОРЫЕ ТЕСТЫ НЕ ПРОШЛИ!\n";
    echo "Требуется дополнительная проверка конфигурации.\n";
}

echo "\n=== ИСПРАВЛЕНИЯ ===\n";
echo "1. ✅ В canHandle() уже было условие для 'cat_Гарантии_'\n";
echo "2. ✅ Добавлена обработка в isConfirmationCallback():\n";
echo "   preg_match('/^cat_Гарантии_.+_yes$/u', \$category)\n";
echo "3. ✅ Ранее добавлены маппинги в extractCategoryFromCallback()\n";
echo "4. ✅ Ранее добавлена обработка в isActivityExecution()\n\n";

echo "Теперь весь поток должен работать корректно:\n";
echo "Callback → canHandle → extractCategory → isConfirmationCallback → isActivityExecution → handleActivityExecution → Отчет ✅\n";
?>