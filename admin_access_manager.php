<?php
if ($_REQUEST["сусурити"] != "жестьсесурити"){
    echo('низя');
    return false;
}
require_once __DIR__ . '/Custome/DB.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Инициализация сервисов
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Обработка действий
$action = $_POST['action'] ?? '';
$resultMessage = '';

if ($action === 'approve_request') {
    $userId = (int)$_POST['user_id'];
    $userName = $_POST['user_name'];
    $chatId = $_POST['chat_id'];
    
    // Одобряем заявку
    $result = $adminService->approveAccessRequest($userId, $userName, $chatId);
    
    if ($result) {
        $resultMessage = "✅ Заявка пользователя $userName (ID: $userId) успешно одобрена!";
    } else {
        $resultMessage = "❌ Ошибка при одобрении заявки пользователя $userName (ID: $userId)";
    }
} elseif ($action === 'add_admin') {
    $userId = (int)$_POST['user_id'];
    $userName = $_POST['user_name'];
    $chatId = $_POST['chat_id'];
    
    // Обновляем уровень доступа до администратора
    $result = $adminService->updateUserAccessLevel($userId, DatabaseService::ACCESS_LEVEL_ADMIN);
    
    if ($result) {
        // Отправляем уведомление пользователю
        $message = "✅ <b>Доступ предоставлен</b>\n\n";
        $message .= "Вам предоставлен доступ к административным функциям бота.\n";
        $message .= "Теперь вы можете использовать все функции админки.";
        
        try {
            $messageService->sendMessage($chatId, $message);
            $resultMessage = "✅ Пользователь $userName (ID: $userId) успешно добавлен как администратор! Уведомление отправлено.";
        } catch (Exception $e) {
            $resultMessage = "✅ Пользователь $userName (ID: $userId) успешно добавлен как администратор! Но не удалось отправить уведомление: " . $e->getMessage();
        }
    } else {
        $resultMessage = "❌ Ошибка при добавлении администратора $userName (ID: $userId)";
    }
} elseif ($action === 'add_supervisor') {
    $userId = (int)$_POST['user_id'];
    $userName = $_POST['user_name'];
    $chatId = $_POST['chat_id'];
    
    // Обновляем уровень доступа до супервайзера
    $result = $adminService->updateUserAccessLevel($userId, DatabaseService::ACCESS_LEVEL_SUPERVISOR);
    
    if ($result) {
        // Отправляем уведомление пользователю
        $message = "✅ <b>Доступ предоставлен</b>\n\n";
        $message .= "Вам предоставлен доступ к функциям супервайзера.\n";
        $message .= "Теперь вы можете просматривать отчеты и использовать другие функции.";
        
        try {
            $messageService->sendMessage($chatId, $message);
            $resultMessage = "✅ Пользователь $userName (ID: $userId) успешно добавлен как супервайзер! Уведомление отправлено.";
        } catch (Exception $e) {
            $resultMessage = "✅ Пользователь $userName (ID: $userId) успешно добавлен как супервайзер! Но не удалось отправить уведомление: " . $e->getMessage();
        }
    } else {
        $resultMessage = "❌ Ошибка при добавлении супервайзера $userName (ID: $userId)";
    }
} elseif ($action === 'reset_to_guest') {
    $userId = (int)$_POST['user_id'];
    $userName = $_POST['user_name'];
    
    // Сбрасываем уровень доступа до гостя
    $result = $adminService->updateUserAccessLevel($userId, DatabaseService::ACCESS_LEVEL_GUEST);
    
    if ($result) {
        $resultMessage = "✅ Уровень доступа пользователя $userName (ID: $userId) успешно сброшен до Гостя!";
    } else {
        $resultMessage = "❌ Ошибка при сбросе уровня доступа пользователя $userName (ID: $userId)";
    }
} elseif ($action === 'check_access') {
    $userId = (int)$_POST['user_id'];
    $userName = $_POST['user_name'];
    
    // Проверяем уровень доступа
    $accessLevel = $adminService->getUserAccessLevel($userId);
    $levelName = $adminService->getAccessLevelName($accessLevel);
    $isSuperAdmin = $adminService->isSuperAdmin($userId) ? "Да" : "Нет";
    
    $resultMessage = "Пользователь $userName (ID: $userId):\n";
    $resultMessage .= "Уровень доступа: $levelName ($accessLevel)\n";
    $resultMessage .= "Суперадмин: $isSuperAdmin";
}

// Получаем текущие заявки на доступ
$accessRequests = $adminService->getAllAccessRequests();

