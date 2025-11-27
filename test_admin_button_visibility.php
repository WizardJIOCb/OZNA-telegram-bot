<?php
/**
 * Тест видимости кнопки "Админка" для разных уровней доступа
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== ТЕСТ ВИДИМОСТИ КНОПКИ 'АДМИНКА' ===\n\n";

$messageService = new MessageService();

// Уровни доступа для тестирования
$testLevels = [
    0 => 'Гость',
    1 => 'Пользователь',
    2 => 'Супервайзер',
    3 => 'Администратор',
    4 => 'Суперадминистратор'
];

echo "Проверка отображения кнопки 'Админка' для разных уровней:\n";
echo "===========================================================\n\n";

foreach ($testLevels as $level => $levelName) {
    echo "Уровень {$level} ({$levelName}):\n";
    echo str_repeat('-', 50) . "\n";
    
    if ($level == 0) {
        // Для гостей используем специальный метод
        $keyboard = $messageService->createGuestMainMenuReplyKeyboard();
        echo "   Используется: createGuestMainMenuReplyKeyboard()\n";
    } else {
        // Для остальных используем обычный метод с передачей уровня
        $keyboard = $messageService->createMainMenuReplyKeyboard($level);
        echo "   Используется: createMainMenuReplyKeyboard({$level})\n";
    }
    
    echo "   Кнопки в меню:\n";
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            echo "      • {$button['text']}\n";
        }
    }
    
    // Проверка наличия кнопки "Админка"
    $hasAdminButton = false;
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            if (strpos($button['text'], 'Админка') !== false) {
                $hasAdminButton = true;
                break 2;
            }
        }
    }
    
    echo "\n   Кнопка '⚙️ Админка': ";
    if ($level >= 2) {
        // Должна быть видна для уровня 2 и выше
        if ($hasAdminButton) {
            echo "✅ ВИДНА (правильно)\n";
        } else {
            echo "❌ НЕ ВИДНА (ошибка!)\n";
        }
    } else {
        // Должна быть скрыта для уровня 0 и 1
        if (!$hasAdminButton) {
            echo "✅ СКРЫТА (правильно)\n";
        } else {
            echo "❌ ВИДНА (ошибка!)\n";
        }
    }
    
    echo "\n";
}

echo "\n=== ИТОГОВАЯ ТАБЛИЦА ===\n\n";
echo "| Уровень | Название             | Кнопка 'Админка' |\n";
echo "|---------|----------------------|------------------|\n";

foreach ($testLevels as $level => $levelName) {
    if ($level == 0) {
        $keyboard = $messageService->createGuestMainMenuReplyKeyboard();
    } else {
        $keyboard = $messageService->createMainMenuReplyKeyboard($level);
    }
    
    $hasAdminButton = false;
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            if (strpos($button['text'], 'Админка') !== false) {
                $hasAdminButton = true;
                break 2;
            }
        }
    }
    
    $levelStr = str_pad($level, 7);
    $nameStr = str_pad($levelName, 20);
    $buttonStr = $hasAdminButton ? "✅ Видна" : "❌ Скрыта";
    
    echo "| {$levelStr} | {$nameStr} | {$buttonStr}         |\n";
}

echo "\n";

echo "=== ПРАВИЛА ОТОБРАЖЕНИЯ ===\n\n";
echo "Кнопка '⚙️ Админка' отображается:\n";
echo "  ✅ Для уровня 2 и выше (Супервайзер, Администратор, Суперадминистратор)\n\n";

echo "Кнопка '⚙️ Админка' НЕ отображается:\n";
echo "  ❌ Для уровня 0 (Гость)\n";
echo "  ❌ Для уровня 1 (Пользователь)\n\n";

echo "=== ЧТО ИЗМЕНИЛОСЬ ===\n\n";
echo "БЫЛО:\n";
echo "  - Все пользователи видели кнопку 'Админка'\n";
echo "  - При нажатии пользователи без прав получали отказ\n\n";

echo "СТАЛО:\n";
echo "  - Кнопка видна только пользователям с уровнем >= 2\n";
echo "  - Гости и обычные пользователи не видят кнопку вообще\n";
echo "  - Чище и понятнее интерфейс\n\n";

echo "✅ ТЕСТ ЗАВЕРШЁН!\n";
