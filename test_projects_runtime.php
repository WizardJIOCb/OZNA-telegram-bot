<?php

/**
 * Полный тест работы кнопки "Проекты" во время выполнения
 * Проверяем реальную работу системы при нажатии кнопки
 */

echo "=== ТЕСТ РАБОТЫ КНОПКИ ПРОЕКТЫ ВО ВРЕМЯ ВЫПОЛНЕНИЯ ===\n\n";

// Подключаем необходимые файлы в правильном порядке
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;

try {
    echo "1. Инициализация сервисов...\n";
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    $handlerFactory = new CallbackHandlerFactory();
    
    echo "✓ Сервисы инициализированы\n\n";
    
    echo "2. Регистрация ProjectHandler...\n";
    $projectHandler = new ProjectHandler($adminService, $messageService);
    $handlerFactory->registerHandler($projectHandler);
    echo "✓ ProjectHandler зарегистрирован\n\n";
    
    echo "3. Тестируем метод canHandle...\n";
    $testCallbacks = [
        'category_Проекты',
        'category_Проекты_МоиПроекты',
        'category_Проекты_ПроектыДляОтчёта',
        'proj_list_add',
        'proj_list_remove',
        'category_Табель', // не должен обрабатываться
        'adm_req_clear' // не должен обрабатываться
    ];
    
    foreach ($testCallbacks as $callback) {
        $canHandle = $projectHandler->canHandle($callback);
        $icon = $canHandle ? '✓' : '✗';
        $expected = (strpos($callback, 'Проекты') !== false || strpos($callback, 'proj_') === 0);
        $correct = ($canHandle === $expected) ? '✓' : '✗';
        echo "  {$icon} '{$callback}' -> canHandle: " . ($canHandle ? 'да' : 'нет') . " {$correct}\n";
    }
    
    echo "\n4. Тестируем создание главного меню для разных уровней пользователей...\n";
    
    $testUsers = [
        ['id' => 111111111, 'level' => 0, 'type' => 'guest'],
        ['id' => 222222222, 'level' => 1, 'type' => 'user'],
        ['id' => 333333333, 'level' => 2, 'type' => 'supervisor'],
        ['id' => 444444444, 'level' => 3, 'type' => 'admin']
    ];
    
    foreach ($testUsers as $user) {
        echo "  Пользователь {$user['type']} (уровень {$user['level']}):\n";
        
        try {
            $keyboard = $messageService->createMainMenuKeyboard($user['id']);
            $hasProjectsButton = false;
            
            foreach ($keyboard as $row) {
                foreach ($row as $button) {
                    if (isset($button['text']) && strpos($button['text'], 'Проекты') !== false) {
                        $hasProjectsButton = true;
                        echo "    ✓ Кнопка 'Проекты' найдена: '{$button['text']}' -> '{$button['callback_data']}'\n";
                        break 2;
                    }
                }
            }
            
            if (!$hasProjectsButton) {
                if ($user['level'] == 0) {
                    echo "    ✓ Кнопка 'Проекты' отсутствует (правильно для гостя)\n";
                } else {
                    echo "    ✗ Кнопка 'Проекты' отсутствует (ОШИБКА для уровня {$user['level']})\n";
                }
            }
            
        } catch (Exception $e) {
            echo "    ✗ Ошибка создания меню: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n5. Симуляция нажатия кнопки 'Проекты'...\n";
    
    // Создаём мок объекты для симуляции callback query
    class MockMessage {
        public function getChat() { return new MockChat(); }
    }
    
    class MockChat {
        public function getId() { return '-1001234567890'; }
    }
    
    class MockUser {
        public function getId() { return 222222222; } // обычный пользователь
    }
    
    class MockCallbackQuery {
        public function getMessage() { return new MockMessage(); }
        public function getFrom() { return new MockUser(); }
        public function getData() { return 'category_Проекты'; }
    }
    
    class MockConversation {
        public $notes = [];
        public function update() {}
    }
    
    try {
        $mockQuery = new MockCallbackQuery();
        $mockConversation = new MockConversation();
        
        echo "  Тестируем callback 'category_Проекты'...\n";
        
        // Проверяем, может ли обработчик это обработать
        $canHandle = $projectHandler->canHandle('category_Проекты');
        if ($canHandle) {
            echo "  ✓ ProjectHandler может обработать callback\n";
            
            // Пытаемся обработать
            $result = $projectHandler->handle($mockQuery, 'Проекты', $mockConversation);
            
            if ($result && isset($result['ok']) && $result['ok']) {
                echo "  ✓ Callback успешно обработан\n";
                echo "  ✓ Отправлено сообщение с меню проектов\n";
            } else {
                echo "  ✗ Ошибка обработки callback\n";
                if (isset($result['description'])) {
                    echo "  Описание ошибки: {$result['description']}\n";
                }
            }
        } else {
            echo "  ✗ ProjectHandler НЕ может обработать callback\n";
        }
        
    } catch (Exception $e) {
        echo "  ✗ Исключение при симуляции: " . $e->getMessage() . "\n";
        echo "  Трассировка: " . $e->getTraceAsString() . "\n";
    }
    
    echo "\n6. Проверка логов ошибок...\n";
    $errorLogFile = ini_get('error_log');
    if ($errorLogFile && file_exists($errorLogFile)) {
        echo "  Путь к логу ошибок: {$errorLogFile}\n";
        echo "  Последние записи (если есть проблемы с Проектами):\n";
        
        $logLines = file($errorLogFile);
        $recentLines = array_slice($logLines, -20); // последние 20 строк
        
        $projectRelatedLogs = array_filter($recentLines, function($line) {
            return strpos($line, 'Project') !== false || strpos($line, 'Проект') !== false;
        });
        
        if (empty($projectRelatedLogs)) {
            echo "  ✓ Нет записей об ошибках, связанных с проектами\n";
        } else {
            foreach ($projectRelatedLogs as $line) {
                echo "  ! " . trim($line) . "\n";
            }
        }
    } else {
        echo "  ! Лог ошибок не найден или недоступен\n";
    }
    
} catch (Exception $e) {
    echo "✗ КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Трассировка:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== ИТОГИ ТЕСТИРОВАНИЯ ===\n";
echo "Если все тесты прошли успешно, но кнопка всё ещё не работает,\n";
echo "проблема может быть в:\n";
echo "- Настройках Telegram Bot API\n";
echo "- Сетевых соединениях\n";
echo "- Базе данных\n";
echo "- Уровне доступа конкретного пользователя\n";
echo "\nДля дальнейшей диагностики нужны логи реальных запросов от Telegram.\n";