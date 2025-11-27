<?php

require_once __DIR__ . '/custom_autoload.php';

// Check which TabelHandler class is being loaded
if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler')) {
    $reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler');
    echo "TabelHandler class file: " . $reflection->getFileName() . "\n";
    
    // Try to read the file to check for getPriority method
    $fileContent = file_get_contents($reflection->getFileName());
    if (preg_match('/public function getPriority\(\): int\s*{\s*return (\d+);/', $fileContent, $matches)) {
        echo "TabelHandler priority from file: " . $matches[1] . "\n";
    } else {
        echo "Could not find priority in file\n";
    }
} else {
    echo "TabelHandler class not found\n";
}