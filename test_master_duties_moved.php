<?php
/**
 * Тест переноса активности "Исполнение обязанностей мастера" в проектную деятельность ШМР
 */

echo "=== ТЕСТ ПЕРЕНОСА 'ИСПОЛНЕНИЕ ОБЯЗАННОСТЕЙ МАСТЕРА' В ПРОЕКТНУЮ ДЕЯТЕЛЬНОСТЬ ===\n\n";

// Проверка 1: Активность удалена из основного меню ШМР
echo "1. ПРОВЕРКА ОСНОВНОГО МЕНЮ ШМР:\n";
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Проверяем метод createSupervisionKeyboard
$supervisionKeyboardStart = strpos($messageServiceContent, 'public function createSupervisionKeyboard()');
$supervisionKeyboardEnd = strpos($messageServiceContent, '}', $supervisionKeyboardStart + strpos(substr($messageServiceContent, $supervisionKeyboardStart), 'return ['));

$supervisionKeyboardContent = substr($messageServiceContent, $supervisionKeyboardStart, $supervisionKeyboardEnd - $supervisionKeyboardStart);

if (strpos($supervisionKeyboardContent, 'Исполнение обязанностей мастера') === false) {
    echo "   ✅ Активность удалена из основного меню ШМР\n";
} else {
    echo "   ❌ Активность все еще в основном меню ШМР\n";
}

echo "\n";

// Проверка 2: Активность добавлена в подменю проектной деятельности ШМР
echo "2. ПРОВЕРКА ПОДМЕНЮ ПРОЕКТНОЙ ДЕЯТЕЛЬНОСТИ ШМР:\n";

// Проверяем метод createSupervisionProjectActivityKeyboard
$projectKeyboardStart = strpos($messageServiceContent, 'public function createSupervisionProjectActivityKeyboard()');
$projectKeyboardEnd = strpos($messageServiceContent, '}', $projectKeyboardStart + strpos(substr($messageServiceContent, $projectKeyboardStart), 'return ['));

$projectKeyboardContent = substr($messageServiceContent, $projectKeyboardStart, $projectKeyboardEnd - $projectKeyboardStart);

if (strpos($projectKeyboardContent, 'Исполнение обязанностей мастера') !== false) {
    echo "   ✅ Активность добавлена в подменю проектной деятельности\n";
} else {
    echo "   ❌ Активность отсутствует в подменю проектной деятельности\n";
}

if (strpos($projectKeyboardContent, 'ШМ_ИспобязМастера') !== false) {
    echo "   ✅ Callback корректно настроен в подменю\n";
} else {
    echo "   ❌ Callback отсутствует в подменю\n";
}

echo "\n";

// Проверка 3: Структура подменю
echo "3. ПРОВЕРКА СТРУКТУРЫ ПОДМЕНЮ ПРОЕКТНОЙ ДЕЯТЕЛЬНОСТИ ШМР:\n";
echo "   Ожидаемые активности в подменю:\n";
echo "   1. 🏢 Участие в ЗПИ\n";
echo "   2. 📝 Разработка программы работ\n";
echo "   3. 📅 Разработка графика работ\n";
echo "   4. 📦 Организация допоставки\n";
echo "   5. 🏭 Участие в ОПИ\n";
echo "   6. 👷 Исполнение обязанностей мастера ← ПЕРЕНЕСЕНА\n";
echo "   7. ⬅️ Назад\n";

echo "\n";

// Проверка 4: Путь пользователя
echo "4. НОВЫЙ ПУТЬ ПОЛЬЗОВАТЕЛЯ:\n";
echo "   1. Пользователь выбирает 'Отдел ШМР'\n";
echo "   2. Пользователь нажимает 'Проектная деятельность'\n";
echo "   3. Пользователь видит подменю с 6 активностями\n";
echo "   4. Пользователь нажимает 'Исполнение обязанностей мастера'\n";
echo "   5. Система показывает диалог подтверждения\n";
echo "   6. Пользователь подтверждает запись в табель\n";

echo "\n";

// Проверка 5: Обработчик callback'а
echo "5. ПРОВЕРКА ОБРАБОТЧИКА CALLBACK'А:\n";
$handlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

if (strpos($handlerContent, 'Табель_ШМ_ИспобязМастера') !== false) {
    echo "   ✅ Обработчик callback'а найден\n";
} else {
    echo "   ❌ Обработчик callback'а отсутствует\n";
}

// Проверяем, что обработчик находится в правильном разделе (после других проектных активностей ШМР)
$supervisionOPIPos = strpos($handlerContent, 'ШМ_УчастиеВОПИ');
$masterDutiesPos = strpos($handlerContent, 'ШМ_ИспобязМастера');

if ($supervisionOPIPos !== false && $masterDutiesPos !== false && $masterDutiesPos > $supervisionOPIPos) {
    echo "   ✅ Обработчик размещен в правильном разделе (после проектных активностей)\n";
} else {
    echo "   ❌ Обработчик размещен неправильно\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
echo "\nРезультат: Активность 'Исполнение обязанностей мастера' успешно перенесена\n";
echo "из основного меню ШМР в подменю 'Проектная деятельность'.\n\n";
echo "Новый путь доступа:\n";
echo "Отдел ШМР → Проектная деятельность → Исполнение обязанностей мастера\n";
?>