<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load our custom autoloader for services and handlers
require_once __DIR__ . '/custom_autoload.php';

// Load custom classes
require_once __DIR__ . '/Custome/DB.php';

$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    
    echo "Telegram bot object created successfully\n";
    
    // Enable MySQL
    $mysql_credentials = [
        'host'     => '82.146.42.213',
        'port'     => 3306,
        'user'     => 'WizardJIOCb',
        'password' => '7qQ#28051961',
        'database' => 'tg_bot',
    ];
    $telegram->enableMySql($mysql_credentials);
    
    echo "MySQL enabled successfully\n";
    
    // Commands path
    $telegram->addCommandsPaths(array(__DIR__ . '/CustomCommands'));
    
    echo "Commands paths added successfully\n";
    
    // Enable admin commands
    $telegram->enableAdmins([
        230379668,  // Your ID
        5261465966  // Боря
    ]);
    
    echo "Admins enabled successfully\n";
    
    echo "Telegram bot reloaded successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

?>