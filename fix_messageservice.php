<?php
/**
 * Script to fix MessageService.php by removing nested deleteMessage methods
 */

$file = 'c:\ozna_bot\CustomCommands\Services\MessageService.php';
$content = file_get_contents($file);

// Pattern to find nested deleteMessage methods (those with extra indentation)
$pattern = '/\s{4,20}\/\*\*\s*\n\s{4,20}\* Delete a specific message\s*\n\s{4,20}\*\/\s*\n\s{4,20}public function deleteMessage\(string \$chatId, int \$messageId\): ServerResponse\s*\n\s{4,20}\{\s*\n.*?\s{4,20}\}\s*\n(?:\s{4,20}public function deleteMessage\(string \$chatId, int \$messageId\): ServerResponse\s*\n\s{4,20}\{\s*\n.*?\s{4,20}\}\s*\n)?/s';

// Remove all nested deleteMessage methods
$fixed = preg_replace($pattern, '', $content);

// Also fix syntax error ];  should be }
$fixed = str_replace('                ];', '                }', $fixed);
$fixed = str_replace('            ];', '            }', $fixed);

// Save backup
copy($file, $file . '.before_fix');

// Write fixed content
file_put_contents($file, $fixed);

echo "Fixed MessageService.php\n";
echo "Backup saved to: {$file}.before_fix\n";

// Check PHP syntax
exec("php -l \"$file\"", $output, $return);
echo implode("\n", $output) . "\n";

if ($return === 0) {
    echo "SUCCESS: No syntax errors!\n";
} else {
    echo "ERROR: Syntax errors found! Restoring backup...\n";
    copy($file . '.before_fix', $file);
}
