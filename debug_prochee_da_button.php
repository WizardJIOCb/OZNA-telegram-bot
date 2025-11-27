<?php
/**
 * DIAGNOSTIC: Debug "Да" button issue for Прочее
 */

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С КНОПКОЙ 'ДА' ===\n\n";

// Load TabelHandler to check current implementation
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. ПРОВЕРКА ОБРАБОТКИ CALLBACK 'новое_прочее_yes':\n";
echo "==============================================\n";

// Check canHandle method
$hasCanHandle = strpos($tabelHandlerContent, "\$callbackData === 'новое_прочее_yes'") !== false;
echo "✓ canHandle recognizes 'новое_прочее_yes': " . ($hasCanHandle ? "ДА" : "НЕТ") . "\n";

// Check isConfirmationCallback method
$hasConfirmation = strpos($tabelHandlerContent, "if (\$category === 'новое_прочее_yes')") !== false;
echo "✓ isConfirmationCallback recognizes 'новое_прочее_yes': " . ($hasConfirmation ? "ДА" : "НЕТ") . "\n";

// Check routing
$hasRouting = strpos($tabelHandlerContent, "\$category === 'новое_прочее_yes'") !== false;
echo "✓ routeTabelCallback handles 'новое_прочее_yes': " . ($hasRouting ? "ДА" : "НЕТ") . "\n";

// Check handler method exists
$hasMethod = strpos($tabelHandlerContent, 'function handleNewProcheeConfirmation') !== false;
echo "✓ handleNewProcheeConfirmation method exists: " . ($hasMethod ? "ДА" : "НЕТ") . "\n\n";

echo "2. ПРОВЕРКА СОЗДАНИЯ CALLBACK В handleCustomInput:\n";
echo "=================================================\n";

// Check which callback is created
$usesNewCallback = strpos($tabelHandlerContent, 'createConfirmationKeyboard("новое_прочее_yes")') !== false;
$usesOldCallback = strpos($tabelHandlerContent, 'createConfirmationKeyboard("cat_ТабЭлОб_Проч_yes")') !== false;

echo "✓ Uses NEW callback 'новое_прочее_yes': " . ($usesNewCallback ? "ДА" : "НЕТ") . "\n";
echo "✓ Uses OLD callback 'cat_ТабЭлОб_Проч_yes': " . ($usesOldCallback ? "ДА" : "НЕТ") . "\n\n";

echo "3. ПРОВЕРКА СОСТОЯНИЯ СИСТЕМЫ:\n";
echo "==============================\n";

if ($hasCanHandle && $hasConfirmation && $hasRouting && $hasMethod && $usesNewCallback) {
    echo "✅ ВСЕ КОМПОНЕНТЫ НА МЕСТЕ!\n\n";
    
    echo "💡 ВЕРОЯТНАЯ ПРИЧИНА: НЕ ПЕРЕЗАПУЩЕН ВЕБ-СЕРВЕР\n";
    echo "   После изменений в обработчиках требуется перезапуск сервера.\n\n";
    
    echo "🔧 РЕШЕНИЕ:\n";
    echo "   1. Остановите веб-сервер (Apache/Nginx/PHP-FPM)\n";
    echo "   2. Запустите веб-сервер заново\n";
    echo "   3. Протестируйте кнопку 'Прочее' снова\n\n";
    
} else {
    echo "❌ НАЙДЕНЫ ПРОБЛЕМЫ В КОДЕ:\n\n";
    
    if (!$hasCanHandle) {
        echo "⚠️ canHandle method НЕ распознает 'новое_прочее_yes'\n";
    }
    if (!$hasConfirmation) {
        echo "⚠️ isConfirmationCallback НЕ распознает 'новое_прочее_yes'\n";
    }
    if (!$hasRouting) {
        echo "⚠️ routeTabelCallback НЕ обрабатывает 'новое_прочее_yes'\n";
    }
    if (!$hasMethod) {
        echo "⚠️ handleNewProcheeConfirmation method отсутствует\n";
    }
    if (!$usesNewCallback) {
        echo "⚠️ handleCustomInput НЕ создает правильный callback\n";
    }
    
    echo "\n🔧 ТРЕБУЕТСЯ ИСПРАВЛЕНИЕ КОДА\n";
}

echo "4. ОЖИДАЕМЫЙ ПОТОК РАБОТЫ:\n";
echo "==========================\n";
echo "1. Пользователь нажимает 'Прочее' → устанавливается awaiting_custom_prochee_input = true\n";
echo "2. Пользователь вводит текст → GenericmessageCommand вызывает handleNewProcheeInput\n";
echo "3. TabelHandler создает подтверждение с кнопкой 'новое_прочее_yes'\n";
echo "4. Пользователь нажимает 'Да' → TabelHandler обрабатывает 'новое_прочее_yes'\n";
echo "5. Создается запись в табель с пользовательским описанием\n\n";

echo "=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>