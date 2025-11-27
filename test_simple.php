<?php

// Simple test to check if our classes can be loaded
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

echo "Testing class loading...\n";

try {
    // Try to instantiate our services
    $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    echo "DatabaseService loaded successfully\n";
    
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    echo "MessageService loaded successfully\n";
    
    $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
    echo "ProjectSelector loaded successfully\n";
    
    $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($dbService, $messageService);
    echo "TabelEntry loaded successfully\n";
    
    $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
    echo "AdminService loaded successfully\n";
    
    // Try to instantiate our handlers
    $tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler($dbService, $messageService, $projectSelector, $tabelEntry, $adminService);
    echo "TabelHandler loaded successfully\n";
    
    $profileHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler($messageService);
    echo "ProfileHandler loaded successfully\n";
    
    $helpHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler($messageService);
    echo "HelpHandler loaded successfully\n";
    
    $functionalDirectionHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler($messageService);
    echo "FunctionalDirectionHandler loaded successfully\n";
    
    $accessLevelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler($adminService, $messageService);
    echo "AccessLevelHandler loaded successfully\n";
    
    $supervisorHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler($messageService, $adminService);
    echo "SupervisorHandler loaded successfully\n";
    
    $adminHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler($adminService, $messageService);
    echo "AdminHandler loaded successfully\n";
    
    // Try to instantiate our factory
    $callbackHandlerFactory = new \Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory();
    echo "CallbackHandlerFactory loaded successfully\n";
    
    echo "All classes loaded successfully!\n";
    echo "The bot should now be working.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}