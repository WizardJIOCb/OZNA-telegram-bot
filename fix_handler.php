<?php
// Script to fix the TabelHandler.php file by removing problematic pattern matching rules

$file_path = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
$content = file_get_contents($file_path);

// Remove the problematic pattern matching rules
$pattern = '/\s*\/\/ Handle supervision works milestone confirmations\s+elseif \(preg_match\(\'\/\^ШМР_\(\.\+\)_ПодтВех_yes\\\/u\', \\\$category, \\\$matches\)\) \{\s+\\\$activityName = \\\$matches\[1\];\s+\\\$activityType = "ШМР_\{\\\$activityName\}_ПодтВех";\s+error_log\("TabelHandler: handleActivityExecution mapped supervision milestone: \{\\\$activityName\} -> \{\\\$activityType\}"\);\s+\}\s+\/\/ Handle supervision works schedule confirmations\s+elseif \(preg_match\(\'\/\^ШМР_\(\.\+\)_РабГр_yes\\\/u\', \\\$category, \\\$matches\)\) \{\s+\\\$activityName = \\\$matches\[1\];\s+\\\$activityType = "ШМР_\{\\\$activityName\}_РабГр";\s+error_log\("TabelHandler: handleActivityExecution mapped supervision schedule: \{\\\$activityName\} -> \{\\\$activityType\}"\);\s+\}/s';

$content = preg_replace($pattern, '', $content);

// Write the fixed content back to the file
file_put_contents($file_path, $content);

echo "File fixed successfully!\n";
?>