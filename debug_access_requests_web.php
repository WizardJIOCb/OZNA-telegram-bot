<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Отладка системы заявок на доступ</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Отладка системы заявок на доступ</h1>
        
        <?php
        try {
            // Подключаем необходимые файлы
            require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
            require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
            require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

            use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
            use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
            use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

            echo '<div class="success">Система инициализирована успешно</div>';

            // Создаем экземпляры сервисов
            $messageService = new MessageService();
            $adminService = new AdminService($messageService);

            // Проверяем загрузку заявок
            echo '<div class="debug-section">';
            echo '<h2>Проверка загрузки заявок</h2>';
            $accessRequests = $adminService->getAllAccessRequests();
            echo '<p>Количество заявок: ' . count($accessRequests) . '</p>';

            if (!empty($accessRequests)) {
                echo '<p>Содержимое заявок:</p>';
                echo '<pre>';
                foreach ($accessRequests as $index => $request) {
                    echo "Заявка #" . ($index + 1) . ":\n";
                    echo "  User ID: " . ($request['user_id'] ?? 'N/A') . "\n";
                    echo "  User Name: " . ($request['user_name'] ?? 'N/A') . "\n";
                    echo "  FIO: " . ($request['user_fio'] ?? 'N/A') . "\n";
                    echo "  Telegram Name: " . ($request['user_telegram_name'] ?? 'N/A') . "\n";
                    echo "  Chat ID: " . ($request['chat_id'] ?? 'N/A') . "\n";
                    echo "  Timestamp: " . ($request['timestamp'] ?? 'N/A') . "\n\n";
                }
                echo '</pre>';
            } else {
                echo '<p class="error">Заявок нет.</p>';
            }
            echo '</div>';

            // Проверяем содержимое файла
            echo '<div class="debug-section">';
            echo '<h2>Проверка содержимого файла access_requests.json</h2>';
            $filePath = __DIR__ . '/data/access_requests.json';
            if (file_exists($filePath)) {
                $data = file_get_contents($filePath);
                echo '<p>Содержимое файла:</p>';
                echo '<pre>' . htmlspecialchars($data) . '</pre>';
                
                $requests = json_decode($data, true);
                echo '<p>Результат декодирования JSON:</p>';
                echo '<pre>' . htmlspecialchars(print_r($requests, true)) . '</pre>';
            } else {
                echo '<p class="error">Файл не существует.</p>';
            }
            echo '</div>';

            // Проверяем работу AdminHandler
            echo '<div class="debug-section">';
            echo '<h2>Проверка работы AdminHandler</h2>';
            echo '<p>Создаем тестовый AdminHandler...</p>';
            
            // Подключаем AdminHandler
            require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
            use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
            
            $adminHandler = new AdminHandler($adminService, $messageService);
            
            // Проверяем метод canHandle
            $testCallbackData = 'category_Админка_Заявки';
            $canHandle = $adminHandler->canHandle($testCallbackData);
            echo '<p>Метод canHandle для "' . $testCallbackData . '": ' . ($canHandle ? 'true' : 'false') . '</p>';
            
            echo '</div>';

        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h2>Ошибка!</h2>';
            echo '<p>Произошла ошибка: ' . $e->getMessage() . '</p>';
            echo '<p>Файл: ' . $e->getFile() . '</p>';
            echo '<p>Строка: ' . $e->getLine() . '</p>';
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 30px; padding: 15px; background-color: #e9ecef; border-radius: 4px;">
            <h3>Инструкция:</h3>
            <p>1. Эта страница показывает результаты отладки системы заявок на доступ</p>
            <p>2. Если все работает правильно, вы должны видеть содержимое файла access_requests.json</p>
            <p>3. Если кнопка "Заявки на доступ" все еще неактивна, возможно, проблема в другом месте</p>
        </div>
    </div>
</body>
</html>