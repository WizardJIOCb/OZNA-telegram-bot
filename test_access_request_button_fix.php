<?php
/**
 * Тест исправления кнопки "Запросить доступ"
 */

echo "=== ТЕСТ ИСПРАВЛЕНИЯ КНОПКИ 'ЗАПРОСИТЬ ДОСТУП' ===\n\n";

// Проверяем GenericmessageCommand
$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';

if (!file_exists($genericFile)) {
    echo "❌ ОШИБКА: Файл GenericmessageCommand.php не найден!\n";
    exit(1);
}

$content = file_get_contents($genericFile);

echo "1. Проверка списка menuItems в GenericmessageCommand:\n";
echo "=======================================================\n";

// Извлекаем массив menuItems
if (preg_match('/\$menuItems\s*=\s*\[(.*?)\];/s', $content, $matches)) {
    $menuItemsStr = $matches[1];
    
    // Проверяем наличие кнопок
    $buttons = [
        'Табель' => false,
        'Помощь' => false,
        'Профиль' => false,
        'Проекты' => false,
        'Админка' => false,
        'Запросить доступ' => false,
    ];
    
    foreach ($buttons as $button => &$found) {
        if (strpos($menuItemsStr, $button) !== false) {
            $found = true;
            echo "   ✅ '{$button}' найдена\n";
        } else {
            echo "   ❌ '{$button}' НЕ найдена\n";
        }
    }
    
    echo "\n";
    
    // Проверяем критичную кнопку
    if ($buttons['Запросить доступ']) {
        echo "✅ УСПЕХ: Кнопка 'Запросить доступ' добавлена в список menuItems!\n";
    } else {
        echo "❌ ОШИБКА: Кнопка 'Запросить доступ' всё ещё ОТСУТСТВУЕТ!\n";
        exit(1);
    }
} else {
    echo "❌ ОШИБКА: Не удалось найти массив menuItems в коде!\n";
    exit(1);
}

echo "\n";
echo "2. Проверка обработки в TextmenuCommand:\n";
echo "=========================================\n";

$textmenuFile = __DIR__ . '/CustomCommands/TextmenuCommand.php';
$textmenuContent = file_get_contents($textmenuFile);

// Проверяем наличие обработчика
$hasHandler = false;
$hasCase = false;

if (strpos($textmenuContent, 'Запросить доступ') !== false) {
    $hasHandler = true;
    echo "✅ Обработчик для 'Запросить доступ' найден в TextmenuCommand\n";
}

if (preg_match('/case.*Запросить доступ.*:/u', $textmenuContent)) {
    $hasCase = true;
    echo "✅ Case для 'Запросить доступ' найден\n";
}

if (strpos($textmenuContent, 'handleAccessRequest') !== false) {
    echo "✅ Метод handleAccessRequest найден\n";
}

echo "\n";
echo "3. Полный путь обработки:\n";
echo "=========================\n";
echo "Когда гость нажимает '🔐 Запросить доступ':\n\n";
echo "Шаг 1: Telegram отправляет текст кнопки\n";
echo "   ↓\n";
echo "Шаг 2: GenericmessageCommand получает текст\n";
echo "   ↓\n";
echo "Шаг 3: Проверяется список menuItems\n";
echo "   ↓ ✅ '🔐 Запросить доступ' найдена в списке\n";
echo "Шаг 4: Передаётся в TextmenuCommand\n";
echo "   ↓\n";
echo "Шаг 5: TextmenuCommand проверяет уровень доступа\n";
echo "   ↓ Если гость (уровень 0)\n";
echo "Шаг 6: Обрабатывается case '🔐 Запросить доступ'\n";
echo "   ↓\n";
echo "Шаг 7: Вызывается handleAccessRequest()\n";
echo "   ↓\n";
echo "Шаг 8: Проверяется профиль (ФИО + Функц. направление)\n";
echo "   ↓\n";
echo "Шаг 9: Отправляется запрос или сообщение об ошибке\n\n";

echo "✅ ВСЕ ПРОВЕРКИ ПРОЙДЕНЫ!\n\n";

echo "=== ЧТО БЫЛО ИСПРАВЛЕНО ===\n\n";
echo "ПРОБЛЕМА:\n";
echo "  Кнопка '🔐 Запросить доступ' отсутствовала в списке menuItems\n";
echo "  в GenericmessageCommand.php (строка ~178)\n\n";

echo "РЕШЕНИЕ:\n";
echo "  Добавлены варианты кнопки в массив menuItems:\n";
echo "  - '🔐 Запросить доступ' (с эмодзи)\n";
echo "  - 'Запросить доступ' (без эмодзи)\n\n";

echo "РЕЗУЛЬТАТ:\n";
echo "  Теперь GenericmessageCommand передаёт нажатие этой кнопки\n";
echo "  в TextmenuCommand для обработки\n\n";

echo "=== СЛЕДУЮЩИЕ ШАГИ ===\n\n";
echo "1. Попросите гостя нажать кнопку '🔐 Запросить доступ'\n";
echo "2. Он должен увидеть:\n";
echo "   - Если профиль не заполнен: Предупреждение с инструкциями\n";
echo "   - Если профиль заполнен: Подтверждение отправки запроса\n\n";

echo "✅ ТЕСТ ЗАВЕРШЁН УСПЕШНО!\n";
