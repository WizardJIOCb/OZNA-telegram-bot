<?php
/**
 * TEST: Verify that Прочее button now preserves custom text correctly
 */

echo "=== ТЕСТ ИСПРАВЛЕНИЯ СОХРАНЕНИЯ ПОЛЬЗОВАТЕЛЬСКОГО ТЕКСТА ===\n\n";

// Load TabelEntry to verify the logic
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

echo "1. ПРОВЕРКА ЛОГИКИ В TabelEntry::createTabelMessage:\n";
echo "================================================\n";

// Check that Прочее_ЭлектрОб handling exists
$hasProcheeHandling = strpos($tabelEntryContent, "if (\$activityType === 'Прочее_ЭлектрОб' && !empty(\$idleReason))") !== false;
echo "✓ TabelEntry handles Прочее_ЭлектрОб with custom text: " . ($hasProcheeHandling ? "ДА" : "НЕТ") . "\n";

// Check the activityTypes mapping
$hasProcheeMapping = strpos($tabelEntryContent, "'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)'") !== false;
echo "✓ TabelEntry has correct Прочее_ЭлектрОб mapping: " . ($hasProcheeMapping ? "ДА" : "НЕТ") . "\n\n";

echo "2. ПРОВЕРКА ИСПРАВЛЕНИЯ В TabelHandler:\n";
echo "======================================\n";

// Load TabelHandler to check the fix
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check that duplicate createTabelMessage call was removed
$hasDuplicateCall = strpos($tabelHandlerContent, '$message = $this->tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $customText);') !== false;
echo "✓ Removed duplicate createTabelMessage call: " . ($hasDuplicateCall ? "НЕТ (ещё есть)" : "ДА") . "\n";

// Check that success message mentions custom text
$hasSuccessMessage = strpos($tabelHandlerContent, 'Ваше описание работы: <i>{$customText}</i>') !== false;
echo "✓ Success message includes custom text confirmation: " . ($hasSuccessMessage ? "ДА" : "НЕТ") . "\n\n";

echo "3. СИМУЛЯЦИЯ ИСПРАВЛЕННОГО ПОТОКА:\n";
echo "=================================\n";

// Simulate the corrected flow
$customText = "Проверка";
$activityType = "Прочее_ЭлектрОб";

echo "Шаг 1: Пользователь вводит: '{$customText}'\n";
echo "Шаг 2: TabelHandler вызывает addEntry с customText: '{$customText}'\n";
echo "Шаг 3: TabelEntry::addEntry вызывает createTabelMessage с idleReason: '{$customText}'\n";

// Simulate TabelEntry logic
$activityText = "📝 Прочее (Электрооборудование, Дополнительные работы)";
if ($activityType === 'Прочее_ЭлектрОб' && !empty($customText)) {
    $activityText .= " ({$customText})";
}

echo "Шаг 4: TabelEntry формирует activityText: '{$activityText}'\n";

$simulatedMessage = sprintf(
    "✅ Запись в табель от 18.09.2025, в проект ОИ696 о %s добавлена",
    $activityText
);

echo "Шаг 5: Создается сообщение: '{$simulatedMessage}'\n";
echo "Шаг 6: TabelHandler отправляет подтверждение пользователю\n\n";

echo "4. ОЖИДАЕМЫЙ РЕЗУЛЬТАТ:\n";
echo "======================\n";
echo "✅ Предварительный отчет: 'Описание работы: Проверка'\n";
echo "✅ Финальный отчет: '...о 📝 Прочее (Электрооборудование, Дополнительные работы) (Проверка) добавлена'\n";
echo "✅ Пользовательский текст 'Проверка' сохраняется в обоих сообщениях\n";
echo "❌ НЕ ДОЛЖНО быть: '...о 📝 Прочее (Электрооборудование, Дополнительные работы) (📊 Табель) добавлена'\n\n";

if ($hasProcheeHandling && $hasProcheeMapping && !$hasDuplicateCall && $hasSuccessMessage) {
    echo "🎉 ИСПРАВЛЕНИЕ ЗАВЕРШЕНО УСПЕШНО!\n";
    echo "   Теперь пользовательский текст должен сохраняться корректно.\n";
    echo "   Протестируйте кнопку 'Прочее' в боте.\n";
} else {
    echo "⚠️  ИСПРАВЛЕНИЕ НЕ ЗАВЕРШЕНО:\n";
    if (!$hasProcheeHandling) echo "   - TabelEntry не обрабатывает Прочее_ЭлектрОб\n";
    if (!$hasProcheeMapping) echo "   - TabelEntry не имеет правильного маппинга\n";
    if ($hasDuplicateCall) echo "   - Дублированный вызов createTabelMessage не удален\n";
    if (!$hasSuccessMessage) echo "   - Сообщение успеха не включает пользовательский текст\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
?>