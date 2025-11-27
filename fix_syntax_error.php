<?php
// Script to fix the syntax error in TabelHandler.php

$filename = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
$content = file_get_contents($filename);

// Find the handleRKCommentsEliminationMenu method and fix it
$lines = explode("\n", $content);
$fixed_lines = [];
$in_method = false;
$method_end_found = false;

foreach ($lines as $line_num => $line) {
    // Check if we're at the end of the method
    if ($in_method && trim($line) === '        );' && isset($lines[$line_num + 1]) && trim($lines[$line_num + 1]) === '    }') {
        $in_method = false;
        $method_end_found = true;
        $fixed_lines[] = $line; // Keep the return statement
        $fixed_lines[] = $lines[$line_num + 1]; // Keep the closing brace
        continue;
    }
    
    // Skip the extraneous code
    if ($method_end_found && trim($line) === '            $keyboard') {
        $method_end_found = false;
        continue;
    }
    
    // Skip the extraneous closing brace
    if ($method_end_found && trim($line) === '        );' && isset($lines[$line_num - 1]) && trim($lines[$line_num - 1]) === '            $keyboard') {
        $method_end_found = false;
        continue;
    }
    
    // Check if we're entering the method
    if (strpos($line, 'private function handleRKCommentsEliminationMenu') !== false) {
        $in_method = true;
    }
    
    $fixed_lines[] = $line;
}

file_put_contents($filename, implode("\n", $fixed_lines));

echo "Syntax error fixed successfully.\n";
?>