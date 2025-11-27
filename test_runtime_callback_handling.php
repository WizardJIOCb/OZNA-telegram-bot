<?php
// Скрипт для создания тестового callback'а и проверки его обработки
echo "=== ТЕСТ RUNTIME ОБРАБОТКИ CALLBACK'А ===\n\n";

// Включаем автозагрузку, если есть
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

try {
    echo "1. Проверка включения файлов...\n";
    
    // Включаем нужные файлы вручную
    $files = [
        __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php',
        __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php', 
        __DIR__ . '/CustomCommands/Services/DatabaseService.php',
        __DIR__ . '/CustomCommands/Services/MessageService.php',
        __DIR__ . '/CustomCommands/Services/AdminService.php',
        __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php'
    ];
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            require_once $file;
            echo "   ✅ Включен: " . basename($file) . "\n";
        } else {
            echo "   ❌ НЕ НАЙДЕН: {$file}\n";
            continue;
        }
    }
    
    echo "\n2. Проверка создания объектов...\n";
    
    // Create mock services
    class MockMessageService {
        public function sendMessage($chatId, $message, $keyboard = null) {
            echo "   📤 Mock отправка сообщения в чат {$chatId}: " . substr($message, 0, 50) . "...\n";
            return (object)['ok' => true];
        }
    }
    
    class MockAdminService {
        public function getUserAccessLevel($userId) {
            echo "   🔍 Mock проверка уровня доступа для пользователя {$userId}\n";
            return 3; // Суперадмин для теста
        }
        
        public function getAllUsersWithAccess() {
            echo "   👥 Mock получение пользователей с доступом\n";
            return [
                ['user_id' => 123, 'data' => ['fio' => 'Тестовый Пользователь', 'access_level' => 2]],
                ['user_id' => 456, 'data' => ['fio' => 'Другой Пользователь', 'access_level' => 1]]
            ];
        }
        
        public function getAccessLevelName($level) {
            $names = [0 => 'Гость', 1 => 'Пользователь', 2 => 'Администратор', 3 => 'Суперадминистратор'];
            return $names[$level] ?? 'Неизвестный';
        }
    }
    
    // Create mock callback query
    class MockCallbackQuery {
        public function getData() { return 'acc_lvl_manage_users'; }
        public function getFrom() { 
            return (object)[
                'getId' => function() { return 12345; },
                'getFirstName' => function() { return 'Тест'; }
            ];
        }
        public function getMessage() {
            return (object)[
                'getChat' => function() {
                    return (object)['getId' => function() { return 67890; }];
                }
            ];
        }
    }
    
    $messageService = new MockMessageService();
    $adminService = new MockAdminService();
    
    echo "   ✅ Mock сервисы созданы\n";
    
    echo "\n3. Тестирование AccessLevelHandler...\n";
    
    // Test AccessLevelHandler if class exists
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler')) {
        $handler = new Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler($adminService, $messageService);
        echo "   ✅ AccessLevelHandler создан\n";
        
        // Test canHandle
        $canHandle = $handler->canHandle('acc_lvl_manage_users');
        echo "   🧪 canHandle('acc_lvl_manage_users'): " . ($canHandle ? 'TRUE' : 'FALSE') . "\n";
        
        if ($canHandle) {
            echo "   ✅ Handler может обработать callback\n";
            
            // Test handle method with mock objects
            try {
                $mockCallback = new MockCallbackQuery();
                $mockConversation = (object)['notes' => []];
                
                echo "   🔄 Попытка обработки callback...\n";
                $result = $handler->handle($mockCallback, 'acc_lvl_manage_users', $mockConversation);
                echo "   ✅ Обработка завершена успешно\n";
                echo "   📊 Результат: " . (is_object($result) ? 'ServerResponse object' : gettype($result)) . "\n";
                
            } catch (Exception $e) {
                echo "   ❌ Ошибка при обработке: " . $e->getMessage() . "\n";
                echo "   📍 Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
            }
        } else {
            echo "   ❌ Handler НЕ может обработать callback\n";
        }
        
    } else {
        echo "   ❌ Класс AccessLevelHandler не найден\n";
    }
    
    echo "\n4. Тестирование CallbackHandlerFactory...\n";
    
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory')) {
        $factory = new Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory();
        echo "   ✅ CallbackHandlerFactory создан\n";
        
        // Register handler
        if (isset($handler)) {
            $factory->registerHandler($handler);
            echo "   ✅ AccessLevelHandler зарегистрирован в фабрике\n";
            
            // Test callback handling
            try {
                $mockCallback = new MockCallbackQuery();
                $mockConversation = (object)['notes' => []];
                
                echo "   🔄 Тестирование handleCallback...\n";
                $result = $factory->handleCallback($mockCallback, 'acc_lvl_manage_users', $mockConversation);
                echo "   ✅ handleCallback завершен успешно\n";
                
            } catch (Exception $e) {
                echo "   ❌ Ошибка в handleCallback: " . $e->getMessage() . "\n";
                echo "   📍 Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
            }
        }
        
    } else {
        echo "   ❌ Класс CallbackHandlerFactory не найден\n";
    }
    
    echo "\n=== РЕЗУЛЬТАТ ТЕСТИРОВАНИЯ ===\n";
    echo "Если все тесты прошли успешно, то код работает правильно.\n";
    echo "Проблема может быть в:\n";
    echo "1. Callback не доходит до сервера (проверьте webhook)\n";
    echo "2. Недостаточные права доступа у пользователя\n";
    echo "3. Проблемы с базой данных\n";
    echo "\nДля дальнейшей диагностики используйте debug_user_management_button.php\n";
    
} catch (Exception $e) {
    echo "\n❌ КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "📍 Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nПроблема в настройке среды или включении файлов.\n";
}
?>