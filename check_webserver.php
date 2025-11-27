<?php
// Проверка состояния веб-сервера и рекомендации

echo "=== ДИАГНОСТИКА ВЕБА-СЕРВЕРА ===\n\n";

// 1. Проверяем возможные URL для webhook
$possibleUrls = [
    'http://localhost/ozna_bot/hook.php',
    'http://127.0.0.1/ozna_bot/hook.php',
    'http://localhost:8080/ozna_bot/hook.php',
    'http://localhost:80/ozna_bot/hook.php'
];

echo "1. ПРОВЕРКА ДОСТУПНОСТИ ВЕБ-СЕРВЕРА\n";
echo "====================================\n";

foreach ($possibleUrls as $url) {
    echo "Тестируем: {$url}\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 3,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        echo "✅ СЕРВЕР ОТВЕЧАЕТ!\n";
        echo "   Ответ: " . substr($response, 0, 200) . "...\n";
        break;
    } else {
        echo "❌ Не отвечает\n";
    }
}

echo "\n";

// 2. Проверяем процессы веб-сервера
echo "2. РЕКОМЕНДАЦИИ ПО ЗАПУСКУ ВЕБ-СЕРВЕРА\n";
echo "======================================\n";

echo "Возможные причины и решения:\n\n";

echo "A) ЕСЛИ ИСПОЛЬЗУЕТЕ XAMPP:\n";
echo "   1. Откройте XAMPP Control Panel\n";
echo "   2. Убедитесь, что Apache запущен (зеленая кнопка)\n";
echo "   3. Если не запущен, нажмите 'Start' рядом с Apache\n";
echo "   4. Проверьте, что нет конфликтов портов\n\n";

echo "B) ЕСЛИ ИСПОЛЬЗУЕТЕ ДРУГОЙ ВЕБ-СЕРВЕР:\n";
echo "   1. Убедитесь, что сервер запущен\n";
echo "   2. Проверьте правильность пути к файлам\n";
echo "   3. Убедитесь, что PHP работает\n\n";

echo "C) ПРОВЕРКА ПОРТОВ:\n";
echo "   Стандартные порты: 80, 8080, 8000\n";
echo "   Проверьте, какой порт использует ваш сервер\n\n";

// 3. Проверяем webhook настройки
echo "3. ПРОВЕРКА WEBHOOK TELEGRAM\n";
echo "============================\n";

$botToken = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';

echo "Ваш бот токен: {$botToken}\n";
echo "Для проверки webhook выполните:\n";
echo "curl -X GET \"https://api.telegram.org/bot{$botToken}/getWebhookInfo\"\n\n";

echo "Если webhook не установлен, установите его:\n";
echo "curl -X POST \"https://api.telegram.org/bot{$botToken}/setWebhook\" \\\n";
echo "     -d \"url=https://ваш_домен.com/ozna_bot/hook.php\"\n\n";

// 4. Быстрое решение
echo "4. БЫСТРОЕ РЕШЕНИЕ\n";
echo "==================\n";

echo "ШАГИ ДЛЯ ВОССТАНОВЛЕНИЯ РАБОТЫ:\n\n";

echo "1. ЗАПУСТИТЕ ВЕБА-СЕРВЕР:\n";
echo "   - Если XAMPP: откройте XAMPP и запустите Apache\n";
echo "   - Если другой сервер: убедитесь, что он запущен\n\n";

echo "2. ПРОВЕРЬТЕ ДОСТУПНОСТЬ:\n";
echo "   - Откройте в браузере: http://localhost/ozna_bot/hook.php\n";
echo "   - Должна появиться пустая страница или ошибки PHP\n\n";

echo "3. ЕСЛИ СЕРВЕР НЕ ЗАПУСКАЕТСЯ:\n";
echo "   - Проверьте логи Apache/Nginx\n";
echo "   - Освободите порт 80 от других программ\n";
echo "   - Перезагрузите компьютер\n\n";

echo "4. ПОСЛЕ ЗАПУСКА СЕРВЕРА:\n";
echo "   - Попробуйте снова нажать кнопку ШМР в боте\n";
echo "   - Запустите monitor_logs.php для проверки\n\n";

echo "❗ ВАЖНО: Без запущенного веб-сервера бот не может обрабатывать сообщения!\n";

?>