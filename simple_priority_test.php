<?php

require_once __DIR__ . '/custom_autoload.php';

// Check if the TabelHandler class exists and what its priority is
if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler')) {
    // Use reflection to check the getPriority method without instantiating
    $reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler');
    $method = $reflection->getMethod('getPriority');
    
    // Create a new instance
    // We need to provide mock dependencies
    $mockDatabaseService = new class {
        // Minimal implementation to satisfy the constructor
    };
    
    $mockMessageService = new class {
        public function createWorkTypeKeyboard() { return []; }
        public function sendMessage($chatId, $text, $keyboard = null) { 
            return new class { public function isOk() { return true; } }; 
        }
    };
    
    $mockProjectSelector = new class {
        public function isDateSelection($category) { return false; }
        public function isProjectSelection($category) { return false; }
    };
    
    $mockTabelEntry = new class {
        public function getConfirmationMessage($activityType, $projectCode) { return ''; }
    };
    
    $mockAdminService = new class {
        public function getUserAccessLevel($userId) { return 1; }
    };
    
    try {
        $handler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
            $mockDatabaseService,
            $mockMessageService,
            $mockProjectSelector,
            $mockTabelEntry,
            $mockAdminService
        );
        
        echo "TabelHandler priority: " . $handler->getPriority() . "\n";
        echo "TabelHandler class file: " . $reflection->getFileName() . "\n";
    } catch (Exception $e) {
        echo "Error creating TabelHandler: " . $e->getMessage() . "\n";
        echo "TabelHandler class file: " . $reflection->getFileName() . "\n";
        
        // Try to read the file directly to check the priority
        $fileContent = file_get_contents($reflection->getFileName());
        if (preg_match('/public function getPriority\(\): int\s*{\s*return (\d+);/', $fileContent, $matches)) {
            echo "TabelHandler priority from file: " . $matches[1] . "\n";
        } else {
            echo "Could not find priority in file\n";
        }
    }
} else {
    echo "TabelHandler class not found\n";
}