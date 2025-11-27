<?php

require_once __DIR__ . '/custom_autoload.php';

// Check which TabelHandler class is loaded
if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler')) {
    $handler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
        // Mock dependencies
        new class { public function getUserAccessLevel($userId) { return 1; } },
        new class { 
            public function createWorkTypeKeyboard() { return []; }
            public function sendMessage($chatId, $text, $keyboard = null) { 
                return new class { public function isOk() { return true; } }; 
            }
        },
        new class { 
            public function isDateSelection($category) { return false; }
            public function isProjectSelection($category) { return false; }
        },
        new class { 
            public function getConfirmationMessage($activityType, $projectCode) { return ''; }
            public function addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason = '') { return true; }
            public function createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') { return ''; }
        },
        new class { public function getUserAccessLevel($userId) { return 1; } }
    );
    
    echo "TabelHandler priority: " . $handler->getPriority() . "\n";
} else {
    echo "TabelHandler class not found\n";
}