<?php
/**
 * Тест - получает ли webhook запросы от Telegram
 */

// Создать тестовый лог файл
$logFile = __DIR__ . '/webhook_test.log';
$timestamp = date('Y-m-d H:i:s');

// Логировать ВСЕ входящие данные
$input = file_get_contents('php://input');
$serverData = json_encode($_SERVER, JSON_UNESCAPED_UNICODE);

$logMessage = "[$timestamp] WEBHOOK TEST\n";
$logMessage .= "Input length: " . strlen($input) . "\n";
$logMessage .= "Input: " . substr($input, 0, 500) . "\n";
$logMessage .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$logMessage .= "Remote: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
$logMessage .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "\n";
$logMessage .= "---\n\n";

file_put_contents($logFile, $logMessage, FILE_APPEND);

// Вернуть OK для Telegram
http_response_code(200);
echo "OK";
