<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Лог кнопки "Управление пользователями"</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1e1e1e; color: #fff; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #4fc1ff; }
        .box { background: #2d2d2d; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #4fc1ff; }
        .log-entry { background: #1a1a1a; padding: 10px; margin: 5px 0; border-left: 3px solid #28a745; font-family: monospace; }
        .no-data { color: #ffc107; }
        .button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .button:hover { background: #0056b3; }
        .clear-btn { background: #dc3545; }
        .clear-btn:hover { background: #c82333; }
        .instruction { background: #264f78; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
    <script>
        function autoRefresh() {
            setTimeout(function() {
                location.reload();
            }, 3000);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>📋 Лог кнопки "Управление пользователями"</h1>
        
        <div class="instruction">
            <strong>📝 ИНСТРУКЦИЯ:</strong><br>
            1. Нажмите кнопку "Очистить лог" ниже<br>
            2. Откройте бота в Telegram<br>
            3. Перейдите: Админка → 🔐 Управление уровнями доступа<br>
            4. Нажмите "👥 Управление пользователями"<br>
            5. Подождите 2 секунды<br>
            6. Нажмите "Обновить лог" здесь<br>
            <br>
            <strong>Если появилась запись в логе</strong> - callback доходит до сервера ✅<br>
            <strong>Если лог пустой</strong> - callback НЕ доходит до сервера ❌
        </div>
        
        <div style="text-align: center; margin: 20px 0;">
            <button class="button" onclick="location.reload();">🔄 Обновить лог</button>
            <button class="button clear-btn" onclick="if(confirm('Очистить лог?')) window.location.href='?clear=1';">🗑️ Очистить лог</button>
            <button class="button" onclick="autoRefresh();" style="background: #28a745;">⏱️ Автообновление (3 сек)</button>
        </div>
        
        <?php
        $logFile = __DIR__ . '/access_level_callback_debug.log';
        
        // Очистка лога
        if (isset($_GET['clear'])) {
            file_put_contents($logFile, '');
            echo "<div class='box' style='border-left-color: #28a745;'>";
            echo "<p>✅ Лог очищен! Теперь нажмите кнопку в боте.</p>";
            echo "</div>";
        }
        
        echo "<div class='box'>";
        echo "<h3>Записи в логе:</h3>";
        
        if (!file_exists($logFile)) {
            echo "<p class='no-data'>⚠️ Файл лога еще не создан. Нажмите кнопку в боте.</p>";
        } else {
            $content = file_get_contents($logFile);
            
            if (empty(trim($content))) {
                echo "<p class='no-data'>⚠️ Лог пустой. Callback запросы НЕ поступают на сервер.</p>";
                echo "<p>Это означает, что:</p>";
                echo "<ul>";
                echo "<li>Кнопка не отправляет callback, ИЛИ</li>";
                echo "<li>Telegram не может доставить callback на сервер, ИЛИ</li>";
                echo "<li>Webhook не настроен правильно</li>";
                echo "</ul>";
            } else {
                $lines = explode("\n", trim($content));
                $count = count($lines);
                
                echo "<p style='color: #28a745;'>✅ Найдено записей: <strong>{$count}</strong></p>";
                echo "<p>Последние 20 записей (новые сверху):</p>";
                
                $lines = array_reverse($lines);
                $lines = array_slice($lines, 0, 20);
                
                foreach ($lines as $line) {
                    if (!empty(trim($line))) {
                        echo "<div class='log-entry'>" . htmlspecialchars($line) . "</div>";
                    }
                }
            }
        }
        echo "</div>";
        
        // Дополнительная информация
        echo "<div class='box' style='border-left-color: #ffc107;'>";
        echo "<h3>🔍 Что делать дальше?</h3>";
        
        if (file_exists($logFile) && !empty(trim(file_get_contents($logFile)))) {
            echo "<p style='color: #28a745;'><strong>✅ CALLBACK ДОХОДИТ ДО СЕРВЕРА!</strong></p>";
            echo "<p>Это означает, что проблема в обработке callback или отправке ответа.</p>";
            echo "<p><strong>Следующий шаг:</strong> Проверьте основной лог для деталей:</p>";
            echo "<p><a href='view_logs.php' style='color: #4fc1ff;'>📋 Открыть полный лог</a></p>";
        } else {
            echo "<p style='color: #dc3545;'><strong>❌ CALLBACK НЕ ДОХОДИТ ДО СЕРВЕРА</strong></p>";
            echo "<p>Возможные причины:</p>";
            echo "<ul>";
            echo "<li>Кнопка создана неправильно (нет callback_data)</li>";
            echo "<li>Проблема с webhook Telegram</li>";
            echo "<li>Сервер недоступен для Telegram</li>";
            echo "</ul>";
            echo "<p><strong>Проверьте webhook:</strong> <a href='test_webhook.php' style='color: #4fc1ff;'>Диагностика webhook</a></p>";
        }
        echo "</div>";
        
        echo "<div style='text-align: center; color: #666; margin-top: 30px;'>";
        echo "Обновлено: " . date('Y-m-d H:i:s');
        echo "</div>";
        ?>
    </div>
</body>
</html>
