<?php
// Simple webhook test - just log that we received something
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/telegram_bot_error.log');

$timestamp = date('Y-m-d H:i:s');
error_log("========================================");
error_log("WEBHOOK ALIVE TEST at {$timestamp}");
error_log("========================================");

$input = file_get_contents('php://input');
error_log("Raw input received: " . substr($input, 0, 200));

$decoded = json_decode($input, true);
if ($decoded) {
    if (isset($decoded['message'])) {
        error_log("MESSAGE RECEIVED from user: " . ($decoded['message']['from']['id'] ?? 'unknown'));
        error_log("Message text: " . ($decoded['message']['text'] ?? 'no text'));
    }
    if (isset($decoded['callback_query'])) {
        error_log("CALLBACK RECEIVED from user: " . ($decoded['callback_query']['from']['id'] ?? 'unknown'));
        error_log("Callback data: " . ($decoded['callback_query']['data'] ?? 'no data'));
    }
}

echo "OK";
