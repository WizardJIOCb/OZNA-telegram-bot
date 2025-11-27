<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Отладка обработки callback-запросов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .debug-section {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .debug-section h2 {
            margin-top: 0;
            color: #007cba;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Отладка обработки callback-запросов</h1>
        
        <?php
        // Debug script for callback handler

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Include necessary files
        require_once __DIR__ . '/vendor/autoload.php';
        require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
        require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
        require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
        require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
        require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';
        require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';

        use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
        use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
        use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
        use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
        use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
        use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;

        echo "Debugging callback handler...\n";

        try {
            // Create service instances
            $databaseService = new DatabaseService();
            $messageService = new MessageService();
            $adminService = new AdminService($messageService);
            
            // Create handlers
            $adminHandler = new AdminHandler($adminService, $messageService);
            $accessLevelHandler = new AccessLevelHandler($adminService, $messageService);
            
            // Test canHandle methods
            echo "\n1. Testing canHandle methods:\n";
            $testCallbackData = 'category_Админка_Заявки';
            
            echo "Testing AdminHandler->canHandle('{$testCallbackData}'):\n";
            $adminCanHandle = $adminHandler->canHandle($testCallbackData);
            echo "Result: " . ($adminCanHandle ? 'true' : 'false') . "\n";
            
            echo "Testing AccessLevelHandler->canHandle('{$testCallbackData}'):\n";
            $accessLevelCanHandle = $accessLevelHandler->canHandle($testCallbackData);
            echo "Result: " . ($accessLevelCanHandle ? 'true' : 'false') . "\n";
            
            // Test CallbackHandlerFactory
            echo "\n2. Testing CallbackHandlerFactory:\n";
            $factory = new CallbackHandlerFactory();
            $factory->registerHandler($adminHandler);
            $factory->registerHandler($accessLevelHandler);
            
            // Use reflection to check registered handlers
            $reflection = new ReflectionClass($factory);
            $property = $reflection->getProperty('handlers');
            $property->setAccessible(true);
            $handlers = $property->getValue($factory);
            
            echo "Registered handlers:\n";
            foreach ($handlers as $index => $handler) {
                echo "  Handler #{$index}: " . get_class($handler) . " (priority: " . $handler->getPriority() . ")\n";
            }
            
            // Test category extraction
            echo "\n3. Testing category extraction:\n";
            $callbackData = 'category_Админка_Заявки';
            
            // Use reflection to call private method
            $extractMethod = $reflection->getMethod('extractCategory');
            $extractMethod->setAccessible(true);
            
            $category = $extractMethod->invoke($factory, $callbackData);
            echo "Extracted category from '{$callbackData}': '{$category}'\n";
            
            echo "\nDebug completed!\n";
            
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
        } catch (Error $e) {
            echo "Error: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
        }
        ?>
        
        <div class="info">
            <h3>Анализ проблемы:</h3>
            <p>Если кнопка "Заявки на доступ" неактивна, возможные причины:</p>
            <ol>
                <li>Проблема с обработкой callback-запросов в CallbackqueryCommand</li>
                <li>Неправильная регистрация обработчиков в CallbackHandlerFactory</li>
                <li>Проблемы с приоритетами обработчиков</li>
                <li>Ошибки в методе canHandle класса AdminHandler</li>
                <li>Проблемы с загрузкой данных из файла access_requests.json</li>
            </ol>
        </div>
    </div>
</body>
</html>