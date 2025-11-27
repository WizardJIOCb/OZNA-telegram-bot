<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Добавление тестовой заявки</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007cba;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #005a87;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Добавление тестовой заявки на доступ</h1>
        
        <?php
        if ($_POST) {
            // Обработка формы
            try {
                // Подключаем необходимые файлы
                require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
                require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
                require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

                use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
                use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
                use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

                // Создаем экземпляры сервисов
                $databaseService = new DatabaseService();
                $messageService = new MessageService();
                $adminService = new AdminService($messageService);

                // Получаем данные из формы
                $user_id = (int)$_POST['user_id'];
                $user_name = $_POST['user_name'];
                $user_telegram_name = $_POST['user_telegram_name'];
                $user_fio = $_POST['user_fio'];
                $chat_id = $_POST['chat_id'];

                // Создаем тестовую заявку
                $testRequest = [
                    'user_id' => $user_id,
                    'user_name' => $user_name,
                    'user_telegram_name' => $user_telegram_name,
                    'user_fio' => $user_fio,
                    'chat_id' => $chat_id,
                    'timestamp' => time()
                ];

                // Получаем текущие заявки
                $accessRequests = $adminService->getAllAccessRequests();

                // Добавляем тестовую заявку
                $accessRequests[] = $testRequest;

                // Сохраняем обновленный список заявок в файл
                $filePath = __DIR__ . '/data/access_requests.json';
                $jsonData = json_encode($accessRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                file_put_contents($filePath, $jsonData);

                echo '<div class="result success">';
                echo '<h3>Успех!</h3>';
                echo '<p>Тестовая заявка успешно добавлена!</p>';
                echo '<p>Теперь проверьте работу кнопки "Заявки на доступ" в админке.</p>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="result error">';
                echo '<h3>Ошибка!</h3>';
                echo '<p>Произошла ошибка при добавлении заявки: ' . $e->getMessage() . '</p>';
                echo '</div>';
            }
        } else {
            // Отображаем форму
        ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="user_id">User ID:</label>
                <input type="number" id="user_id" name="user_id" value="123456789" required>
            </div>
            
            <div class="form-group">
                <label for="user_name">Имя пользователя:</label>
                <input type="text" id="user_name" name="user_name" value="Тестовый пользователь" required>
            </div>
            
            <div class="form-group">
                <label for="user_telegram_name">Имя в Telegram:</label>
                <input type="text" id="user_telegram_name" name="user_telegram_name" value="Test User" required>
            </div>
            
            <div class="form-group">
                <label for="user_fio">ФИО:</label>
                <input type="text" id="user_fio" name="user_fio" value="Иванов Иван Иванович" required>
            </div>
            
            <div class="form-group">
                <label for="chat_id">Chat ID:</label>
                <input type="text" id="chat_id" name="chat_id" value="987654321" required>
            </div>
            
            <button type="submit">Добавить тестовую заявку</button>
        </form>
        
        <?php
        }
        ?>
        
        <div style="margin-top: 30px; padding: 15px; background-color: #e9ecef; border-radius: 4px;">
            <h3>Инструкция:</h3>
            <p>1. Заполните форму выше и нажмите кнопку "Добавить тестовую заявку"</p>
            <p>2. После добавления заявки проверьте работу кнопки "Заявки на доступ" в админке Telegram бота</p>
            <p>3. Вы должны увидеть список заявок с одной тестовой заявкой</p>
        </div>
    </div>
</body>
</html>