<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Простая диагностика</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff; }
        .success { border-left-color: #28a745; }
        .error { border-left-color: #dc3545; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Простая диагностика callback</h1>
    
    <?php
    echo "<div class='box'>";
    echo "<h3>Шаг 1: Проверка callback data</h3>";
    
    $callbackData = 'acc_lvl_manage_users';
    echo "Callback: <code>{$callbackData}</code><br>";
    echo "Длина: " . strlen($callbackData) . " байт<br>";
    
    // Проверка префикса
    if (strpos($callbackData, 'acc_lvl_') === 0) {
        echo "<p style='color: green;'>✅ Префикс 'acc_lvl_' найден</p>";
    } else {
        echo "<p style='color: red;'>❌ Префикс 'acc_lvl_' НЕ найден</p>";
    }
    echo "</div>";
    
    echo "<div class='box'>";
    echo "<h3>Шаг 2: Проверка файлов</h3>";
    
    $files = [
        'AccessLevelHandler.php' => __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php',
        'CallbackHandlerFactory.php' => __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php',
        'AdminService.php' => __DIR__ . '/CustomCommands/Services/AdminService.php'
    ];
    
    foreach ($files as $name => $path) {
        if (file_exists($path)) {
            echo "✅ {$name} существует<br>";
        } else {
            echo "❌ {$name} НЕ найден<br>";
        }
    }
    echo "</div>";
    
    echo "<div class='box'>";
    echo "<h3>Шаг 3: Проверка базы данных</h3>";
    
    try {
        // Простое подключение к БД
        $mysqli = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
        
        if ($mysqli->connect_error) {
            echo "<p style='color: red;'>❌ Ошибка подключения: " . $mysqli->connect_error . "</p>";
        } else {
            echo "<p style='color: green;'>✅ Подключение к БД успешно</p>";
            
            // Получаем количество пользователей с доступом
            $result = $mysqli->query("SELECT COUNT(*) as count FROM ozna_data WHERE JSON_EXTRACT(data, '$.access_level') > 0");
            
            if ($result) {
                $row = $result->fetch_assoc();
                echo "Пользователей с доступом: <strong>" . $row['count'] . "</strong><br>";
            }
            
            $mysqli->close();
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    echo "<div class='box'>";
    echo "<h3>Шаг 4: Логика обработки</h3>";
    echo "<p>При нажатии кнопки 'Управление пользователями':</p>";
    echo "<ol>";
    echo "<li>Telegram отправляет callback_data: <code>acc_lvl_manage_users</code></li>";
    echo "<li>CallbackHandlerFactory извлекает категорию</li>";
    echo "<li>AccessLevelHandler проверяет canHandle()</li>";
    echo "<li>Если true - вызывается handle()</li>";
    echo "<li>В handle() проверяется условие: if (\$category === 'acc_lvl_manage_users')</li>";
    echo "<li>Вызывается showUsersWithAccess()</li>";
    echo "<li>Отправляется сообщение со списком пользователей</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='box success'>";
    echo "<h3>📋 Что делать дальше?</h3>";
    echo "<p><strong>Попробуйте следующее:</strong></p>";
    echo "<ol>";
    echo "<li>Откройте бота в Telegram</li>";
    echo "<li>Нажмите на ЛЮБУЮ другую кнопку (например, 'Профиль')</li>";
    echo "<li>Проверьте, что эта кнопка работает</li>";
    echo "<li>Затем вернитесь в меню 'Управление уровнями доступа'</li>";
    echo "<li>Нажмите 'Управление пользователями'</li>";
    echo "<li>Напишите мне, что произошло:</li>";
    echo "<ul>";
    echo "<li>Ничего не происходит (кнопка не реагирует)</li>";
    echo "<li>Появляется сообщение об ошибке</li>";
    echo "<li>Появляется пустой список</li>";
    echo "<li>Что-то другое</li>";
    echo "</ul>";
    echo "</ol>";
    echo "</div>";
    ?>
    
    <div style="text-align: center; margin: 20px;">
        <button onclick="location.reload();" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🔄 Обновить
        </button>
    </div>
</body>
</html>
