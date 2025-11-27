<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Тестирование методов canHandle</title>
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
        .test-result {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .test-result h3 {
            margin-top: 0;
            color: #007cba;
        }
        .can-handle {
            color: green;
            font-weight: bold;
        }
        .cannot-handle {
            color: red;
            font-weight: bold;
        }
        .priority {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Тестирование методов canHandle</h1>
        
        <?php
        try {
            // Подключаем необходимые файлы
            require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
            require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
            require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
            require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
            require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';

            use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
            use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
            use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
            use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
            use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;

            echo '<div class="test-result">';
            echo '<h2>Результаты тестирования</h2>';

            // Создаем экземпляры сервисов
            $messageService = new MessageService();
            $adminService = new AdminService($messageService);

            // Создаем экземпляры обработчиков
            $adminHandler = new AdminHandler($adminService, $messageService);
            $accessLevelHandler = new AccessLevelHandler($adminService, $messageService);

            // Тестовые callback-данные
            $testCallbacks = [
                'category_Админка_Заявки',
                'category_Админка',
                'category_Админка_Проекты',
                'category_УровеньДоступа_Управление',
                'category_УровеньДоступа_Просмотр',
                'category_Табель',
                'category_Профиль'
            ];

            foreach ($testCallbacks as $callbackData) {
                echo '<div class="test-result">';
                echo '<h3>Тест для: ' . htmlspecialchars($callbackData) . '</h3>';
                echo '<p>AdminHandler canHandle: <span class="' . ($adminHandler->canHandle($callbackData) ? 'can-handle">true' : 'cannot-handle">false') . '</span></p>';
                echo '<p>AccessLevelHandler canHandle: <span class="' . ($accessLevelHandler->canHandle($callbackData) ? 'can-handle">true' : 'cannot-handle">false') . '</span></p>';
                echo '</div>';
            }

            echo '<div class="priority">';
            echo '<h3>Приоритеты обработчиков:</h3>';
            echo '<p>AdminHandler: ' . $adminHandler->getPriority() . '</p>';
            echo '<p>AccessLevelHandler: ' . $accessLevelHandler->getPriority() . '</p>';
            echo '</div>';

            echo '</div>';

        } catch (Exception $e) {
            echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">';
            echo '<h2>Ошибка!</h2>';
            echo '<p>Произошла ошибка: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>Файл: ' . htmlspecialchars($e->getFile()) . '</p>';
            echo '<p>Строка: ' . htmlspecialchars($e->getLine()) . '</p>';
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 30px; padding: 15px; background-color: #d1ecf1; border-radius: 4px;">
            <h3>Анализ результатов:</h3>
            <p>Если AdminHandler правильно обрабатывает "category_Админка_Заявки", то кнопка должна работать.</p>
            <p>Если AccessLevelHandler имеет более высокий приоритет, но не обрабатывает этот callback, то проблема может быть в другом месте.</p>
        </div>
    </div>
</body>
</html>