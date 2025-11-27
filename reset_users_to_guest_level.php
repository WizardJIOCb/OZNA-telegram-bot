<?php
require_once __DIR__ . '/Custome/DB.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Сброс пользователей на уровень Гость ===\n\n";

// Инициализация сервисов
$messageService = new MessageService();
$databaseService = new DatabaseService();
$adminService = new AdminService($messageService);

// Список пользователей, которых НЕ нужно сбрасывать (суперадмины и первые админы)
$excludedUsers = [
    230379668,  // Главный админ
    5261465966, // Боря
    1090118386, // Близи
    444234398,  // Олег olegpysh
    // Добавьте сюда ID первых администраторов
];

echo "Исключенные пользователи (не будут сброшены):\n";
foreach ($excludedUsers as $userId) {
    echo "- $userId\n";
}
echo "\n";

// Подключение к базе данных для получения всех пользователей
$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

try {
    $mysqli = new mysqli($host, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        die("Ошибка подключения: " . $mysqli->connect_error);
    }
    
    echo "Подключение к базе данных успешно установлено.\n\n";
    
    // Получение всех пользователей из таблицы ozna_data
    $result = $mysqli->query("SELECT user_id, data FROM ozna_data");
    
    if ($result) {
        $usersProcessed = 0;
        $usersReset = 0;
        $usersExcluded = 0;
        
        echo "Обработка пользователей:\n";
        echo str_repeat("-", 50) . "\n";
        
        while ($row = $result->fetch_assoc()) {
            $userId = $row['user_id'];
            $usersProcessed++;
            
            // Проверяем, не находится ли пользователь в списке исключений
            if (in_array($userId, $excludedUsers)) {
                echo "Пользователь $userId: Исключен из сброса\n";
                $usersExcluded++;
                continue;
            }
            
            // Декодируем данные пользователя
            $userData = json_decode($row['data'], true);
            
            // Проверяем текущий уровень доступа
            $currentLevel = $userData['access_level'] ?? DatabaseService::ACCESS_LEVEL_GUEST;
            $currentLevelName = $adminService->getAccessLevelName($currentLevel);
            
            // Если пользователь не гость, сбрасываем его уровень
            if ($currentLevel != DatabaseService::ACCESS_LEVEL_GUEST) {
                // Обновляем уровень доступа до гостя
                $userData['access_level'] = DatabaseService::ACCESS_LEVEL_GUEST;
                
                // Обновляем данные в базе
                $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
                $updateResult = $mysqli->query("UPDATE ozna_data SET data = '" . $mysqli->real_escape_string($jsonData) . "' WHERE user_id = $userId");
                
                if ($updateResult) {
                    echo "Пользователь $userId: Уровень сброшен с $currentLevelName ($currentLevel) до Гость (0)\n";
                    $usersReset++;
                    
                    // Отправляем уведомление пользователю
                    try {
                        $message = "ℹ️ <b>Обновление доступа</b>\n\n";
                        $message .= "В боте внедрена новая система уровней доступа.\n";
                        $message .= "Ваш уровень доступа был сброшен до уровня Гость.\n\n";
                        $message .= "Для получения доступа к функциям бота необходимо подать заявку через меню Профиль → Уровень доступа.";
                        // Здесь можно отправить сообщение пользователю, если известен его chat_id
                    } catch (Exception $e) {
                        // Игнорируем ошибки отправки уведомлений
                    }
                } else {
                    echo "Пользователь $userId: Ошибка при сбросе уровня\n";
                }
            } else {
                echo "Пользователь $userId: Уже имеет уровень Гость\n";
            }
        }
        
        echo "\n" . str_repeat("-", 50) . "\n";
        echo "Статистика:\n";
        echo "Всего обработано пользователей: $usersProcessed\n";
        echo "Сброшено пользователей: $usersReset\n";
        echo "Исключено пользователей: $usersExcluded\n";
        
    } else {
        echo "Ошибка при получении данных: " . $mysqli->error . "\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== Сброс пользователей завершен ===\n";
?>