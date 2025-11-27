<?php
// Проверка текущего состояния системы управления пользователями
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Проверка системы управления пользователями</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; }
        .box { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { border-left: 4px solid #4caf50; }
        .error { border-left: 4px solid #f44336; }
        .warning { border-left: 4px solid #ff9800; }
        .info { border-left: 4px solid #2196f3; }
        .check-item { margin: 10px 0; padding: 10px; background: #f9f9f9; border-radius: 4px; }
        .status-ok { color: #4caf50; font-weight: bold; }
        .status-error { color: #f44336; font-weight: bold; }
        .status-warning { color: #ff9800; font-weight: bold; }
        pre { background: #333; color: #fff; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .button { background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Проверка системы управления пользователями</h1>
        
        <?php
        $allOk = true;
        $issues = [];
        
        // 1. Проверка файлов
        echo "<div class='box info'>";
        echo "<h3>1. 📁 Проверка файлов</h3>";
        
        $requiredFiles = [
            'CallbackqueryCommand' => 'CustomCommands/CallbackqueryCommand.php',
            'AccessLevelHandler' => 'CustomCommands/Handlers/AccessLevelHandler.php',
            'CallbackHandlerFactory' => 'CustomCommands/Services/CallbackHandlerFactory.php',
            'AdminService' => 'CustomCommands/Services/AdminService.php',
            'MessageService' => 'CustomCommands/Services/MessageService.php',
            'DatabaseService' => 'CustomCommands/Services/DatabaseService.php'
        ];
        
        foreach ($requiredFiles as $name => $path) {
            $fullPath = __DIR__ . '/' . $path;
            if (file_exists($fullPath)) {
                echo "<div class='check-item'>✅ <strong>{$name}:</strong> найден</div>";
            } else {
                echo "<div class='check-item'>❌ <strong>{$name}:</strong> НЕ НАЙДЕН ({$path})</div>";
                $allOk = false;
                $issues[] = "Отсутствует файл: {$path}";
            }
        }
        echo "</div>";
        
        // 2. Проверка CallbackqueryCommand
        echo "<div class='box info'>";
        echo "<h3>2. 🔄 Проверка CallbackqueryCommand</h3>";
        
        $callbackFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
        if (file_exists($callbackFile)) {
            $content = file_get_contents($callbackFile);
            
            // Проверка early exit фильтра
            if (strpos($content, "strpos(\$callback_data, 'acc_lvl_') !== 0") !== false) {
                echo "<div class='check-item'>✅ Early exit фильтр поддерживает acc_lvl_ callbacks</div>";
            } else {
                echo "<div class='check-item'>❌ Early exit фильтр НЕ поддерживает acc_lvl_ callbacks</div>";
                $allOk = false;
                $issues[] = "Отсутствует поддержка acc_lvl_ в early exit фильтре";
            }
            
            // Проверка регистрации AccessLevelHandler
            if (strpos($content, 'new AccessLevelHandler') !== false) {
                echo "<div class='check-item'>✅ AccessLevelHandler регистрируется</div>";
            } else {
                echo "<div class='check-item'>❌ AccessLevelHandler НЕ регистрируется</div>";
                $allOk = false;
                $issues[] = "AccessLevelHandler не регистрируется в CallbackqueryCommand";
            }
            
            // Проверка логирования
            if (strpos($content, 'simple_callback_test.log') !== false) {
                echo "<div class='check-item'>✅ Диагностическое логирование активно</div>";
            } else {
                echo "<div class='check-item'>⚠️ Диагностическое логирование не найдено</div>";
            }
        }
        echo "</div>";
        
        // 3. Проверка AccessLevelHandler
        echo "<div class='box info'>";
        echo "<h3>3. 👥 Проверка AccessLevelHandler</h3>";
        
        $handlerFile = __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';
        if (file_exists($handlerFile)) {
            $content = file_get_contents($handlerFile);
            
            // Проверка canHandle метода
            if (strpos($content, "callback === 'acc_lvl_manage_users'") !== false) {
                echo "<div class='check-item'>✅ canHandle поддерживает acc_lvl_manage_users</div>";
            } else {
                echo "<div class='check-item'>❌ canHandle НЕ поддерживает acc_lvl_manage_users</div>";
                $allOk = false;
                $issues[] = "AccessLevelHandler.canHandle не поддерживает acc_lvl_manage_users";
            }
            
            // Проверка handle метода
            if (strpos($content, "category === 'acc_lvl_manage_users'") !== false) {
                echo "<div class='check-item'>✅ handle поддерживает acc_lvl_manage_users категорию</div>";
            } else {
                echo "<div class='check-item'>❌ handle НЕ поддерживает acc_lvl_manage_users категорию</div>";
                $allOk = false;
                $issues[] = "AccessLevelHandler.handle не поддерживает категорию acc_lvl_manage_users";
            }
            
            // Проверка метода showUsersWithAccess
            if (strpos($content, 'function showUsersWithAccess') !== false) {
                echo "<div class='check-item'>✅ Метод showUsersWithAccess существует</div>";
            } else {
                echo "<div class='check-item'>❌ Метод showUsersWithAccess НЕ найден</div>";
                $allOk = false;
                $issues[] = "Отсутствует метод showUsersWithAccess в AccessLevelHandler";
            }
        }
        echo "</div>";
        
        // 4. Проверка базы данных
        echo "<div class='box info'>";
        echo "<h3>4. 🗄️ Проверка базы данных</h3>";
        
        try {
            $host = '82.146.42.213';
            $dbname = 'tg_bot';
            $username = 'wj';
            $password = 'k41nz7C&dBr#';
            
            $mysqli = new mysqli($host, $username, $password, $dbname);
            $mysqli->set_charset('utf8');
            
            if ($mysqli->connect_error) {
                throw new Exception("Ошибка подключения: " . $mysqli->connect_error);
            }
            
            echo "<div class='check-item'>✅ Подключение к базе данных успешно</div>";
            
            // Проверка таблицы ozna_data
            $result = $mysqli->query("SHOW TABLES LIKE 'ozna_data'");
            if ($result->num_rows > 0) {
                echo "<div class='check-item'>✅ Таблица ozna_data существует</div>";
                
                // Проверка пользователей с правами администратора
                $result = $mysqli->query("SELECT COUNT(*) as count FROM ozna_data WHERE JSON_EXTRACT(data, '$.access_level') >= 2");
                if ($result) {
                    $row = $result->fetch_assoc();
                    $adminCount = $row['count'];
                    if ($adminCount > 0) {
                        echo "<div class='check-item'>✅ Найдено администраторов: {$adminCount}</div>";
                    } else {
                        echo "<div class='check-item'>⚠️ Не найдено пользователей с правами администратора</div>";
                        $issues[] = "Нет пользователей с уровнем доступа >= 2";
                    }
                }
                
                // Проверка общего количества пользователей
                $result = $mysqli->query("SELECT COUNT(*) as count FROM ozna_data");
                if ($result) {
                    $row = $result->fetch_assoc();
                    $totalUsers = $row['count'];
                    echo "<div class='check-item'>📊 Всего пользователей в системе: {$totalUsers}</div>";
                }
                
            } else {
                echo "<div class='check-item'>❌ Таблица ozna_data НЕ найдена</div>";
                $allOk = false;
                $issues[] = "Отсутствует таблица ozna_data";
            }
            
            $mysqli->close();
            
        } catch (Exception $e) {
            echo "<div class='check-item'>❌ Ошибка БД: " . $e->getMessage() . "</div>";
            $allOk = false;
            $issues[] = "Проблемы с базой данных: " . $e->getMessage();
        }
        echo "</div>";
        
        // 5. Проверка логов
        echo "<div class='box info'>";
        echo "<h3>5. 📋 Проверка логов</h3>";
        
        $logFiles = [
            'deep_callback_monitor.log' => 'Детальный лог callback\'ов',
            'simple_callback_test.log' => 'Простой лог для диагностики'
        ];
        
        foreach ($logFiles as $logFile => $description) {
            $fullPath = __DIR__ . '/' . $logFile;
            if (file_exists($fullPath)) {
                $size = filesize($fullPath);
                $lastModified = date('Y-m-d H:i:s', filemtime($fullPath));
                echo "<div class='check-item'>📄 <strong>{$description}:</strong> найден (размер: {$size} байт, изменен: {$lastModified})</div>";
            } else {
                echo "<div class='check-item'>📄 <strong>{$description}:</strong> не найден</div>";
            }
        }
        echo "</div>";
        
        // Итоговый результат
        if ($allOk && empty($issues)) {
            echo "<div class='box success'>";
            echo "<h3>✅ Система готова к работе</h3>";
            echo "<p>Все компоненты системы управления пользователями настроены правильно.</p>";
            echo "<p><strong>Возможные причины проблемы:</strong></p>";
            echo "<ul>";
            echo "<li>У пользователя недостаточно прав (нужен уровень доступа >= 2)</li>";
            echo "<li>Проблемы с webhook'ом Telegram</li>";
            echo "<li>Временные проблемы с сетью</li>";
            echo "</ul>";
        } else {
            echo "<div class='box error'>";
            echo "<h3>❌ Найдены проблемы</h3>";
            echo "<p>Обнаружены следующие проблемы:</p>";
            echo "<ul>";
            foreach ($issues as $issue) {
                echo "<li>{$issue}</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
        ?>
        
        <div class="box info">
            <h3>🛠️ Инструменты диагностики</h3>
            <p>Используйте эти инструменты для дальнейшей диагностики:</p>
            <a href="simple_callback_monitor.php" class="button">📊 Мониторинг callback'ов</a>
            <a href="check_user_access_level.php" class="button">👤 Проверка прав доступа</a>
            <a href="debug_user_management_button.php" class="button">🔍 Детальная диагностика</a>
        </div>
        
        <div class="box info">
            <h3>📝 Инструкция по диагностике</h3>
            <p>Для полной диагностики проблемы выполните следующие шаги:</p>
            <ol>
                <li><strong>Проверьте callback'и:</strong> Используйте мониторинг callback'ов, чтобы убедиться, что они доходят до сервера</li>
                <li><strong>Проверьте права доступа:</strong> Убедитесь, что у пользователя есть права администратора (уровень >= 2)</li>
                <li><strong>Проверьте логи:</strong> Просмотрите логи сервера на наличие ошибок PHP</li>
                <li><strong>Протестируйте с другим пользователем:</strong> Попробуйте с аккаунта суперадминистратора</li>
            </ol>
        </div>
    </div>
</body>
</html>