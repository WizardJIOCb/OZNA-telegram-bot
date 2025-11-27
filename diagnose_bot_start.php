<?php
/**
 * Diagnostic Script for /start Button Issue
 * Checks all critical bot components
 */

echo "=== BOT START BUTTON DIAGNOSTIC ===\n\n";

// 1. Check PHP extensions
echo "1. PHP Extensions:\n";
echo "   - OpenSSL: " . (extension_loaded('openssl') ? "✅ Loaded" : "❌ NOT LOADED") . "\n";
echo "   - cURL: " . (extension_loaded('curl') ? "✅ Loaded" : "❌ NOT LOADED") . "\n";
echo "   - PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✅ Loaded" : "❌ NOT LOADED") . "\n";
echo "   - mbstring: " . (extension_loaded('mbstring') ? "✅ Loaded" : "❌ NOT LOADED") . "\n\n";

// 2. Check if we can make HTTPS requests
echo "2. HTTPS Connectivity:\n";
if (extension_loaded('curl')) {
    $ch = curl_init('https://api.telegram.org/bot7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ/getMe');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "   ✅ Can connect to Telegram API\n";
        echo "   Response: " . substr($response, 0, 100) . "...\n\n";
    } else {
        echo "   ❌ Cannot connect to Telegram API (HTTP $httpCode)\n\n";
    }
} else {
    echo "   ❌ cURL not available\n\n";
}

// 3. Check webhook status
echo "3. Webhook Status:\n";
if (extension_loaded('curl')) {
    $ch = curl_init('https://api.telegram.org/bot7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ/getWebhookInfo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if ($data && isset($data['result'])) {
        $info = $data['result'];
        echo "   URL: " . ($info['url'] ?? 'NOT SET') . "\n";
        echo "   Pending Updates: " . ($info['pending_update_count'] ?? 0) . "\n";
        echo "   Last Error: " . ($info['last_error_message'] ?? 'None') . "\n";
        echo "   Last Error Date: " . (isset($info['last_error_date']) ? date('Y-m-d H:i:s', $info['last_error_date']) : 'N/A') . "\n\n";
    } else {
        echo "   ❌ Cannot get webhook info\n\n";
    }
}

// 4. Check hook.php file
echo "4. Hook File:\n";
$hookFile = __DIR__ . '/hook.php';
if (file_exists($hookFile)) {
    echo "   ✅ hook.php exists\n";
    echo "   Size: " . filesize($hookFile) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($hookFile)) . "\n\n";
} else {
    echo "   ❌ hook.php NOT FOUND\n\n";
}

// 5. Check StartCommand
echo "5. StartCommand:\n";
$startCommand = __DIR__ . '/CustomCommands/StartCommand.php';
if (file_exists($startCommand)) {
    echo "   ✅ StartCommand.php exists\n";
    echo "   Size: " . filesize($startCommand) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($startCommand)) . "\n\n";
} else {
    echo "   ❌ StartCommand.php NOT FOUND\n\n";
}

// 6. Check recent error logs
echo "6. Recent Error Logs (last 10 lines):\n";
$logFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -10);
    foreach ($lastLines as $line) {
        echo "   " . trim($line) . "\n";
    }
} else {
    echo "   ❌ No error log found\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
echo "\nRECOMMENDATIONS:\n";
if (!extension_loaded('openssl')) {
    echo "⚠️  CRITICAL: Enable OpenSSL in php.ini (extension=php_openssl.dll)\n";
}
if (!extension_loaded('curl')) {
    echo "⚠️  CRITICAL: Enable cURL in php.ini (extension=php_curl.dll)\n";
}
echo "\n";
