<?php

// Check which TabelHandler file is being loaded by the autoloader
require_once __DIR__ . '/custom_autoload.php';

// Check if the TabelHandler class exists
if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler')) {
    // Use reflection to get the file path
    $reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler');
    $filePath = $reflection->getFileName();
    echo "TabelHandler class loaded from: " . $filePath . "\n";
    
    // Read the file and check the priority
    $fileContent = file_get_contents($filePath);
    if (preg_match('/public function getPriority\(\): int\s*{\s*return (\d+);/', $fileContent, $matches)) {
        echo "TabelHandler priority: " . $matches[1] . "\n";
    } else {
        echo "Could not find priority method in file\n";
    }
} else {
    echo "TabelHandler class not found\n";
}

// Also check the backup file
$backupPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.backup.php';
if (file_exists($backupPath)) {
    echo "Backup file exists: " . $backupPath . "\n";
    $backupContent = file_get_contents($backupPath);
    if (preg_match('/public function getPriority\(\): int\s*{\s*return (\d+);/', $backupContent, $matches)) {
        echo "Backup TabelHandler priority: " . $matches[1] . "\n";
    } else {
        echo "Could not find priority method in backup file\n";
    }
} else {
    echo "Backup file does not exist\n";
}