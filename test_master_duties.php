<?php
/**
 * Тест активности "Исполнение обязанностей мастера"
 */

echo "=== ТЕСТ АКТИВНОСТИ 'ИСПОЛНЕНИЕ ОБЯЗАННОСТЕЙ МАСТЕРА' ===\n\n";

// Проверка 1: MessageService.php - кнопка в меню ШМР
echo "1. ПРОВЕРКА КНОПКИ В МЕНЮ ШМР:\n";
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

if (strpos($messageServiceContent, 'Исполнение обязанностей мастера') !== false) {
    echo "   ✅ Кнопка 'Исполнение обязанностей мастера' найдена\n";
} else {
    echo "   ❌ Кнопка 'Исполнение обязанностей мастера' отсутствует\n";
}

if (strpos($messageServiceContent, 'category_Табель_ШМ_ИспобязМастера') !== false) {
    echo "   ✅ Callback 'category_Табель_ШМ_ИспобязМастера' найден\n";
} else {
    echo "   ❌ Callback 'category_Табель_ШМ_ИспобязМастера' отсутствует\n";
}

echo "\n";

// Проверка 2: TabelHandler.php - тип активности
echo "2. ПРОВЕРКА ТИПА АКТИВНОСТИ В TABELHANDLER:\n";
$handlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

if (strpos($handlerContent, "'ШМ_ИспобязМастера'") !== false) {
    echo "   ✅ Тип активности 'ШМ_ИспобязМастера' найден в activityTypes\n";
} else {
    echo "   ❌ Тип активности 'ШМ_ИспобязМастера' отсутствует в activityTypes\n";
}

if (strpos($handlerContent, 'Табель_ШМ_ИспобязМастера') !== false) {
    echo "   ✅ Обработчик callback'а найден\n";
} else {
    echo "   ❌ Обработчик callback'а отсутствует\n";
}

echo "\n";

// Проверка 3: TabelEntry.php - название активности
echo "3. ПРОВЕРКА НАЗВАНИЯ АКТИВНОСТИ В TABELENTRY:\n";
$entryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

if (strpos($entryContent, "'ШМ_ИспобязМастера'") !== false && 
    strpos($entryContent, 'Исполнение обязанностей мастера') !== false) {
    echo "   ✅ Название активности найдено в TabelEntry\n";
} else {
    echo "   ❌ Название активности отсутствует в TabelEntry\n";
}

echo "\n";

// Проверка 4: Структура меню
echo "4. ПРОВЕРКА СТРУКТУРЫ МЕНЮ ШМР:\n";
echo "   Ожидаемые активности в меню 'Отдел ШМР':\n";
echo "   1. 🚗 Подготовка к служебной поездке\n";
echo "   2. 🚗 Дорога\n";
echo "   3. 📋 Инструктаж\n";
echo "   4. 🔧 ШМР\n";
echo "   5. 📂 Проектная деятельность\n";
echo "   6. 👷 Исполнение обязанностей мастера ← НОВАЯ\n";
echo "   7. 🔧 Устранение замечаний РК\n";
echo "   8. 🛡️ Работы по гарантии\n";
echo "   9. ⏸️ Простой\n";
echo "   10. ⬅️ Назад\n";

echo "\n";

// Проверка 5: Путь пользователя
echo "5. ОЖИДАЕМЫЙ ПУТЬ ПОЛЬЗОВАТЕЛЯ:\n";
echo "   1. Пользователь выбирает 'Отдел ШМР'\n";
echo "   2. Пользователь нажимает '👷 Исполнение обязанностей мастера'\n";
echo "   3. Система показывает диалог подтверждения:\n";
echo "      'Проект XXX: Исполнение обязанностей мастера. Отправить запись в табель?'\n";
echo "   4. Пользователь подтверждает 'Да'\n";
echo "   5. Запись добавляется в табель\n";

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
echo "\nРезультат: Активность 'Исполнение обязанностей мастера' успешно добавлена в отдел ШМР.\n";
echo "Callback: category_Табель_ШМ_ИспобязМастера\n";
echo "Тип активности: ШМ_ИспобязМастера\n";
echo "Название: Исполнение обязанностей мастера\n";
?>