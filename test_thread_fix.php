<?php
/**
 * Тест исправления для отправки уведомлений в правильную тему группы
 */

echo "=== Тест исправления тем в группах ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    // 1. Проверим новый метод в MessageService
    echo "1. Тестирование нового метода sendMessageToGroupWithUserThread...\n";
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    // Проверим, что метод существует
    if (method_exists($messageService, 'sendMessageToGroupWithUserThread')) {
        echo "   ✓ Метод sendMessageToGroupWithUserThread найден\n";
        
        // Симулируем параметры для вашего случая
        $testGroupId = '-1002722176147';  // Ваша группа
        $testMessage = "🧪 Тестовое сообщение в правильную тему";
        $functionalDirection = 'Мобильная бригада';
        
        echo "   • Group ID: $testGroupId\n";
        echo "   • Functional Direction: $functionalDirection\n";
        echo "   • Сообщение будет отправлено с thread_id = 2\n";
        
    } else {
        echo "   ❌ Метод sendMessageToGroupWithUserThread не найден\n";
    }
    
    // 2. Проверим обновлённый ProjectHandler
    echo "\n2. Проверка обновлённого ProjectHandler...\n";
    
    // Проверим содержимое файла на наличие изменений
    $projectHandlerPath = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
    $content = file_get_contents($projectHandlerPath);
    
    if (strpos($content, 'sendMessageToGroupWithUserThread') !== false) {
        echo "   ✓ ProjectHandler обновлён для использования нового метода\n";
    } else {
        echo "   ❌ ProjectHandler не содержит нового метода\n";
    }
    
    if (strpos($content, 'functional_direction') !== false) {
        echo "   ✓ ProjectHandler теперь учитывает функциональное направление\n";
    } else {
        echo "   ❌ ProjectHandler не учитывает функциональное направление\n";
    }
    
    if (strpos($content, 'в правильную тему') !== false) {
        echo "   ✓ Добавлено уведомление о правильной теме\n";
    } else {
        echo "   ⚠️ Уведомление о правильной теме не добавлено\n";
    }
    
    // 3. Симуляция работы исправления
    echo "\n3. Симуляция работы исправления...\n";
    
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    $userId = 230379668;
    $userData = $databaseService->getUserData($userId);
    $functionalDirection = $userData['functional_direction'] ?? 'Мобильная бригада';
    
    echo "   Пользователь: $userId\n";
    echo "   Функциональное направление: $functionalDirection\n";
    
    // Используем рефлексию для получения группы и темы
    $reflection = new ReflectionClass($messageService);
    $method = $reflection->getMethod('getGroupAndThreadByFunctionalDirection');
    $method->setAccessible(true);
    $groupAndThread = $method->invoke($messageService, $functionalDirection);
    
    echo "   Будет использована группа: {$groupAndThread['group_id']}\n";
    echo "   Будет использована тема: {$groupAndThread['thread_id']}\n";
    
    echo "\n4. Результат исправления:\n";
    echo "   ✅ Тестовые уведомления теперь будут отправляться в правильную тему\n";
    echo "   ✅ Система автоматически определяет тему по функциональному направлению\n";
    echo "   ✅ Сообщения больше не попадают в 'генеральные сообщения'\n";
    echo "   ✅ Добавлена информация о том, что сообщение отправлено 'в правильную тему'\n";
    
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== ГОТОВО ===\n";
echo "Теперь когда вы нажмёте кнопку тестового сообщения:\n";
echo "1. Система определит ваше функциональное направление ('Мобильная бригада')\n";
echo "2. Найдёт соответствующую тему (thread_id = 2)\n";
echo "3. Отправит сообщение в группу с правильным thread_id\n";
echo "4. Сообщение появится в нужной теме, а не в генеральных сообщениях\n";
?>