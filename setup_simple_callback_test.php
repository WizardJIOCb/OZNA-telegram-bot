<?php
// Простой тест для проверки нажатия кнопки
echo "Создаю файлы для тестирования...\n";

// 1. Создаем простой лог callback'ов
$callbackLogFile = __DIR__ . '/simple_callback_test.log';
file_put_contents($callbackLogFile, '');
echo "✅ Создан файл лога: {$callbackLogFile}\n";

// 2. Создаем простую HTML страницу для мониторинга
$monitorHtml = '<!DOCTYPE html>
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
        </div>';

$monitorHtml .= '
        <div class="log-box">
            <h3>📊 Результат мониторинга:</h3>';

// Handle clear request
if (isset($_GET['clear'])) {
    file_put_contents($callbackLogFile, '');
    $monitorHtml .= '<p style="color: green;">✅ Лог очищен</p>';
}

// Read and display log
if (file_exists($callbackLogFile)) {
    $logContent = file_get_contents($callbackLogFile);
    if (empty(trim($logContent))) {
        $monitorHtml .= '<p style="color: orange;">⚠️ Лог пустой - callback\'и не поступают</p>';
    } else {
        $monitorHtml .= '<p style="color: green;">✅ Обнаружены callback\'и:</p>';
        $monitorHtml .= '<pre>' . htmlspecialchars($logContent) . '</pre>';
    }
} else {
    $monitorHtml .= '<p style="color: red;">❌ Файл лога не найден</p>';
}

$monitorHtml .= '        </div>
    </div>
</body>
</html>';

file_put_contents(__DIR__ . '/simple_callback_monitor.php', '<?php' . "\n" . $monitorHtml);
echo "✅ Создана страница мониторинга: simple_callback_monitor.php\n";

echo "\n=== СЛЕДУЮЩИЕ ШАГИ ===\n";
echo "1. Откройте в браузере: http://ваш-сервер/simple_callback_monitor.php\n";
echo "2. Следуйте инструкциям на странице\n";
echo "3. Если лог остается пустым - callback не доходит до сервера\n";
echo "4. Если появляются записи - callback доходит, проблема в обработке\n";
?>