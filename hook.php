<?php

// Включаем логирование
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/telegram_bot_error.log');

// Устанавливаем кодировку UTF-8
mb_internal_encoding('UTF-8');
@iconv_set_encoding('internal_encoding', 'UTF-8');
@iconv_set_encoding('input_encoding', 'UTF-8');
@iconv_set_encoding('output_encoding', 'UTF-8');

// Устанавливаем заголовки для UTF-8
header('Content-Type: text/html; charset=utf-8');

// Try to load PDO MySQL extension dynamically
if (!extension_loaded('pdo_mysql')) {
    // Try different possible paths for the extension
    $possible_paths = [
        'C:\\php\\ext\\php_pdo_mysql.dll',
        'C:\\php\\php_pdo_mysql.dll',
        ini_get('extension_dir') . DIRECTORY_SEPARATOR . 'php_pdo_mysql.dll'
    ];
    
    $loaded = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            if (dl(basename($path))) {
                $loaded = true;
                error_log("Successfully loaded PDO MySQL extension from: " . $path);
                break;
            } else {
                error_log("Failed to load PDO MySQL extension from: " . $path);
            }
        } else {
            error_log("PDO MySQL extension file not found at: " . $path);
        }
    }
    
    if (!$loaded) {
        error_log("Could not load PDO MySQL extension from any of the possible paths");
    }
}

// Check if PDO MySQL is loaded
if (!extension_loaded('pdo_mysql')) {
    error_log("PDO MySQL extension is not loaded - this will cause database connection issues");
    // Don't die here, let the application handle the error gracefully
}

try {
    // CRITICAL: Load our custom autoloader FIRST (before vendor/autoload)
    // This ensures our GenericmessageCommand is loaded instead of vendor version
    require_once __DIR__ . '/custom_autoload.php';
    error_log("Custom autoloader registered BEFORE vendor autoloader");
    
    // Load composer
    require __DIR__ . '/vendor/autoload.php';
    
    // Load custom classes
    require_once __DIR__ . '/Custome/DB.php';

    $bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
    $bot_username = 'OznaWork_bot';

    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Enable MySQL only if PDO MySQL is available
    if (extension_loaded('pdo_mysql')) {
        $mysql_credentials = [
            'host'     => '82.146.42.213',
            'port'     => 3306,
            'user'     => 'WizardJIOCb',
            'password' => '7qQ#28051961',
            'database' => 'tg_bot',
        ];
        $telegram->enableMySql($mysql_credentials);
    } else {
        error_log("Skipping MySQL initialization due to missing PDO MySQL extension");
    }

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
    
    // LOG ALL INCOMING REQUESTS
    $input = file_get_contents('php://input');
    error_log("========== WEBHOOK INPUT START ==========");
    error_log("Raw input length: " . strlen($input));
    error_log("Raw input: " . substr($input, 0, 500)); // First 500 chars
    error_log("========== WEBHOOK INPUT END ==========");
    
    // Handle telegram webhook request
    error_log("Processing webhook request at " . date('Y-m-d H:i:s'));
    
    // Get input and log callback queries for warranty equipment debugging
    $jsonInput = json_decode($input, true);
    if ($jsonInput && isset($jsonInput['callback_query'])) {
        $callbackData = $jsonInput['callback_query']['data'] ?? '';
        $userId = $jsonInput['callback_query']['from']['id'] ?? 'unknown';
        
        // Log ALL callback queries
        error_log("=== CALLBACK QUERY RECEIVED ===");
        error_log("User ID: {$userId}");
        error_log("Callback Data: '{$callbackData}'");
        error_log("Callback Length: " . strlen($callbackData));
        
        // Special logging for access level callbacks
        if (strpos($callbackData, 'acc_lvl') !== false) {
            error_log("🔥 ACCESS LEVEL CALLBACK DETECTED!");
            error_log("🔥 Callback: '{$callbackData}'");
            
            // Запись в отдельный файл для отладки
            $debugLog = __DIR__ . '/access_level_callback_debug.log';
            $timestamp = date('Y-m-d H:i:s');
            $debugMessage = "[{$timestamp}] User {$userId} pressed button: {$callbackData}\n";
            file_put_contents($debugLog, $debugMessage, FILE_APPEND);
        }
        
        if (strpos($callbackData, 'РабГарантии') !== false) {
            error_log("WARRANTY EQUIPMENT CALLBACK: " . $callbackData);
        }
    }
    
    // LOG TEXT MESSAGES
    if ($jsonInput && isset($jsonInput['message']) && isset($jsonInput['message']['text'])) {
        $messageText = $jsonInput['message']['text'];
        $userId = $jsonInput['message']['from']['id'] ?? 'unknown';
        error_log("=== TEXT MESSAGE RECEIVED ===");
        error_log("User ID: {$userId}");
        error_log("Message Text: '{$messageText}'");
        error_log("Message Length: " . strlen($messageText));
    }
    
    $telegram->handle();
    error_log("Webhook request processed successfully");
    
} catch (Exception $e) {
    // Log errors if needed
    error_log('Telegram Bot Error: ' . $e->getMessage());
    error_log('Telegram Bot Error Trace: ' . $e->getTraceAsString());
} catch (Error $e) {
    // Log fatal errors if needed
    error_log('Telegram Bot Fatal Error: ' . $e->getMessage());
    error_log('Telegram Bot Fatal Error Trace: ' . $e->getTraceAsString());
}