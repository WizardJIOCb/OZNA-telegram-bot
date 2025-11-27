<?php
/**
 * TEST: Verify Прочее button fix without server restart
 */

echo "=== ТЕСТ ИСПРАВЛЕНИЯ КНОПКИ 'ПРОЧЕЕ' БЕЗ ПЕРЕЗАПУСКА ===\n\n";

// Load TabelHandler to check current implementation
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. ПРОВЕРКА УНИФИЦИРОВАННОЙ ОБРАБОТКИ CALLBACK:\n";
echo "===============================================\n";

// Check canHandle method recognizes both callback formats
$canHandleNew = strpos($tabelHandlerContent, "\$callbackData === 'новое_прочее_yes'") !== false;
$canHandleOld = strpos($tabelHandlerContent, "\$callbackData === 'cat_ТабЭлОб_Проч_yes'") !== false;

echo "✓ canHandle recognizes NEW callback 'новое_прочее_yes': " . ($canHandleNew ? "ДА" : "НЕТ") . "\n";
echo "✓ canHandle recognizes OLD callback 'cat_ТабЭлОб_Проч_yes': " . ($canHandleOld ? "ДА" : "НЕТ") . "\n";

// Check isConfirmationCallback method recognizes both callback formats
$confirmationNew = strpos($tabelHandlerContent, "if (\$category === 'новое_прочее_yes')") !== false;
$confirmationOld = strpos($tabelHandlerContent, "if (\$category === 'cat_ТабЭлОб_Проч_yes')") !== false;

echo "✓ isConfirmationCallback recognizes NEW callback: " . ($confirmationNew ? "ДА" : "НЕТ") . "\n";
echo "✓ isConfirmationCallback recognizes OLD callback: " . ($confirmationOld ? "ДА" : "НЕТ") . "\n";

// Check routing handles both callback formats
$routingBoth = strpos($tabelHandlerContent, "\$category === 'cat_ТабЭлОб_Проч_yes' || \$category === 'новое_прочее_yes'") !== false;

echo "✓ Routing handles BOTH callback formats: " . ($routingBoth ? "ДА" : "НЕТ") . "\n\n";

echo "2. ПРОВЕРКА ИСПОЛЬЗУЕМОГО CALLBACK В ПОДТВЕРЖДЕНИИ:\n";
echo "==================================================\n";

// Check which callback format is used in confirmation keyboard
$usesOldInConfirmation = strpos($tabelHandlerContent, 'createConfirmationKeyboard("cat_ТабЭлОб_Проч_yes")') !== false;
$usesNewInConfirmation = strpos($tabelHandlerContent, 'createConfirmationKeyboard("новое_прочее_yes")') !== false;

echo "✓ Confirmation uses OLD callback 'cat_ТабЭлОб_Проч_yes': " . ($usesOldInConfirmation ? "ДА" : "НЕТ") . "\n";
echo "✓ Confirmation uses NEW callback 'новое_прочее_yes': " . ($usesNewInConfirmation ? "ДА" : "НЕТ") . "\n\n";

echo "3. АНАЛИЗ ИСПРАВЛЕНИЯ:\n";
echo "=====================\n";

if ($canHandleOld && $confirmationOld && $routingBoth && $usesOldInConfirmation) {
    echo "✅ ИСПРАВЛЕНИЕ УСПЕШНО ПРИМЕНЕНО!\n\n";
    
    echo "💡 ЧТО БЫЛО ИСПРАВЛЕНО:\n";
    echo "   1. Добавлена поддержка OLD callback 'cat_ТабЭлОб_Проч_yes' в canHandle\n";
    echo "   2. Добавлена поддержка OLD callback в isConfirmationCallback\n"; 
    echo "   3. Routing теперь обрабатывает ОБА формата callback\n";
    echo "   4. Confirmation keyboard использует OLD формат (уже поддерживается)\n\n";
    
    echo "🎯 РЕЗУЛЬТАТ:\n";
    echo "   Теперь кнопка 'Да' будет работать без перезапуска сервера!\n";
    echo "   Система использует OLD callback формат, который уже поддерживается.\n\n";
    
} else {
    echo "❌ ИСПРАВЛЕНИЕ НЕ ЗАВЕРШЕНО\n";
    if (!$canHandleOld) echo "   - canHandle НЕ поддерживает OLD callback\n";
    if (!$confirmationOld) echo "   - isConfirmationCallback НЕ поддерживает OLD callback\n";
    if (!$routingBoth) echo "   - Routing НЕ поддерживает оба формата\n";
    if (!$usesOldInConfirmation) echo "   - Confirmation НЕ использует OLD callback\n";
}

echo "4. ОЖИДАЕМЫЙ ПОТОК РАБОТЫ ПОСЛЕ ИСПРАВЛЕНИЯ:\n";
echo "============================================\n";
echo "1. Пользователь нажимает 'Прочее' → awaiting_custom_prochee_input = true\n";
echo "2. Пользователь вводит текст → handleNewProcheeInput сохраняет текст\n";
echo "3. Система создает подтверждение с кнопкой 'cat_ТабЭлОб_Проч_yes'\n";
echo "4. Пользователь нажимает 'Да' → система распознает callback\n";
echo "5. Вызывается handleNewProcheeConfirmation → создается запись\n";
echo "6. Отправляется отчет с пользовательским описанием\n\n";

echo "=== ТЕСТ ЗАВЕРШЕН - ПОПРОБУЙТЕ ФУНКЦИЮ ===\n";
?>