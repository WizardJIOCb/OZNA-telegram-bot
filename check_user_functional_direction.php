<?php
/**
 * Проверим функциональное направление пользователя для определения правильной темы
 */

echo "=== Проверка функционального направления пользователя ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    // Ваш ID пользователя
    $userId = 230379668;
    
    echo "1. Получение данных пользователя ID: $userId\n";
    $userData = $databaseService->getUserData($userId);
    
    if (isset($userData['functional_direction'])) {
        $functionalDirection = $userData['functional_direction'];
        echo "   ✓ Функциональное направление: $functionalDirection\n";
        
        // Проверим, какую группу и тему должен использовать
        echo "\n2. Определение группы и темы для направления '$functionalDirection':\n";
        
        require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
        $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
        
        // Используем рефлексию для доступа к приватному методу
        $reflection = new ReflectionClass($messageService);
        $method = $reflection->getMethod('getGroupAndThreadByFunctionalDirection');
        $method->setAccessible(true);
        
        $groupAndThread = $method->invoke($messageService, $functionalDirection);
        
        echo "   • Group ID: {$groupAndThread['group_id']}\n";
        echo "   • Thread ID: {$groupAndThread['thread_id']}\n";
        
        // Покажем какие константы используются
        echo "\n3. Константы для функциональных направлений:\n";
        $constants = $reflection->getConstants();
        
        echo "   Мобильная бригада:\n";
        echo "     - Group: {$constants['MOBILE_BRIGADE_GROUP_ID']}\n";
        echo "     - Thread: {$constants['MOBILE_BRIGADE_THREAD_ID']}\n";
        
        echo "   Пусконаладка:\n";
        echo "     - Group: {$constants['COMMISSIONING_GROUP_ID']}\n";
        echo "     - Thread: {$constants['COMMISSIONING_THREAD_ID']}\n";
        
        echo "   Шеф-монтаж:\n";
        echo "     - Group: {$constants['SUPERVISION_GROUP_ID']}\n";
        echo "     - Thread: {$constants['SUPERVISION_THREAD_ID']}\n";
        
        echo "\n4. Рекомендация:\n";
        echo "   Тестовое уведомление должно отправляться в группу {$groupAndThread['group_id']}\n";
        echo "   с указанием thread_id = {$groupAndThread['thread_id']}\n";
        echo "   для направления '{$functionalDirection}'\n";
        
    } else {
        echo "   ⚠️ Функциональное направление не указано\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== Анализ завершён ===\n";
?>