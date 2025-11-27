<?php
// Simple syntax test for TabelHandler.php

// Try to parse the file
$file = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
$output = shell_exec("php -l " . escapeshellarg($file));

if (strpos($output, 'No syntax errors detected') !== false) {
    echo "SUCCESS: TabelHandler.php has no syntax errors\n";
} else {
    echo "ERROR: TabelHandler.php has syntax errors:\n";
    echo $output;
}