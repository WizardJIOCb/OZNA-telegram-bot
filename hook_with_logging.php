<?php

// Включаем логирование
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/telegram_bot_error.log');

// Добавляем логирование входящих запросов
file_put_contents(__DIR__ . '/webhook_requests.log', date('Y-m-d H:i:s') . " - Request received\n", FILE_APPEND);

try {
    // Load composer
    require __DIR__ . '/vendor/autoload.php';
    
    // Load custome classes
    require_once __DIR__ . '/Custome/DB.php';

    $bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
    $bot_username = 'OznaWork_bot';

    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Enable MySQL
    $mysql_credentials = [
        'host'     => '82.146.42.213',
        'port'     => 3306,
        'user'     => 'WizardJIOCb',
        'password' => '7qQ#28051961',
        'database' => 'tg_bot',
    ];
    $telegram->enableMySql($mysql_credentials);

    // Commands path
    $telegram->addCommandsPaths(array(__DIR__ . '/CustomCommands'));

    // Enable admin commands
    $telegram->enableAdmins([
        230379668,  // Your ID
        5261465966  // Боря
        // 1090118386, // Близи (removed as per request)
        // 444234398   // Олег olegpysh (removed as per request)
    ]);

    // Handle telegram webhook request
    error_log("Telegram bot handling request at " . date('Y-m-d H:i:s'));
    file_put_contents(__DIR__ . '/webhook_requests.log', date('Y-m-d H:i:s') . " - Handling request\n", FILE_APPEND);
    
    // Получаем входящие данные
    $input = file_get_contents('php://input');
    file_put_contents(__DIR__ . '/webhook_requests.log', date('Y-m-d H:i:s') . " - Input: " . $input . "\n", FILE_APPEND);
    
    // Enhanced debugging for commissioning works
    error_log("=== Enhanced Debug Information ===");
    error_log("Request method: " . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown'));
    error_log("Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown'));
    error_log("Content type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Unknown'));
    error_log("Input length: " . strlen($input));
    
    // Try to parse JSON input for better debugging
    $jsonInput = json_decode($input, true);
    if ($jsonInput) {
        error_log("Parsed input: " . json_encode($jsonInput, JSON_UNESCAPED_UNICODE));
        
        // Check for callback queries specifically
        if (isset($jsonInput['callback_query'])) {
            error_log("=== Callback Query Detected ===");
            $callbackData = $jsonInput['callback_query']['data'] ?? 'No data';
            error_log("Callback data: " . $callbackData);
            
            // Check if this is related to commissioning
            if (strpos($callbackData, 'Пусконаладка') !== false || 
                strpos($callbackData, 'ПНЛ') !== false ||
                strpos($callbackData, 'Commissioning') !== false) {
                error_log("=== COMMISSIONING RELATED CALLBACK ===");
                error_log("This callback appears to be related to commissioning works");
            }
        }
    }
    
    $result = $telegram->handle();
    error_log("Telegram bot request handled successfully");
    error_log("Result class: " . get_class($result));
    error_log("Result is OK: " . ($result->isOk() ? 'YES' : 'NO'));
    if (!$result->isOk()) {
        error_log("Result error code: " . $result->getErrorCode());
        error_log("Result error description: " . $result->getDescription());
    }
    file_put_contents(__DIR__ . '/webhook_requests.log', date('Y-m-d H:i:s') . " - Request handled successfully\n", FILE_APPEND);
    
} catch (Exception $e) {
    // Log errors if needed
    error_log('Telegram Bot Error: ' . $e->getMessage());
    error_log('Telegram Bot Error Trace: ' . $e->getTraceAsString());
    file_put_contents(__DIR__ . '/webhook_requests.log', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
} catch (Error $e) {
    // Log fatal errors if needed
    error_log('Telegram Bot Fatal Error: ' . $e->getMessage());
    error_log('Telegram Bot Fatal Error Trace: ' . $e->getTraceAsString());
    file_put_contents(__DIR__ . '/webhook_requests.log', date('Y-m-d H:i:s') . " - Fatal Error: " . $e->getMessage() . "\n", FILE_APPEND);
}