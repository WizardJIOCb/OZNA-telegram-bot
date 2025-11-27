<?php

// Script to check handler priorities
echo "Handler Priority Check\n";
echo "====================\n";

$handlers = [
    'MainMenuHandler' => 'c:\ozna_bot\CustomCommands\Handlers\MainMenuHandler.php',
    'ProjectManagementHandler' => 'c:\ozna_bot\CustomCommands\Handlers\ProjectManagementHandler.php',
    'AccessLevelHandler' => 'c:\ozna_bot\CustomCommands\Handlers\AccessLevelHandler.php',
    'AdminHandler' => 'c:\ozna_bot\CustomCommands\Handlers\AdminHandler.php'
];

foreach ($handlers as $handlerName => $filePath) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        if (preg_match('/public function getPriority\(\): int\s*\{\s*return (\d+);/', $content, $matches)) {
            echo $handlerName . " priority: " . $matches[1] . "\n";
        } else {
            echo "Could not find " . $handlerName . " priority\n";
        }
    } else {
        echo $handlerName . " file not found\n";
    }
}