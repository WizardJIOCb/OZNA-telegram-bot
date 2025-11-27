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

try {
    // Load composer
    require __DIR__ . '/vendor/autoload.php';
    
    // Load our custom autoloader for services and handlers
    require_once __DIR__ . '/custom_autoload.php';
    
    // Load encoding helper
    require_once __DIR__ . '/encoding_helper.php';

    $bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
    $bot_username = 'OznaWork_bot';

    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Commands path
    $telegram->addCommandsPaths(array(__DIR__ . '/CustomCommands'));

    // Enable admin commands
    $telegram->enableAdmins([
        230379668,  // Your ID
        5261465966  // Боря
    ]);

    // Handle telegram webhook request
    error_log("Telegram bot handling request at " . date('Y-m-d H:i:s'));
    $telegram->handle();
    error_log("Telegram bot request handled successfully");
    
} catch (Exception $e) {
    // Log errors if needed
    error_log('Telegram Bot Error: ' . $e->getMessage());
    error_log('Telegram Bot Error Trace: ' . $e->getTraceAsString());
} catch (Error $e) {
    // Log fatal errors if needed
    error_log('Telegram Bot Fatal Error: ' . $e->getMessage());
    error_log('Telegram Bot Fatal Error Trace: ' . $e->getTraceAsString());
}