<?php

echo "=== ТЕСТИРОВАНИЕ CALLBACK 'cat_ШМР_АналФунд' ===\n\n";

// Включаем необходимые файлы
require_once "vendor/autoload.php";
require_once "CustomCommands/Services/DatabaseService.php";
require_once "CustomCommands/Services/MessageService.php";
require_once "CustomCommands/Services/TabelEntry.php";
require_once "CustomCommands/Handlers/TabelHandler.php";
require_once "CustomCommands/Services/ProjectSelector.php";
require_once "CustomCommands/Services/AdminService.php";

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Тестируем специфический callback
$callbackData = 'cat_ШМР_АналФунд';

try {
    // Создаем сервисы
    $dbService = new DatabaseService();
    $messageService = new MessageService($dbService);
    $tabelEntry = new TabelEntry($dbService, $messageService);
    $projectSelector = new ProjectSelector($dbService, $messageService);
    $adminService = new AdminService($dbService);
    
    // Создаем обработчик
    $handler = new TabelHandler($dbService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    echo "1. ПРОВЕРЯЕМ canHandle():\n";
    $canHandle = $handler->canHandle($callbackData);
    echo "   Callback: '{$callbackData}'\n";
    echo "   canHandle result: " . ($canHandle ? 'true ✅' : 'false ❌') . "\n\n";
    
    if (!$canHandle) {
        echo "❌ ОШИБКА: TabelHandler не может обработать этот callback!\n";
        exit(1);
    }
    
    echo "2. ТЕСТИРУЕМ МАППИНГ КАТЕГОРИЙ:\n";
    // Используем рефлексию для доступа к private методу
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('extractCategoryFromCallback');
    $method->setAccessible(true);
    
    $category = $method->invoke($handler, $callbackData);
    echo "   Original callback: '{$callbackData}'\n";
    echo "   Mapped category: '{$category}'\n\n";
    
    if ($category === 'Табель_АнализФундамента') {
        echo "✅ УСПЕХ: Callback правильно мапится на 'Табель_АнализФундамента'\n";
        echo "✅ Это должно привести к диалогу подтверждения\n\n";
    } else {
        echo "❌ ОШИБКА: Неправильный маппинг! Ожидался 'Табель_АнализФундамента'\n\n";
    }
    
    echo "3. ПРОВЕРЯЕМ МЕТОД getConfirmationMessage():\n";
    $projectCode = 'TEST-PROJECT';
    $confirmationText = $tabelEntry->getConfirmationMessage('ПроверкаМонтажнойПлощадки', $projectCode);
    echo "   Activity type: 'ПроверкаМонтажнойПлощадки'\n";
    echo "   Project code: '{$projectCode}'\n";
    echo "   Confirmation text: '{$confirmationText}'\n\n";
    
    echo "4. ПРОВЕРЯЕМ CALLBACK ДЛЯ КНОПКИ ПОДТВЕРЖДЕНИЯ:\n";
    $confirmationCallback = 'cat_ШМР_АналФунд_yes';
    echo "   Expected confirmation callback: '{$confirmationCallback}'\n";
    echo "   Callback length: " . strlen($confirmationCallback) . " bytes (limit: 64)\n\n";
    
    if (strlen($confirmationCallback) <= 64) {
        echo "✅ УСПЕХ: Callback подтверждения в пределах лимита\n\n";
    } else {
        echo "❌ ОШИБКА: Callback подтверждения слишком длинный!\n\n";
    }
    
    echo "5. СИМУЛЯЦИЯ ПОЛНОГО ПОТОКА:\n";
    echo "   Пользователь нажимает: 'Проверка монтажной площадки'\n";
    echo "   → Callback: '{$callbackData}'\n";
    echo "   → Category: '{$category}'\n";
    echo "   → Должен показать диалог подтверждения\n";
    echo "   → При нажатии 'Да': '{$confirmationCallback}'\n";
    echo "   → Должен создать запись с правильным форматом\n\n";
    
    echo "6. ТЕСТИРУЕМ СОЗДАНИЕ СООБЩЕНИЯ:\n";
    $testMessage = $tabelEntry->createTabelMessage(
        'Тестовый Пользователь',
        '04.10.2025',
        'ХХХ',
        'ПроверкаМонтажнойПлощадки'
    );
    echo "   Generated message:\n   {$testMessage}\n\n";
    
    if (strpos($testMessage, '✅04.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки') !== false) {
        echo "✅ ПРАВИЛЬНЫЙ ФОРМАТ!\n";
    } else {
        echo "❌ НЕПРАВИЛЬНЫЙ ФОРМАТ!\n";
    }
    
    echo "\n=== РЕЗУЛЬТАТ ТЕСТИРОВАНИЯ ===\n";
    echo "Если все тесты прошли успешно, то проблема может быть:\n";
    echo "1. В коррупции callback данных на уровне Telegram\n";
    echo "2. В обработке callback в CallbackqueryCommand\n";
    echo "3. В другом месте цепочки вызовов\n";

} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}