<?php
// Script to fix the TabelHandler.php file by removing problematic pattern matching rules

$file_path = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
$content = file_get_contents($file_path);

// Split content into lines
$lines = explode("\n", $content);

// Find and remove the problematic lines
$fixed_lines = [];
$skip_lines = false;

foreach ($lines as $line) {
    // Check if we're at the start of the problematic section
    if (strpos($line, '// Handle supervision works milestone confirmations') !== false) {
        $skip_lines = true;
        continue; // Skip this line
    }
    
    // Check if we're at the end of the problematic section
    if (strpos($line, '// Handle short callback format for supervision works milestone confirmations') !== false) {
        $skip_lines = false;
        // Add this line back
        $fixed_lines[] = $line;
        continue;
    }
    
    // Skip lines if we're in the problematic section
    if ($skip_lines) {
        continue;
    }
    
    // Add line to fixed content
    $fixed_lines[] = $line;
}

// Join the lines back together
$fixed_content = implode("\n", $fixed_lines);

// Write the fixed content back to the file
file_put_contents($file_path, $fixed_content);

echo "File fixed successfully!\n";
?>