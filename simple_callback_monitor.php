<?php
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Простой мониторинг кнопки</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; }
        .log-box { background: white; padding: 20px; border-radius: 5px; margin: 10px 0; }
        .instruction { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .button { background: #2196f3; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .clear-btn { background: #f44336; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
    <script>
        function clearLog() {
            if(confirm("Очистить лог?")) {
                window.location.href="?clear=1";
            }
        }
        setInterval(function() {
            location.reload();
        }, 3000);
    </script>
</head>
<body>
    <div class="container">
        <h1>🔍 Простой мониторинг кнопки</h1>
        
        <div class="instruction">
            <h3>📋 Инструкция:</h3>
            <ol>
                <li>Нажмите "Очистить лог" ниже</li>
                <li>Откройте бота в Telegram</li>
                <li>Идите: Админка → 🔐 Управление уровнями доступа</li>
                <li>Нажмите "👥 Управление пользователями"</li>
                <li>Подождите 5 секунд</li>
                <li>Проверьте результат здесь</li>
            </ol>
        </div>
        
        <div style="text-align: center;">
            <button class="button clear-btn" onclick="clearLog()">🗑️ Очистить лог</button>
            <button class="button" onclick="location.reload();">🔄 Обновить</button>
        </div>
        <div class="log-box">
            <h3>📊 Результат мониторинга:</h3><p style="color: orange;">⚠️ Лог пустой - callback'и не поступают</p>        </div>
    </div>
</body>
</html>