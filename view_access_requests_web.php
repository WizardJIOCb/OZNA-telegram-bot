<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Просмотр заявок на доступ</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .no-requests {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        .actions {
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #007cba;
            color: white;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Заявки на доступ к админке</h1>
        
        <?php
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

        // Получаем все заявки
        $accessRequests = $adminService->getAllAccessRequests();
        
        if (empty($accessRequests)) {
            echo '<div class="no-requests">';
            echo '<h3>Нет заявок на доступ</h3>';
            echo '<p>В настоящее время нет заявок на доступ к админке.</p>';
            echo '</div>';
        } else {
            echo '<p><strong>Всего заявок:</strong> ' . count($accessRequests) . '</p>';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>User ID</th>';
            echo '<th>Имя пользователя</th>';
            echo '<th>ФИО</th>';
            echo '<th>Имя в Telegram</th>';
            echo '<th>Chat ID</th>';
            echo '<th>Дата подачи</th>';
            echo '<th>Действия</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($accessRequests as $request) {
                $userId = $request['user_id'] ?? '';
                $userName = $request['user_name'] ?? '';
                $userFio = $request['user_fio'] ?? '';
                $userTelegramName = $request['user_telegram_name'] ?? '';
                $chatId = $request['chat_id'] ?? '';
                $timestamp = isset($request['timestamp']) ? date('d.m.Y H:i:s', $request['timestamp']) : '';
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars($userId) . '</td>';
                echo '<td>' . htmlspecialchars($userName) . '</td>';
                echo '<td>' . htmlspecialchars($userFio) . '</td>';
                echo '<td>' . htmlspecialchars($userTelegramName) . '</td>';
                echo '<td>' . htmlspecialchars($chatId) . '</td>';
                echo '<td>' . htmlspecialchars($timestamp) . '</td>';
                echo '<td class="actions">';
                echo '<a href="#" class="btn btn-success">Одобрить</a>';
                echo '<a href="#" class="btn btn-danger">Отклонить</a>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        }
        ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="add_test_access_request_web.php" class="btn btn-primary">Добавить тестовую заявку</a>
            <a href="#" class="btn btn-danger" onclick="clearAllRequests()">Очистить все заявки</a>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background-color: #e9ecef; border-radius: 4px;">
            <h3>Инструкция:</h3>
            <p>1. На этой странице отображаются все заявки на доступ к админке</p>
            <p>2. Если заявок нет, это нормально - кнопка "Заявки на доступ" в Telegram боте должна отображать сообщение "Нет новых заявок на доступ"</p>
            <p>3. Чтобы протестировать работу кнопки, добавьте тестовую заявку с помощью кнопки выше</p>
        </div>
        
        <script>
        function clearAllRequests() {
            if (confirm('Вы уверены, что хотите очистить все заявки?')) {
                // Здесь можно добавить код для очистки заявок
                alert('Функция очистки заявок будет реализована позже');
            }
        }
        </script>
    </div>
</body>
</html>