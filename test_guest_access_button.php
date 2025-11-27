<?php
/**
 * Тестовый скрипт для проверки кнопки "Запросить доступ" в главном меню для гостей
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== ТЕСТ КНОПКИ 'ЗАПРОСИТЬ ДОСТУП' В ГЛАВНОМ МЕНЮ ===\n\n";

// Создаем экземпляр MessageService
$messageService = new MessageService();

echo "1. Проверяем постоянную клавиатуру для гостей:\n";
echo "================================================\n";
$guestKeyboard = $messageService->createGuestMainMenuReplyKeyboard();

echo "Клавиатура для гостей содержит следующие кнопки:\n";
foreach ($guestKeyboard as $row => $buttons) {
    foreach ($buttons as $button) {
        echo "  ✓ {$button['text']}\n";
    }
}

// Проверяем наличие кнопки "Запросить доступ"
$hasAccessButton = false;
foreach ($guestKeyboard as $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Запросить доступ') !== false) {
            $hasAccessButton = true;
            break 2;
        }
    }
}

if ($hasAccessButton) {
    echo "\n✅ УСПЕХ: Кнопка '🔐 Запросить доступ' найдена в главном меню для гостей!\n";
} else {
    echo "\n❌ ОШИБКА: Кнопка 'Запросить доступ' НЕ найдена в главном меню!\n";
}

echo "\n2. Проверяем постоянную клавиатуру для обычных пользователей:\n";
echo "================================================================\n";
$normalKeyboard = $messageService->createMainMenuReplyKeyboard();

echo "Клавиатура для обычных пользователей содержит следующие кнопки:\n";
foreach ($normalKeyboard as $row => $buttons) {
    foreach ($buttons as $button) {
        echo "  ✓ {$button['text']}\n";
    }
}

// Проверяем отсутствие кнопки "Запросить доступ" у обычных пользователей
$normalHasAccessButton = false;
foreach ($normalKeyboard as $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Запросить доступ') !== false) {
            $normalHasAccessButton = true;
            break 2;
        }
    }
}

if (!$normalHasAccessButton) {
    echo "\n✅ УСПЕХ: Кнопка 'Запросить доступ' правильно СКРЫТА для обычных пользователей!\n";
} else {
    echo "\n❌ ОШИБКА: Кнопка 'Запросить доступ' НЕ должна отображаться для обычных пользователей!\n";
}

echo "\n3. Сводка изменений:\n";
echo "===================\n";
echo "✓ Кнопка '🔐 Запросить доступ' добавлена в постоянное меню для гостей\n";
echo "✓ Кнопка видна только гостям (уровень доступа 0)\n";
echo "✓ Кнопка удалена из профиля гостей (избежание дублирования)\n";
echo "✓ Обработка кнопки добавлена в TextmenuCommand\n";

echo "\n4. Что нужно сделать для проверки в боте:\n";
echo "===========================================\n";
echo "1. Войдите в бота как гость (уровень доступа 0)\n";
echo "2. Отправьте команду /start\n";
echo "3. Проверьте, что в постоянном меню внизу есть кнопка '🔐 Запросить доступ'\n";
echo "4. Нажмите на кнопку '🔐 Запросить доступ'\n";
echo "5. Проверьте, что система правильно обрабатывает запрос:\n";
echo "   - Если профиль не заполнен - показывает предупреждение\n";
echo "   - Если профиль заполнен - отправляет запрос администраторам\n";
echo "6. Войдите в профиль и убедитесь, что там НЕТ кнопки 'Запросить доступ'\n";

echo "\n✅ ТЕСТ ЗАВЕРШЕН!\n";
