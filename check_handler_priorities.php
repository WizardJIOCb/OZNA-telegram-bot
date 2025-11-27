<?php
// Script to check handler priorities

echo "Handler Priority Check\n";
echo "====================\n";

// Read the TabelHandler file and extract priority
$tabelHandlerContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');
if (preg_match('/public function getPriority\(\): int\s*\{\s*return (\d+);/', $tabelHandlerContent, $matches)) {
    echo "TabelHandler priority: " . $matches[1] . "\n";
} else {
    echo "Could not find TabelHandler priority\n";
}

// Read other handler files and extract priorities
$handlers = [
    'AdminHandler' => 'c:\ozna_bot\CustomCommands\Handlers\AdminHandler.php',
    'AccessLevelHandler' => 'c:\ozna_bot\CustomCommands\Handlers\AccessLevelHandler.php',
    'SupervisorHandler' => 'c:\ozna_bot\CustomCommands\Handlers\SupervisorHandler.php',
    'ProfileHandler' => 'c:\ozna_bot\CustomCommands\Handlers\ProfileHandler.php',
    'FunctionalDirectionHandler' => 'c:\ozna_bot\CustomCommands\Handlers\FunctionalDirectionHandler.php',
    'HelpHandler' => 'c:\ozna_bot\CustomCommands\Handlers\HelpHandler.php',
    'MainMenuHandler' => 'c:\ozna_bot\CustomCommands\Handlers\MainMenuHandler.php'
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

echo "\nPriority Analysis:\n";
echo "==================\n";
echo "TabelHandler should have higher priority than AdminHandler, AccessLevelHandler, and SupervisorHandler\n";
echo "to ensure submenu buttons work correctly.\n";