// Получаем список тестовых пользователей
$testUsers = [
    230379668,  // Главный админ
    5261465966, // Боря
    1090118386, // Близи
    444234398,  // Олег olegpysh
    // Добавим тестового пользователя из файла access_requests.json
    123456789,  // Тестовый пользователь
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление уровнями доступа</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
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
        h1, h2 {
            color: #333;
        }
        .result {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
        }
        .error {
            background-color: #ffebee;
            border-color: #ffcdd2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            padding: 8px 16px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #2196F3;
            color: white;
        }
        .btn-success {
            background-color: #4CAF50;
            color: white;
        }
        .btn-warning {
            background-color: #FF9800;
            color: white;
        }
        .btn-danger {
            background-color: #f44336;
            color: white;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background-color: #fafafa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Управление уровнями доступа</h1>
        
        <?php if ($resultMessage): ?>
            <div class="result<?= strpos($resultMessage, '❌') !== false ? ' error' : '' ?>">
                <?= nl2br(htmlspecialchars($resultMessage)) ?>
            </div>
        <?php endif; ?>
        
        <!-- Форма для проверки уровня доступа -->
        <div class="card">
            <h2>Проверить уровень доступа пользователя</h2>
            <form method="POST">
                <input type="hidden" name="action" value="check_access">
                <div class="form-group">
                    <label for="check_user_id">User ID:</label>
                    <input type="number" id="check_user_id" name="user_id" required>
                </div>
                <div class="form-group">
                    <label for="check_user_name">Имя пользователя:</label>
                    <input type="text" id="check_user_name" name="user_name" required>
                </div>
                <button type="submit" class="btn btn-primary">Проверить уровень доступа</button>
            </form>
        </div>
        
        <!-- Форма для добавления супервайзера -->
        <div class="card">
            <h2>Добавить супервайзера</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_supervisor">
                <div class="form-group">
                    <label for="supervisor_user_id">User ID:</label>
                    <input type="number" id="supervisor_user_id" name="user_id" required>
                </div>
                <div class="form-group">
                    <label for="supervisor_user_name">Имя пользователя:</label>
                    <input type="text" id="supervisor_user_name" name="user_name" required>
                </div>
                <div class="form-group">
                    <label for="supervisor_chat_id">Chat ID:</label>
                    <input type="text" id="supervisor_chat_id" name="chat_id" required>
                </div>
                <button type="submit" class="btn btn-success">Добавить супервайзера</button>
            </form>
        </div>
        
        <!-- Форма для добавления администратора -->
        <div class="card">
            <h2>Добавить администратора</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_admin">
                <div class="form-group">
                    <label for="add_user_id">User ID:</label>
                    <input type="number" id="add_user_id" name="user_id" required>
                </div>
                <div class="form-group">
                    <label for="add_user_name">Имя пользователя:</label>
                    <input type="text" id="add_user_name" name="user_name" required>
                </div>
                <div class="form-group">
                    <label for="add_chat_id">Chat ID:</label>
                    <input type="text" id="add_chat_id" name="chat_id" required>
                </div>
                <button type="submit" class="btn btn-success">Добавить администратора</button>
            </form>
        </div>
        
        <!-- Форма для сброса уровня доступа до гостя -->
        <div class="card">
            <h2>Сбросить уровень доступа до Гостя</h2>
            <form method="POST">
                <input type="hidden" name="action" value="reset_to_guest">
                <div class="form-group">
                    <label for="reset_user_id">User ID:</label>
                    <input type="number" id="reset_user_id" name="user_id" required>
                </div>
                <div class="form-group">
                    <label for="reset_user_name">Имя пользователя:</label>
                    <input type="text" id="reset_user_name" name="user_name" required>
                </div>
                <button type="submit" class="btn btn-warning">Сбросить до Гостя</button>
            </form>
        </div>
        
        <!-- Заявки на доступ -->
        <div class="card">
            <h2>Заявки на доступ</h2>
            <?php if (empty($accessRequests)): ?>
                <p>Нет активных заявок на доступ.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Имя пользователя</th>
                            <th>Дата подачи</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accessRequests as $request): ?>
                            <tr>
                                <td><?= htmlspecialchars($request['user_id']) ?></td>
                                <td><?= htmlspecialchars($request['user_name']) ?></td>
                                <td><?= date('Y-m-d H:i:s', $request['timestamp']) ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="approve_request">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($request['user_id']) ?>">
                                        <input type="hidden" name="user_name" value="<?= htmlspecialchars($request['user_name']) ?>">
                                        <input type="hidden" name="chat_id" value="<?= htmlspecialchars($request['chat_id']) ?>">
                                        <button type="submit" class="btn btn-success">Одобрить</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Информация о пользователях -->
        <div class="card">
            <h2>Информация о пользователях</h2>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Уровень доступа</th>
                        <th>Суперадмин</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($testUsers as $userId): ?>
                        <?php
                        $accessLevel = $adminService->getUserAccessLevel($userId);
                        $levelName = $adminService->getAccessLevelName($accessLevel);
                        $isSuperAdmin = $adminService->isSuperAdmin($userId) ? "Да" : "Нет";
                        ?>
                        <tr>
                            <td><?= $userId ?></td>
                            <td><?= $levelName ?> (<?= $accessLevel ?>)</td>
                            <td><?= $isSuperAdmin ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>