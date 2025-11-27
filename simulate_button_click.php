<?php
/**
 * Симуляция нажатия кнопки "Управление пользователями"
 * Этот скрипт показывает что должно произойти
 */

echo "=== СИМУЛЯЦИЯ НАЖАТИЯ КНОПКИ 'УПРАВЛЕНИЕ ПОЛЬЗОВАТЕЛЯМИ' ===\n\n";

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Тестовый ID пользователя (замените на реальный ID администратора)
$testUserId = 230379668; // Калимуллин Родион - Суперадминистратор

try {
    echo "Шаг 1: Инициализация сервисов...\n";
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    echo "✅ Сервисы созданы\n\n";
    
    echo "Шаг 2: Проверка уровня доступа пользователя {$testUserId}...\n";
    $userAccessLevel = $adminService->getUserAccessLevel($testUserId);
    $levelName = $adminService->getAccessLevelName($userAccessLevel);
    echo "Уровень доступа: {$levelName} ({$userAccessLevel})\n";
    
    if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
        echo "❌ У пользователя недостаточно прав (требуется уровень 2 или выше)\n";
        echo "Пользователь увидит сообщение об ошибке\n\n";
    } else {
        echo "✅ У пользователя есть права для просмотра списка\n\n";
        
        echo "Шаг 3: Получение списка пользователей с доступом...\n";
        $usersWithAccess = $adminService->getAllUsersWithAccess();
        echo "Найдено пользователей: " . count($usersWithAccess) . "\n\n";
        
        if (empty($usersWithAccess)) {
            echo "⚠️ Список пуст - пользователь увидит сообщение:\n";
            echo "\"Нет пользователей с уровнем доступа выше гостя.\"\n\n";
        } else {
            echo "✅ Список будет показан!\n\n";
            
            echo "Шаг 4: Формирование сообщения и клавиатуры...\n";
            $message = "👥 <b>Пользователи с доступом</b>\n\n";
            $message .= "Выберите пользователя для просмотра деталей:\n\n";
            
            echo "Сообщение:\n";
            echo $message . "\n";
            
            echo "Кнопки (первые 10):\n";
            echo "-------------------\n";
            
            $count = 0;
            foreach ($usersWithAccess as $user) {
                if ($count >= 10) break;
                
                $userId = $user['user_id'];
                $userData = $user['data'] ?? [];
                $fio = $userData['fio'] ?? 'Без ФИО';
                $accessLevel = $userData['access_level'] ?? 0;
                $levelName = $adminService->getAccessLevelName($accessLevel);
                
                $buttonText = "{$fio} ({$levelName})";
                
                // Ограничиваем длину (как в коде)
                if (mb_strlen($buttonText) > 30) {
                    $buttonText = mb_substr($buttonText, 0, 27) . '...';
                }
                
                $callbackData = "acc_lvl_user_details_{$userId}";
                
                echo "[{$buttonText}] -> {$callbackData}\n";
                
                $count++;
            }
            
            echo "-------------------\n";
            echo "И еще " . (count($usersWithAccess) - 10) . " пользователей...\n\n";
            
            echo "✅ ВЫВОД: Кнопка должна показать список из " . count($usersWithAccess) . " пользователей!\n";
        }
    }
    
    echo "\n=== ЧТО ДОЛЖНО ПРОИЗОЙТИ В БОТЕ ===\n\n";
    echo "Когда пользователь нажимает кнопку 'Управление пользователями':\n";
    echo "1. Telegram отправляет callback_data: 'acc_lvl_manage_users'\n";
    echo "2. CallbackqueryCommand получает callback\n";
    echo "3. AccessLevelHandler.canHandle() возвращает TRUE\n";
    echo "4. AccessLevelHandler.handle() вызывается\n";
    echo "5. Проверяется что category === 'acc_lvl_manage_users'\n";
    echo "6. Проверяется уровень доступа пользователя\n";
    echo "7. Вызывается showUsersWithAccess()\n";
    echo "8. Отправляется сообщение со списком пользователей\n\n";
    
    echo "=== ВОЗМОЖНЫЕ ПРИЧИНЫ ПРОБЛЕМЫ ===\n\n";
    echo "Если список НЕ показывается:\n\n";
    
    echo "1. CALLBACK НЕ ДОХОДИТ:\n";
    echo "   - Webhook не настроен\n";
    echo "   - Telegram не может достучаться до сервера\n";
    echo "   - Кнопка имеет неправильный callback_data\n\n";
    
    echo "2. CALLBACK БЛОКИРУЕТСЯ:\n";
    echo "   - CallbackqueryCommand блокирует на early exit filter\n";
    echo "   - (но acc_lvl_ должен проходить!)\n\n";
    
    echo "3. HANDLER НЕ СРАБАТЫВАЕТ:\n";
    echo "   - AccessLevelHandler.canHandle() возвращает FALSE\n";
    echo "   - Category передается неправильно\n\n";
    
    echo "4. ОШИБКА В HANDLE:\n";
    echo "   - Исключение выбрасывается\n";
    echo "   - Код доходит не до той ветки if\n\n";
    
    echo "5. ПРАВА ДОСТУПА:\n";
    echo "   - У пользователя уровень < 2\n";
    echo "   - Показывается сообщение об ошибке доступа\n\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
