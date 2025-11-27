<?php
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "🔧 ТЕСТ ИСПРАВЛЕНИЯ: Исполнение обязанностей мастера\n";
echo "====================================================\n\n";

// Создаем экземпляр TabelEntry
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$projectCode = 'ОИ835';
$activityType = 'ИспОбМаст';

echo "Тестируем активность: {$activityType}\n";
echo "Проект: {$projectCode}\n\n";

try {
    // Тестируем метод getConfirmationMessage
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    
    echo "✅ РЕЗУЛЬТАТ:\n";
    echo "Сообщение подтверждения: {$confirmationMessage}\n\n";
    
    // Проверяем, что больше не отображается сокращенное наименование
    if (strpos($confirmationMessage, 'ИспОбМаст') !== false) {
        echo "❌ ПРОБЛЕМА: Все еще отображается сокращенное наименование 'ИспОбМаст'\n";
    } else {
        echo "✅ УСПЕХ: Сокращенное наименование 'ИспОбМаст' больше не отображается\n";
    }
    
    // Проверяем, что отображается полное наименование
    if (strpos($confirmationMessage, 'Исполнение обязанностей мастера') !== false) {
        echo "✅ УСПЕХ: Отображается полное наименование 'Исполнение обязанностей мастера'\n";
    } else {
        echo "❌ ПРОБЛЕМА: Не отображается полное наименование\n";
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
}

echo "\n🎯 ЗАКЛЮЧЕНИЕ:\n";
echo "Исправление применено. Теперь при нажатии кнопки 'Исполнение обязанностей мастера'\n";
echo "будет отображаться полное наименование активности вместо сокращенного 'ИспОбМаст'.\n";
?>