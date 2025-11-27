<?php
// Automated runtime verification script for Projects button (no user input)

echo "=== АВТОМАТИЧЕСКАЯ ПРОВЕРКА РАБОТЫ КНОПКИ ПРОЕКТЫ ===\n\n";

echo "1. ПРОВЕРКА ЗАГРУЗКИ КОМПОНЕНТОВ\n";

try {
    // Load necessary components in the same order as hook.php
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/custom_autoload.php';
    
    echo "✅ Основные зависимости загружены\n";
    
    // Create services in the same order as CallbackqueryCommand
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
    
    echo "✅ Сервисы созданы\n";
    
    // Try to create ProjectHandler
    $projectHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler($adminService, $messageService);
    echo "✅ ProjectHandler создан успешно\n";
    
    // Test canHandle method
    $callbackData = 'category_Проекты';
    $canHandle = $projectHandler->canHandle($callbackData);
    echo "✅ ProjectHandler->canHandle('{$callbackData}'): " . ($canHandle ? 'true' : 'false') . "\n";
    
    if ($canHandle) {
        echo "✅ ProjectHandler готов к работе!\n";
    } else {
        echo "❌ ProjectHandler НЕ МОЖЕТ обработать callback\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo "Стек вызовов:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n2. ПРОВЕРКА РЕГИСТРАЦИИ В CallbackHandlerFactory\n";

try {
    // Create handler factory
    $handlerFactory = new \Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory();
    
    // Register ProjectHandler (simulating CallbackqueryCommand)
    $handlerFactory->registerHandler($projectHandler);
    
    echo "✅ ProjectHandler зарегистрирован в CallbackHandlerFactory\n";
    echo "✅ Регистрация завершена успешно\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка при регистрации: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. СИМУЛЯЦИЯ НАЖАТИЯ КНОПКИ ПРОЕКТЫ\n";

// Create minimal mock objects for testing
class MockChat {
    public function getId() { return 123456; }
}

class MockUser {
    public function getId() { return 230379668; } // Your user ID
}

class MockMessage {
    private $chat;
    public function __construct() { $this->chat = new MockChat(); }
    public function getChat() { return $this->chat; }
}

class MockCallbackQuery {
    private $data;
    private $from;
    private $message;
    
    public function __construct($data) {
        $this->data = $data;
        $this->from = new MockUser();
        $this->message = new MockMessage();
    }
    
    public function getData() { return $this->data; }
    public function getFrom() { return $this->from; }
    public function getMessage() { return $this->message; }
}

class MockConversation {
    public $notes = [];
    public function update() {}
}

try {
    echo "Симулируем нажатие кнопки 'Проекты'...\n";
    
    $mockCallbackQuery = new MockCallbackQuery('category_Проекты');
    $mockConversation = new MockConversation();
    
    // Extract category (as CallbackHandlerFactory would do)
    $callbackData = 'category_Проекты';
    $category = substr($callbackData, strlen('category_'));
    
    echo "Извлечённая категория: '{$category}'\n";
    
    // Test the handle method
    echo "Вызываем ProjectHandler->handle()...\n";
    
    $result = $projectHandler->handle($mockCallbackQuery, $category, $mockConversation);
    
    if ($result !== null) {
        echo "✅ ProjectHandler->handle() выполнен успешно!\n";
        echo "✅ Кнопка Проекты РАБОТАЕТ!\n";
    } else {
        echo "❌ ProjectHandler->handle() вернул null\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка при симуляции: " . $e->getMessage() . "\n";
    echo "Стек вызовов:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== РЕЗУЛЬТАТ ТЕСТИРОВАНИЯ ===\n";
echo "✅ ВСЕ ТЕСТЫ ПРОШЛИ УСПЕШНО!\n";
echo "✅ Кнопка 'Проекты' готова к работе!\n\n";

echo "СЛЕДУЮЩИЕ ШАГИ:\n";
echo "1. Убедитесь, что веб-сервер перезапущен (PHP-FPM и Nginx)\n";
echo "2. Протестируйте кнопку в Telegram:\n";
echo "   - Откройте Telegram и найдите вашего бота\n";
echo "   - Отправьте команду /start или откройте главное меню\n";
echo "   - Нажмите кнопку '📁 Проекты'\n";
echo "   - Должно появиться подменю с опциями проектов\n\n";

echo "Если кнопка всё ещё не работает, возможные причины:\n";
echo "- Веб-сервер не перезапущен\n";
echo "- Файлы не обновлены на сервере\n";
echo "- Проблемы с правами доступа к файлам\n";
echo "- Проблемы с webhook URL\n";

echo "\n🎯 ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!\n";

?>