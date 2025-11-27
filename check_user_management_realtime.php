<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Проверка кнопки управления пользователями</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1e1e1e; color: #fff; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #4fc1ff; }
        .box { background: #2d2d2d; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #4fc1ff; }
        .success { border-left-color: #28a745; background: #1a3d1a; }
        .error { border-left-color: #dc3545; background: #3d1a1a; }
        .warning { border-left-color: #ffc107; background: #3d2e1a; }
        .info { border-left-color: #17a2b8; background: #1a2e3d; }
        .log-entry { background: #1a1a1a; padding: 10px; margin: 5px 0; border-left: 3px solid #28a745; font-family: monospace; font-size: 12px; }
        .button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; text-decoration: none; display: inline-block; }
        .button:hover { background: #0056b3; }
        .clear-btn { background: #dc3545; }
        .clear-btn:hover { background: #c82333; }
        pre { background: #000; padding: 10px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        .step { background: #264f78; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .step h3 { margin-top: 0; color: #4fc1ff; }
        ol { line-height: 1.8; }
    </style>
    <script>
        function clearLog() {
            if(confirm('Очистить лог?')) {
                window.location.href='?clear=1';
            }
        }
        
        function autoRefresh() {
            setTimeout(function() {
                location.reload();
            }, 3000);
        }
    </script>
</head>
<body onload="autoRefresh()">
    <div class="container">
        <h1>🔍 Диагностика: Управление пользователями</h1>
        
        <?php
        // Clear log if requested
        if (isset($_GET['clear'])) {
            $logFile = __DIR__ . '/deep_callback_monitor.log';
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
                echo "<div class='box success'><h3>✅ Лог очищен успешно</h3></div>";
            }
        }
        ?>
        
        <div class="step">
            <h3>📋 Инструкция:</h3>
            <ol>
                <li><strong>Очистите лог:</strong> Нажмите кнопку "Очистить лог" ниже</li>
                <li><strong>Откройте бота в Telegram</strong></li>
                <li><strong>Перейдите:</strong> Главное меню → Админка → 🔐 Управление уровнями доступа</li>
                <li><strong>Нажмите:</strong> "👥 Управление пользователями"</li>
                <li><strong>Подождите 3-5 секунд</strong> и проверьте результаты на этой странице</li>
            </ol>
        </div>
        
        <div style="text-align: center; margin: 20px 0;">
            <button class="button clear-btn" onclick="clearLog()">🗑️ Очистить лог</button>
            <button class="button" onclick="location.reload();">🔄 Обновить сейчас</button>
        </div>

        <?php
        // Проверяем существование обработчика
        $handlerPath = __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';
        if (file_exists($handlerPath)) {
            echo "<div class='box success'>";
            echo "<h3>✅ Файл AccessLevelHandler найден</h3>";
            echo "<p>Путь: " . htmlspecialchars($handlerPath) . "</p>";
            echo "</div>";
        } else {
            echo "<div class='box error'>";
            echo "<h3>❌ Файл AccessLevelHandler НЕ найден</h3>";
            echo "<p>Ожидаемый путь: " . htmlspecialchars($handlerPath) . "</p>";
            echo "</div>";
        }
        
        // Проверяем базу данных на наличие пользователей
        $configPath = __DIR__ . '/config.php';
        if (file_exists($configPath)) {
            require_once $configPath;
            
            try {
                $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                
                if ($mysqli->connect_error) {
                    echo "<div class='box error'>";
                    echo "<h3>❌ Ошибка подключения к базе данных</h3>";
                    echo "<p>" . htmlspecialchars($mysqli->connect_error) . "</p>";
                    echo "</div>";
                } else {
                    // Проверяем количество пользователей с уровнем доступа > 0
                    $result = $mysqli->query("SELECT COUNT(*) as count FROM ozna_data WHERE JSON_EXTRACT(data, '$.access_level') > 0");
                    
                    if ($result) {
                        $row = $result->fetch_assoc();
                        $userCount = $row['count'];
                        
                        echo "<div class='box " . ($userCount > 0 ? 'success' : 'warning') . "'>";
                        echo "<h3>👥 Пользователи с доступом в базе данных</h3>";
                        echo "<p>Количество пользователей с уровнем доступа выше Гостя: <strong>{$userCount}</strong></p>";
                        
                        if ($userCount > 0) {
                            echo "<p>✅ В базе есть пользователи для отображения</p>";
                            
                            // Получаем примеры пользователей
                            $usersResult = $mysqli->query("SELECT user_id, data FROM ozna_data WHERE JSON_EXTRACT(data, '$.access_level') > 0 LIMIT 5");
                            if ($usersResult && $usersResult->num_rows > 0) {
                                echo "<h4>Примеры пользователей:</h4>";
                                echo "<pre>";
                                while ($userRow = $usersResult->fetch_assoc()) {
                                    $userData = json_decode($userRow['data'], true);
                                    $fio = $userData['fio'] ?? 'Без ФИО';
                                    $accessLevel = $userData['access_level'] ?? 0;
                                    echo "ID: {$userRow['user_id']}, ФИО: {$fio}, Уровень: {$accessLevel}\n";
                                }
                                echo "</pre>";
                            }
                        } else {
                            echo "<p>⚠️ В базе нет пользователей с уровнем доступа выше Гостя</p>";
                            echo "<p>Список будет пустым даже если кнопка работает правильно</p>";
                        }
                        echo "</div>";
                    }
                    
                    $mysqli->close();
                }
            } catch (Exception $e) {
                echo "<div class='box error'>";
                echo "<h3>❌ Ошибка при проверке базы данных</h3>";
                echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
                echo "</div>";
            }
        }
        ?>

        <div class="box info">
            <h3>📊 Анализ последних callback'ов</h3>
            <?php
            $logFile = __DIR__ . '/deep_callback_monitor.log';
            
            if (!file_exists($logFile)) {
                echo "<p class='warning'>⚠️ Файл лога не существует</p>";
                echo "<p>Лог будет создан после первого нажатия кнопки в боте</p>";
            } else {
                $logContent = file_get_contents($logFile);
                
                if (empty(trim($logContent))) {
                    echo "<p class='warning'>⚠️ Лог пустой</p>";
                    echo "<p>Нажмите кнопку в боте, чтобы увидеть callback'и здесь</p>";
                } else {
                    // Анализируем последние записи
                    $entries = explode("=== CALLBACK RECEIVED AT", $logContent);
                    $entries = array_filter($entries, function($entry) {
                        return !empty(trim($entry));
                    });
                    
                    $recentEntries = array_slice($entries, -10); // Последние 10 записей
                    
                    echo "<h4>📝 Последние 10 callback'ов:</h4>";
                    
                    $foundAccLvl = false;
                    
                    foreach (array_reverse($recentEntries) as $entry) {
                        $fullEntry = "=== CALLBACK RECEIVED AT" . $entry;
                        
                        // Проверяем на acc_lvl в callback data
                        if (preg_match("/Callback Data: '(acc_lvl_[^']+)'/", $fullEntry, $matches)) {
                            $foundAccLvl = true;
                            echo "<div class='log-entry' style='border-left-color: #28a745;'>";
                            echo "<strong style='color: #28a745;'>✅ НАЙДЕН CALLBACK: {$matches[1]}</strong><br>";
                            echo "<pre>" . htmlspecialchars($fullEntry) . "</pre>";
                            echo "</div>";
                        } elseif (strpos($fullEntry, "acc_lvl") !== false) {
                            echo "<div class='log-entry' style='border-left-color: #ffc107;'>";
                            echo "<strong style='color: #ffc107;'>⚠️ Упоминание acc_lvl (но не в Callback Data)</strong><br>";
                            echo "<pre>" . htmlspecialchars($fullEntry) . "</pre>";
                            echo "</div>";
                        } else {
                            // Показываем сокращенную версию других callback'ов
                            if (preg_match("/Callback Data: '([^']+)'/", $fullEntry, $matches)) {
                                echo "<div class='log-entry' style='border-left-color: #555;'>";
                                echo "Callback: <code>" . htmlspecialchars($matches[1]) . "</code>";
                                echo "</div>";
                            }
                        }
                    }
                    
                    if (!$foundAccLvl) {
                        echo "<div class='box error'>";
                        echo "<h4>❌ Проблема найдена!</h4>";
                        echo "<p>В последних 10 callback'ах не найдено <code>acc_lvl_manage_users</code></p>";
                        echo "<p><strong>Это означает:</strong></p>";
                        echo "<ul>";
                        echo "<li>Кнопка не отправляет правильные данные, ИЛИ</li>";
                        echo "<li>Вы еще не нажали на кнопку 'Управление пользователями'</li>";
                        echo "</ul>";
                        echo "</div>";
                    }
                }
            }
            ?>
        </div>

        <div class="box">
            <h3>🎯 Что проверяем:</h3>
            <ul>
                <li>✅ Файл AccessLevelHandler.php существует</li>
                <li>✅ В базе есть пользователи для отображения</li>
                <li>❓ Callback 'acc_lvl_manage_users' доходит до сервера</li>
                <li>❓ Обработчик получает и обрабатывает callback</li>
            </ul>
        </div>

        <div class="box warning">
            <h3>⚠️ Если callback не найден:</h3>
            <p>Возможные причины:</p>
            <ol>
                <li><strong>Webhook не настроен:</strong> Telegram не отправляет данные на сервер</li>
                <li><strong>Кнопка имеет неправильный callback_data:</strong> Проверьте код кнопки</li>
                <li><strong>Callback блокируется до логирования:</strong> Проблема в CallbackqueryCommand</li>
            </ol>
        </div>

        <div style="margin-top: 30px; padding: 15px; background: #264f78; border-radius: 5px;">
            <p><strong>Автообновление:</strong> Страница обновляется каждые 3 секунды</p>
            <p>Последнее обновление: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
