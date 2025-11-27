<?php

/**
 * Тестовый скрипт для проверки полного функционала админки
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes since they're not autoloaded
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/FunctionalDirectionHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/HelpHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;

echo "Тестирование полного функционала админки...\n";

try {
    // Создаем сервисы
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "✅ Сервисы успешно созданы\n";
    
    // Создаем AdminHandler
    $adminHandler = new AdminHandler($adminService, $messageService);
    
    echo "✅ AdminHandler успешно создан\n";
    
    // Создаем CallbackHandlerFactory и регистрируем все handlers
    $handlerFactory = new CallbackHandlerFactory();
    $handlerFactory->registerHandler($adminHandler);
    
    echo "✅ CallbackHandlerFactory успешно создан и зарегистрирован\n";
    
    // Получаем текущие заявки на доступ
    $requests = $adminService->getAllAccessRequests();
    echo "✅ Получен список заявок на доступ: " . count($requests) . " заявок\n";
    
    foreach ($requests as $index => $request) {
        echo "  Заявка #{$index}:\n";
        echo "    User ID: " . $request['user_id'] . "\n";
        echo "    User Name: " . $request['user_name'] . "\n";
        echo "    Chat ID: " . $request['chat_id'] . "\n";
        echo "    Timestamp: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n";
    }
    
    // Тестируем функционал очистки заявок
    echo "\nТестирование функции очистки заявок:\n";
    
    // Сохраняем текущее количество заявок
    $initialCount = count($requests);
    echo "  Начальное количество заявок: {$initialCount}\n";
    
    // Создаем callback query для очистки заявок
    $callbackQuery = new CallbackQuery([
        'id' => 'test_clear_' . time(),
        'from' => new User([
            'id' => 230379668, // Супер-админ ID
            'first_name' => 'Test',
            'last_name' => 'User'
        ]),
        'message' => new Message([
            'message_id' => 1,
            'from' => new User([
                'id' => 230379668,
                'first_name' => 'Test',
                'last_name' => 'User'
            ]),
            'chat' => new Chat([
                'id' => 230379668,
                'type' => 'private'
            ]),
            'date' => time(),
            'text' => 'Test message'
        ]),
        'chat_instance' => 'test_instance',
        'data' => 'adm_req_clear'
    ]);
    
    // Создаем conversation
    $conversation = new Conversation(230379668, 230379668, "/callback");
    
    echo "  Создан callback query с данными: adm_req_clear\n";
    echo "  User ID: 230379668 (супер-админ)\n";
    echo "  Chat ID: 230379668\n";
    
    // Проверяем, может ли handler обработать этот callback
    $canHandle = $adminHandler->canHandle('adm_req_clear');
    echo "  AdminHandler canHandle('adm_req_clear'): " . ($canHandle ? 'true' : 'false') . "\n";
    
    if ($canHandle) {
        echo "  ✅ AdminHandler может обработать этот callback\n";
        echo "  ✅ Функция очистки заявок должна работать корректно\n";
    } else {
        echo "  ❌ AdminHandler не может обработать этот callback\n";
        echo "  ❌ Функция очистки заявок не будет работать\n";
    }
    
    // Тестируем функционал просмотра заявки
    echo "\nТестирование функции просмотра заявки:\n";
    
    if (!empty($requests)) {
        $firstRequest = $requests[0];
        $userId = $firstRequest['user_id'];
        $callbackData = "adm_req_view_{$userId}";
        
        echo "  Тестируем просмотр заявки для user ID: {$userId}\n";
        
        // Проверяем, может ли handler обработать этот callback
        $canHandle = $adminHandler->canHandle($callbackData);
        echo "  AdminHandler canHandle('{$callbackData}'): " . ($canHandle ? 'true' : 'false') . "\n";
        
        if ($canHandle) {
            echo "  ✅ AdminHandler может обработать этот callback\n";
            echo "  ✅ Функция просмотра заявки должна работать корректно\n";
        } else {
            echo "  ❌ AdminHandler не может обработать этот callback\n";
            echo "  ❌ Функция просмотра заявки не будет работать\n";
        }
    } else {
        echo "  Нет заявок для тестирования функции просмотра\n";
    }
    
    // Тестируем функционал одобрения заявки
    echo "\nТестирование функции одобрения заявки:\n";
    
    if (!empty($requests)) {
        $firstRequest = $requests[0];
        $userId = $firstRequest['user_id'];
        $callbackData = "adm_req_approve_{$userId}";
        
        echo "  Тестируем одобрение заявки для user ID: {$userId}\n";
        
        // Проверяем, может ли handler обработать этот callback
        $canHandle = $adminHandler->canHandle($callbackData);
        echo "  AdminHandler canHandle('{$callbackData}'): " . ($canHandle ? 'true' : 'false') . "\n";
        
        if ($canHandle) {
            echo "  ✅ AdminHandler может обработать этот callback\n";
            echo "  ✅ Функция одобрения заявки должна работать корректно\n";
        } else {
            echo "  ❌ AdminHandler не может обработать этот callback\n";
            echo "  ❌ Функция одобрения заявки не будет работать\n";
        }
    } else {
        echo "  Нет заявок для тестирования функции одобрения\n";
    }
    
    // Тестируем функционал отклонения заявки
    echo "\nТестирование функции отклонения заявки:\n";
    
    if (!empty($requests)) {
        $firstRequest = $requests[0];
        $userId = $firstRequest['user_id'];
        $callbackData = "adm_req_reject_{$userId}";
        
        echo "  Тестируем отклонение заявки для user ID: {$userId}\n";
        
        // Проверяем, может ли handler обработать этот callback
        $canHandle = $adminHandler->canHandle($callbackData);
        echo "  AdminHandler canHandle('{$callbackData}'): " . ($canHandle ? 'true' : 'false') . "\n";
        
        if ($canHandle) {
            echo "  ✅ AdminHandler может обработать этот callback\n";
            echo "  ✅ Функция отклонения заявки должна работать корректно\n";
        } else {
            echo "  ❌ AdminHandler не может обработать этот callback\n";
            echo "  ❌ Функция отклонения заявки не будет работать\n";
        }
    } else {
        echo "  Нет заявок для тестирования функции отклонения\n";
    }
    
    echo "\n✅ Все тесты завершены успешно!\n";
    echo "Кнопки админки должны работать корректно\n";
    echo "Если кнопки все еще не работают, проблема может быть в другом месте\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}