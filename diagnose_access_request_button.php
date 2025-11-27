<?php
/**
 * Диагностика работы кнопки "Запросить доступ"
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== ДИАГНОСТИКА КНОПКИ 'ЗАПРОСИТЬ ДОСТУП' ===\n\n";

// 1. Проверяем, что кнопка есть в меню
echo "1. Проверка наличия кнопки в главном меню для гостей:\n";
echo "========================================================\n";
$messageService = new MessageService();
$guestKeyboard = $messageService->createGuestMainMenuReplyKeyboard();

$hasButton = false;
foreach ($guestKeyboard as $row) {
    foreach ($row as $button) {
        echo "   Кнопка: {$button['text']}\n";
        if (strpos($button['text'], 'Запросить доступ') !== false) {
            $hasButton = true;
        }
    }
}

if ($hasButton) {
    echo "\n✅ Кнопка '🔐 Запросить доступ' найдена в меню\n\n";
} else {
    echo "\n❌ ОШИБКА: Кнопка 'Запросить доступ' НЕ найдена!\n\n";
    exit(1);
}

// 2. Проверяем обработчик в TextmenuCommand
echo "2. Проверка обработчика в TextmenuCommand.php:\n";
echo "================================================\n";

$textmenuFile = __DIR__ . '/CustomCommands/TextmenuCommand.php';
if (!file_exists($textmenuFile)) {
    echo "❌ ОШИБКА: Файл TextmenuCommand.php не найден!\n\n";
    exit(1);
}

$content = file_get_contents($textmenuFile);

// Проверяем наличие обработчика для кнопки "Запросить доступ"
if (strpos($content, 'Запросить доступ') !== false) {
    echo "✅ Обработчик для кнопки найден в TextmenuCommand.php\n";
} else {
    echo "❌ ОШИБКА: Обработчик для кнопки НЕ найден в TextmenuCommand.php!\n";
}

// Проверяем наличие метода handleAccessRequest
if (strpos($content, 'function handleAccessRequest') !== false || 
    strpos($content, 'handleAccessRequest(') !== false) {
    echo "✅ Метод handleAccessRequest найден\n\n";
} else {
    echo "❌ ОШИБКА: Метод handleAccessRequest НЕ найден!\n\n";
}

// 3. Симулируем процесс обработки
echo "3. Симуляция обработки нажатия кнопки:\n";
echo "========================================\n";

// Симулируем разные варианты текста кнопки
$testTexts = [
    '🔐 Запросить доступ',
    'Запросить доступ',
    '🔐 Запросить доступ ',  // с пробелом
    ' 🔐 Запросить доступ',  // с пробелом в начале
];

foreach ($testTexts as $testText) {
    $trimmed = trim($testText);
    echo "   Тест текста: '{$testText}'\n";
    echo "   После trim(): '{$trimmed}'\n";
    
    // Проверяем точное совпадение
    if ($trimmed === '🔐 Запросить доступ' || $trimmed === 'Запросить доступ') {
        echo "   ✅ Совпадение найдено!\n";
    } else {
        echo "   ❌ Совпадение НЕ найдено\n";
    }
    echo "\n";
}

// 4. Проверяем структуру обработки в TextmenuCommand
echo "4. Анализ структуры обработки:\n";
echo "================================\n";

// Ищем switch/case для гостей
if (preg_match('/case.*Запросить доступ.*:/u', $content)) {
    echo "✅ Найден case для обработки кнопки 'Запросить доступ'\n";
} else {
    echo "⚠️  Case для обработки НЕ найден (возможно используется другая структура)\n";
}

// Ищем вызов handleAccessRequest
if (preg_match('/handleAccessRequest\s*\(/u', $content)) {
    echo "✅ Найден вызов метода handleAccessRequest\n";
} else {
    echo "❌ ОШИБКА: Вызов handleAccessRequest НЕ найден!\n";
}

echo "\n";

// 5. Проверяем логи (последние строки)
echo "5. Проверка логов:\n";
echo "===================\n";
echo "Для диагностики проблемы проверьте логи бота:\n";
echo "- Найдите файл логов (обычно в папке storage/logs/)\n";
echo "- Посмотрите последние записи при нажатии кнопки\n";
echo "- Ищите строки с 'TextmenuCommand' или 'handleAccessRequest'\n\n";

// 6. Рекомендации
echo "=== РЕКОМЕНДАЦИИ ПО ДИАГНОСТИКЕ ===\n\n";

echo "Если кнопка не работает, проверьте:\n\n";

echo "1. ПРОВЕРКА В БОТЕ:\n";
echo "   - Отправьте /start от имени гостя\n";
echo "   - Убедитесь, что видите кнопку '🔐 Запросить доступ'\n";
echo "   - Нажмите кнопку и наблюдайте реакцию\n\n";

echo "2. ПРОВЕРКА ЛОГОВ:\n";
echo "   - Откройте файлы логов бота\n";
echo "   - При нажатии кнопки должны появиться записи:\n";
echo "     * 'TextmenuCommand handling text: ...'\n";
echo "     * 'TextmenuCommand: handleAccessRequest called for user: ...'\n\n";

echo "3. ВОЗМОЖНЫЕ ПРОБЛЕМЫ:\n";
echo "   a) Текст кнопки не совпадает с ожидаемым в коде\n";
echo "   b) TextmenuCommand не зарегистрирован\n";
echo "   c) Другой обработчик перехватывает сообщение\n";
echo "   d) Ошибка в логике проверки уровня доступа\n\n";

echo "4. ЧТО ДЕЛАТЬ ДАЛЬШЕ:\n";
echo "   - Запустите этот скрипт: php diagnose_access_request_button.php\n";
echo "   - Нажмите кнопку в боте\n";
echo "   - Проверьте логи\n";
echo "   - Сообщите результаты для дальнейшей диагностики\n\n";

echo "✅ ДИАГНОСТИКА ЗАВЕРШЕНА\n";
echo "\nСледующий шаг: Попросите гостя нажать кнопку и отправьте мне логи бота.\n";
