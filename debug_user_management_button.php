<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Диагностика кнопки "Управление пользователями"</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1e1e1e; color: #fff; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #4fc1ff; }
        .box { background: #2d2d2d; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #4fc1ff; }
        .log-entry { background: #1a1a1a; padding: 10px; margin: 5px 0; border-left: 3px solid #28a745; font-family: monospace; font-size: 12px; }
        .error { border-left-color: #dc3545; }
        .success { border-left-color: #28a745; }
        .warning { border-left-color: #ffc107; }
        .no-data { color: #ffc107; }
        .button { 
            background: #007bff; color: white; padding: 10px 20px; border: none; 
            border-radius: 5px; cursor: pointer; margin: 5px; text-decoration: none; display: inline-block;
        }
        .button:hover { background: #0056b3; }
        .clear-btn { background: #dc3545; }
        .clear-btn:hover { background: #c82333; }
        .test-btn { background: #28a745; }
        .test-btn:hover { background: #218838; }
        .debug-section { margin: 20px 0; }
        .step { background: #264f78; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .step h3 { margin-top: 0; color: #4fc1ff; }
        pre { background: #000; padding: 10px; border-radius: 5px; overflow-x: auto; }
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
            }, 5000);
        }
    </script>
</head>
<body onload="autoRefresh()">
    <div class="container">
        <h1>🔍 Диагностика кнопки "Управление пользователями"</h1>
        
        <?php
        // Clear log if requested
        if (isset($_GET['clear'])) {
            $logFile = __DIR__ . '/deep_callback_monitor.log';
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
                echo "<div class='box success'><h3>✅ Лог очищен</h3></div>";
            }
        }
        ?>
        
        <div class="debug-section">
            <div class="step">
                <h3>📋 Пошаговая инструкция:</h3>
                <ol>
                    <li><strong>Очистите лог</strong> - нажмите кнопку "Очистить лог" ниже</li>
                    <li><strong>Откройте бота</strong> в Telegram</li>
                    <li><strong>Перейдите</strong>: Админка → 🔐 Управление уровнями доступа</li>
                    <li><strong>Нажмите</strong> "👥 Управление пользователями"</li>
                    <li><strong>Подождите</strong> 3-5 секунд</li>
                    <li><strong>Проверьте результат</strong> на этой странице</li>
                </ol>
            </div>
            
            <div style="text-align: center; margin: 20px 0;">
                <button class="button clear-btn" onclick="clearLog()">🗑️ Очистить лог</button>
                <button class="button" onclick="location.reload();">🔄 Обновить</button>
                <a href="check_access_level_handler.php" class="button test-btn">🧪 Проверить AccessLevelHandler</a>
            </div>
        </div>

        <div class="box">
            <h3>📊 Мониторинг callback'ов в реальном времени</h3>
            <?php
            $logFile = __DIR__ . '/deep_callback_monitor.log';
            
            if (!file_exists($logFile)) {
                echo "<p class='no-data'>Файл лога не существует. Попробуйте нажать кнопку в боте.</p>";
            } else {
                $logContent = file_get_contents($logFile);
                
                if (empty(trim($logContent))) {
                    echo "<p class='no-data'>Лог пустой. Callback'и не поступают на сервер.</p>";
                    echo "<p class='warning'>⚠️ Это означает, что кнопка не отправляет callback данные или они блокируются.</p>";
                } else {
                    echo "<h4>📝 Последние callback'и:</h4>";
                    
                    // Show last 5 entries
                    $entries = explode("=== CALLBACK RECEIVED AT", $logContent);
                    $entries = array_filter($entries, function($entry) {
                        return !empty(trim($entry));
                    });
                    
                    $entries = array_slice($entries, -5); // Last 5 entries
                    
                    foreach (array_reverse($entries) as $entry) {
                        if (!empty(trim($entry))) {
                            $fullEntry = "=== CALLBACK RECEIVED AT" . $entry;
                            
                            // Check if this is about acc_lvl_manage_users
                            if (strpos($fullEntry, 'acc_lvl_manage_users') !== false) {
                                echo "<div class='log-entry success'>";
                                echo "<strong>✅ НАЙДЕН CALLBACK ДЛЯ УПРАВЛЕНИЯ ПОЛЬЗОВАТЕЛЯМИ!</strong><br>";
                            } else {
                                echo "<div class='log-entry'>";
                            }
                            
                            echo "<pre>" . htmlspecialchars($fullEntry) . "</pre>";
                            echo "</div>";
                        }
                    }
                }
            }
            ?>
        </div>

        <div class="box">
            <h3>🔧 Проверка обработчика AccessLevelHandler</h3>
            <?php
            // Test if AccessLevelHandler can handle the callback
            $testCallback = 'acc_lvl_manage_users';
            
            // Include the handler file
            $handlerFile = __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';
            if (file_exists($handlerFile)) {
                echo "<p>✅ Файл AccessLevelHandler найден</p>";
                
                // Test canHandle method
                echo "<h4>Тест canHandle метода:</h4>";
                echo "<pre>";
                
                // Simulate the test
                $canHandleResult = false;
                $isAccessLevelManagement = (
                    $testCallback === 'acc_lvl_users' ||
                    $testCallback === 'acc_lvl_manage_users' ||
                    strpos($testCallback, 'acc_lvl_user_details_') === 0 ||
                    strpos($testCallback, 'acc_lvl_set_level_') === 0
                );
                
                echo "Callback data: '{$testCallback}'\n";
                echo "isAccessLevelManagement: " . ($isAccessLevelManagement ? 'true' : 'false') . "\n";
                echo "canHandle result: " . ($isAccessLevelManagement ? 'true' : 'false') . "\n";
                
                if ($isAccessLevelManagement) {
                    echo "\n✅ AccessLevelHandler МОЖЕТ обработать этот callback!";
                } else {
                    echo "\n❌ AccessLevelHandler НЕ МОЖЕТ обработать этот callback";
                }
                
                echo "</pre>";
            } else {
                echo "<p class='error'>❌ Файл AccessLevelHandler не найден: {$handlerFile}</p>";
            }
            ?>
        </div>

        <div class="box">
            <h3>📋 Возможные причины проблемы</h3>
            
            <div class="step">
                <h4>1. Callback не доходит до сервера</h4>
                <p>Если в логе нет записей, значит Telegram не отправляет callback или он блокируется.</p>
                <ul>
                    <li>Проверьте интернет соединение</li>
                    <li>Убедитесь, что webhook настроен правильно</li>
                    <li>Проверьте, что кнопка имеет правильный callback_data</li>
                </ul>
            </div>
            
            <div class="step">
                <h4>2. Callback блокируется в CallbackqueryCommand</h4>
                <p>Callback может быть заблокирован в early exit фильтре.</p>
                <p>Ожидаемый callback_data: <code>acc_lvl_manage_users</code></p>
                <p>Этот callback должен проходить фильтр, так как начинается с <code>acc_lvl_</code></p>
            </div>
            
            <div class="step">
                <h4>3. AccessLevelHandler не может обработать callback</h4>
                <p>Если callback доходит, но не обрабатывается, проблема в логике обработчика.</p>
                <p>Проверьте метод canHandle в AccessLevelHandler.php</p>
            </div>
            
            <div class="step">
                <h4>4. Проблема с доступом пользователя</h4>
                <p>У пользователя может не быть прав для управления пользователями.</p>
                <p>Требуется уровень доступа: Администратор (2) или выше</p>
            </div>
        </div>

        <div class="box">
            <h3>🎯 Следующие шаги</h3>
            <p>В зависимости от результатов выше:</p>
            <ul>
                <li><strong>Если callback'и не поступают</strong> - проблема с webhook или кнопкой</li>
                <li><strong>Если callback'и поступают, но не обрабатываются</strong> - проблема в коде обработчика</li>
                <li><strong>Если все работает, но пользователь видит ошибку</strong> - проблема с правами доступа</li>
            </ul>
        </div>
    </div>
</body>
</html>