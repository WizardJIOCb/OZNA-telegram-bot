<?php

echo "Checking and fixing bot webhook...\n\n";

// Проверка 1: Наличие необходимых файлов
echo "=== Check 1: Required Files ===\n";
$requiredFiles = [
    __DIR__ . '/hook.php',
    __DIR__ . '/config.php',
    __DIR__ . '/set-hook.py',
    __DIR__ . '/webhook_info.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✓ " . basename($file) . " found\n";
    } else {
        echo "? " . basename($file) . " not found\n";
    }
}

echo "\n";

// Проверка 2: Содержимое config.php
echo "=== Check 2: Configuration File ===\n";
$configPath = __DIR__ . '/config.php';

if (file_exists($configPath)) {
    $configContent = file_get_contents($configPath);
    
    // Проверяем наличие ключевых элементов конфигурации
    if (strpos($configContent, 'api_key') !== false) {
        echo "✓ API key found in config.php\n";
    } else {
        echo "? API key not found in config.php\n";
    }
    
    if (strpos($configContent, 'hook_url') !== false) {
        echo "✓ Hook URL found in config.php\n";
    } else {
        echo "? Hook URL not found in config.php\n";
    }
    
} else {
    echo "✗ config.php not found\n";
}

echo "\n";

// Проверка 3: Состояние webhook
echo "=== Check 3: Webhook Status ===\n";
$webhookInfoPath = __DIR__ . '/webhook_info.php';

if (file_exists($webhookInfoPath)) {
    echo "✓ webhook_info.php found\n";
    echo "  To check webhook status, run: php webhook_info.php\n";
} else {
    echo "? webhook_info.php not found\n";
}

echo "\n";

// Проверка 4: Создание скрипта для установки webhook
echo "=== Check 4: Webhook Setup Script ===\n";
$setWebhookContent = '<?php

require_once __DIR__ . \'/config.php\';

// Получаем токен бота из конфигурации
$botToken = $api_key ?? \'\';

if (empty($botToken)) {
    die("Bot token not found in config.php\n");
}

// URL webhook
$webhookUrl = $hook_url ?? \'https://yourdomain.com/hook.php\';

if (empty($webhookUrl)) {
    die("Webhook URL not found in config.php\n");
}

// Формируем URL для установки webhook
$telegramApiUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";

// Данные для отправки
$postData = [
    \'url\' => $webhookUrl
];

// Инициализируем cURL
$ch = curl_init($telegramApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

// Отправляем запрос
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Проверяем результат
if ($response === false) {
    echo "Error setting webhook: curl error\n";
} else {
    $responseData = json_decode($response, true);
    if ($responseData[\'ok\'] ?? false) {
        echo "Webhook set successfully!\n";
        echo "Webhook URL: {$webhookUrl}\n";
    } else {
        echo "Error setting webhook:\n";
        echo "HTTP Code: {$httpCode}\n";
        echo "Response: " . ($response ?: \'empty\') . "\n";
    }
}
';

$setWebhookPath = __DIR__ . '/set_webhook.php';

if (file_put_contents($setWebhookPath, $setWebhookContent)) {
    echo "✓ set_webhook.php created successfully\n";
    echo "  To set webhook, run: php set_webhook.php\n";
} else {
    echo "✗ Failed to create set_webhook.php\n";
}

echo "\n";

// Проверка 5: Создание скрипта для проверки webhook
echo "=== Check 5: Webhook Check Script ===\n";
$checkWebhookContent = '<?php

require_once __DIR__ . \'/config.php\';

// Получаем токен бота из конфигурации
$botToken = $api_key ?? \'\';

if (empty($botToken)) {
    die("Bot token not found in config.php\n");
}

// Формируем URL для получения информации о webhook
$telegramApiUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";

// Инициализируем cURL
$ch = curl_init($telegramApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Отправляем запрос
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Проверяем результат
if ($response === false) {
    echo "Error getting webhook info: curl error\n";
} else {
    $responseData = json_decode($response, true);
    if ($responseData[\'ok\'] ?? false) {
        echo "Webhook info retrieved successfully:\n";
        echo json_encode($responseData[\'result\'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "Error getting webhook info:\n";
        echo "HTTP Code: {$httpCode}\n";
        echo "Response: " . ($response ?: \'empty\') . "\n";
    }
}
';

$checkWebhookPath = __DIR__ . '/check_webhook_simple.php';

if (file_put_contents($checkWebhookPath, $checkWebhookContent)) {
    echo "✓ check_webhook_simple.php created successfully\n";
    echo "  To check webhook, run: php check_webhook_simple.php\n";
} else {
    echo "✗ Failed to create check_webhook_simple.php\n";
}

echo "\n";

// Проверка 6: Рекомендации по исправлению webhook
echo "=== Check 6: Webhook Fix Recommendations ===\n";
echo "If the webhook is not working properly, try these steps:\n\n";

echo "1. Check current webhook status:\n";
echo "   php check_webhook_simple.php\n\n";

echo "2. Set webhook if needed:\n";
echo "   php set_webhook.php\n\n";

echo "3. Verify that your server is accessible from the internet:\n";
echo "   - Make sure port 443 (HTTPS) or 80 (HTTP) is open\n";
echo "   - Check that your domain has a valid SSL certificate\n";
echo "   - Ensure your hosting provider allows webhook connections\n\n";

echo "4. Check server logs for errors:\n";
echo "   - Apache/Nginx error logs\n";
echo "   - PHP error logs\n";
echo "   - Application logs in the /logs directory\n\n";

echo "5. Restart web server if needed:\n";
echo "   sudo systemctl restart apache2\n";
echo "   or\n";
echo "   sudo systemctl restart nginx\n\n";

echo "Webhook check and fix preparation completed.\n";