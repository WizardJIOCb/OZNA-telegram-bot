<?php
// Script to fix the syntax error in TabelHandler.php

$filename = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
$content = file_get_contents($filename);

// Remove the extraneous code after the method
$pattern = '/(private function handleRKCommentsEliminationMenu\(string \$chatId, \?Conversation \$conversation = null\): ServerResponse\s*\{[^}]+\}\s*)\s*\$keyboard\s*;\s*\)\s*;\s*\}/s';
$replacement = '$1';

$content = preg_replace($pattern, $replacement, $content);

file_put_contents($filename, $content);

echo "File fixed successfully.\n";
?>