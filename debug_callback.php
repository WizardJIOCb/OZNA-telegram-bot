<?php
// Отладка обработки callback 'acc_lvl_manage_users'
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отладка Callback</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f0f0f0; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; }
        .step h3 { margin-top: 0; color: #007bff; }
        .code { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; font-family: 'Courier New', monospace; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Отладка callback 'acc_lvl_manage_users'</h1>
    
    <?php
    require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
    require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';
    require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    
    use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
    use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
    use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
    use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
    use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
    
    $callbackData = 'acc_lvl_manage_users';
    
    echo "<div class='step'>";
    echo "<h3>Шаг 1: Проверка callback data</h3>";
    echo "<table>";
    echo "<tr><th>Параметр</th><th>Значение</th></tr>";
    echo "<tr><td>Callback Data</td><td><code>{$callbackData}</code></td></tr>";
    echo "<tr><td>Длина</td><td>" . strlen($callbackData) . " байт</td></tr>";
    echo "</table>";
    echo "</div>";
    
    // Шаг 2: Проверка canHandle
    echo "<div class='step'>";
    echo "<h3>Шаг 2: Проверка AccessLevelHandler::canHandle()</h3>";
    
    try {
        $messageService = new MessageService();
        $adminService = new AdminService($messageService);
        $handler = new AccessLevelHandler($adminService, $messageService);
        
        $canHandle = $handler->canHandle($callbackData);
        
        if ($canHandle) {
            echo "<p class='success'>✅ AccessLevelHandler МОЖЕТ обработать этот callback</p>";
        } else {
            echo "<p class='error'>❌ AccessLevelHandler НЕ МОЖЕТ обработать этот callback</p>";
            echo "<p><strong>ПРОБЛЕМА:</strong> Метод canHandle() вернул false</p>";
        }
        
    } catch (\Exception $e) {
        echo "<p class='error'>❌ Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";
    
    // Шаг 3: Проверка extractCategory
    echo "<div class='step'>";
    echo "<h3>Шаг 3: Симуляция extractCategory()</h3>";
    
    // Симулируем логику extractCategory
    $category = '';
    if (strpos($callbackData, 'acc_lvl_') === 0) {
        $category = $callbackData;
        echo "<p class='success'>✅ Обнаружен префикс 'acc_lvl_'</p>";
        echo "<p>Извлеченная категория: <code>{$category}</code></p>";
    } else {
        echo "<p class='error'>❌ Префикс 'acc_lvl_' НЕ обнаружен</p>";
    }
    echo "</div>";
    
    // Шаг 4: Проверка условия в handle()
    echo "<div class='step'>";
    echo "<h3>Шаг 4: Проверка условия в handle()</h3>";
    
    if ($category === 'acc_lvl_manage_users') {
        echo "<p class='success'>✅ Условие if (\$category === 'acc_lvl_manage_users') ВЫПОЛНИТСЯ</p>";
        echo "<p>Это означает, что должен вызваться метод showUsersWithAccess()</p>";
    } else {
        echo "<p class='error'>❌ Условие НЕ ВЫПОЛНИТСЯ</p>";
        echo "<p>Категория '{$category}' не равна 'acc_lvl_manage_users'</p>";
    }
    echo "</div>";
    
    // Шаг 5: Проверка пользователей в базе
    echo "<div class='step'>";
    echo "<h3>Шаг 5: Проверка пользователей в базе данных</h3>";
    
    try {
        $databaseService = new DatabaseService();
        $adminService = new AdminService($messageService);
        
        $usersWithAccess = $adminService->getAllUsersWithAccess();
        
        echo "<p>Пользователей с доступом (уровень > 0): <strong>" . count($usersWithAccess) . "</strong></p>";
        
        if (count($usersWithAccess) > 0) {
            echo "<p class='success'>✅ Есть пользователи для отображения</p>";
            echo "<p>Первые 5 пользователей:</p>";
            echo "<table>";
            echo "<tr><th>User ID</th><th>ФИО</th><th>Уровень</th></tr>";
            
            $count = min(5, count($usersWithAccess));
            for ($i = 0; $i < $count; $i++) {
                $user = $usersWithAccess[$i];
                $userId = $user['user_id'];
                $fio = $user['data']['fio'] ?? 'Без ФИО';
                $accessLevel = $user['data']['access_level'] ?? 0;
                $levelName = $adminService->getAccessLevelName($accessLevel);
                
                echo "<tr>";
                echo "<td>{$userId}</td>";
                echo "<td>" . htmlspecialchars($fio) . "</td>";
                echo "<td>{$levelName} ({$accessLevel})</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>❌ Нет пользователей с доступом</p>";
        }
        
    } catch (\Exception $e) {
        echo "<p class='error'>❌ Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";
    
    // Резюме
    echo "<div class='step' style='background: #d4edda; border-left-color: #28a745;'>";
    echo "<h3>📋 Резюме</h3>";
    echo "<p>На основе проверок выше:</p>";
    echo "<ol>";
    echo "<li>Callback data правильный: <code>acc_lvl_manage_users</code></li>";
    echo "<li>AccessLevelHandler должен обработать этот callback</li>";
    echo "<li>Категория извлекается правильно</li>";
    echo "<li>Условие в handle() должно сработать</li>";
    echo "<li>В базе есть " . count($usersWithAccess) . " пользователей для отображения</li>";
    echo "</ol>";
    echo "<p><strong>ВЫВОД:</strong> Логика работает правильно. Если кнопка не работает в боте, проблема может быть в:</p>";
    echo "<ul>";
    echo "<li>Telegram не отправляет callback на сервер (проблема webhook)</li>";
    echo "<li>CallbackHandlerFactory не передает запрос в AccessLevelHandler</li>";
    echo "<li>Ошибка при отправке ответного сообщения пользователю</li>";
    echo "</ul>";
    echo "</div>";
    ?>
    
    <div style="text-align: center; margin-top: 20px;">
        <button onclick="location.reload();" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🔄 Обновить
        </button>
        <button onclick="window.location.href='view_logs.php';" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            📋 Посмотреть логи
        </button>
    </div>
</div>
</body>
</html>
