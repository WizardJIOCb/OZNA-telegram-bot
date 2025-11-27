<?php
// Простой просмотрщик логов
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр логов бота</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background-color: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #4ec9b0;
            text-align: center;
        }
        .log-box {
            background-color: #252526;
            border: 1px solid #3e3e42;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
            max-height: 600px;
            overflow-y: auto;
        }
        .log-line {
            margin: 5px 0;
            padding: 5px;
            border-left: 3px solid transparent;
        }
        .log-line.callback {
            background-color: #264f78;
            border-left-color: #4fc1ff;
        }
        .log-line.error {
            background-color: #5a1d1d;
            border-left-color: #f48771;
        }
        .log-line.access {
            background-color: #1e5a1e;
            border-left-color: #4ec9b0;
        }
        .button {
            background-color: #0e639c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        .button:hover {
            background-color: #1177bb;
        }
        .info {
            background-color: #264f78;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .highlight {
            background-color: #ffff00;
            color: #000;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Просмотр логов Telegram бота</h1>
        
        <div class="info">
            <strong>Инструкция:</strong><br>
            1. Нажмите кнопку "Обновить логи"<br>
            2. Откройте бота в Telegram<br>
            3. Нажмите "Управление пользователями"<br>
            4. Снова нажмите "Обновить логи" здесь
        </div>
        
        <div style="text-align: center;">
            <button class="button" onclick="location.reload();">🔄 Обновить логи</button>
            <button class="button" onclick="clearLogs();">🗑️ Очистить логи</button>
        </div>
        
        <div class="log-box">
            <?php
            $logFile = __DIR__ . '/telegram_bot_error.log';
            
            if (!file_exists($logFile)) {
                echo '<div class="log-line error">❌ Файл лога не найден</div>';
            } else {
                $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                
                if (empty($logs)) {
                    echo '<div class="log-line">ℹ️ Логи пусты. Нажмите кнопку в боте.</div>';
                } else {
                    // Показываем последние 100 строк
                    $logs = array_slice($logs, -100);
                    
                    foreach ($logs as $line) {
                        $class = '';
                        
                        // Определяем тип лога
                        if (stripos($line, 'CALLBACK') !== false || stripos($line, '===') !== false) {
                            $class = 'callback';
                        } elseif (stripos($line, 'error') !== false || stripos($line, 'ERROR') !== false) {
                            $class = 'error';
                        } elseif (stripos($line, 'acc_lvl') !== false || stripos($line, 'AccessLevel') !== false) {
                            $class = 'access';
                        }
                        
                        // Подсвечиваем важные слова
                        $line = htmlspecialchars($line);
                        $line = str_replace('acc_lvl_manage_users', '<span class="highlight">acc_lvl_manage_users</span>', $line);
                        $line = str_replace('AccessLevelHandler', '<span class="highlight">AccessLevelHandler</span>', $line);
                        $line = str_replace('CALLBACK QUERY', '<span class="highlight">CALLBACK QUERY</span>', $line);
                        
                        echo "<div class='log-line {$class}'>{$line}</div>";
                    }
                }
            }
            ?>
        </div>
        
        <div style="text-align: center; margin-top: 20px; color: #858585;">
            Последнее обновление: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
    
    <script>
        function clearLogs() {
            if (confirm('Очистить все логи?')) {
                window.location.href = '?action=clear';
            }
        }
        
        <?php
        if (isset($_GET['action']) && $_GET['action'] === 'clear') {
            $logFile = __DIR__ . '/telegram_bot_error.log';
            file_put_contents($logFile, '');
            echo "alert('Логи очищены!'); window.location.href = 'view_logs.php';";
        }
        ?>
        
        // Автообновление каждые 5 секунд
        // setTimeout(function() {
        //     location.reload();
        // }, 5000);
    </script>
</body>
</html>
