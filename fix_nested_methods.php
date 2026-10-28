<?php
/**
 * Fix MessageService.php by removing ALL deleteMessage definitions
 * and adding ONE correct one at the end
 */

$file = 'C:\\ozna_bot\\CustomCommands\\Services\\MessageService.php';
$content = file_get_contents($file);

// Create backup
copy($file, $file . '.broken_version');
echo "Backup created: {$file}.broken_version\n";

// Remove ALL deleteMessage method definitions (we'll add one correct one at the end)
// Pattern matches the entire deleteMessage method with varying indentation
$pattern = '/\s*\/\*\*[^\/]*\* Delete a specific message[^\/]*\*\/\s*public function deleteMessage\([^{]*\):[^{]*\{(?:[^{}]|\{[^{}]*\})*\}/s';

$count = 0;
$fixed = preg_replace_callback($pattern, function($matches) use (&$count) {
    $count++;
    error_log("Removing deleteMessage method #" . $count);
    return '';
}, $content);

echo "Removed $count deleteMessage methods\n";

// Fix syntax errors: ];  should be }
$fixed = preg_replace('/\s*\];\s*$/m', '                }', $fixed);

// Find the end of the class (last closing brace)
$classEndPos = strrpos($fixed, '}');

if ($classEndPos === false) {
    die("ERROR: Could not find end of class\n");
}

// Add ONE correct deleteMessage method before the closing brace
$deleteMessageMethod = '
    /**
     * Delete a specific message
     */
    public function deleteMessage(string $chatId, int $messageId): ServerResponse
    {
        try {
            error_log("MessageService: deleteMessage called for chatId: {$chatId}, messageId: {$messageId}");
            
            $data = [
                \'chat_id\' => $chatId,
                \'message_id\' => $messageId
            ];
            
            $result = Request::deleteMessage($data);
            
            error_log("MessageService: deleteMessage result: " . ($result->isOk() ? \'success\' : \'failed\'));
            
            if (!$result->isOk()) {
                error_log("MessageService: deleteMessage error: " . $result->getDescription());
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("MessageService: deleteMessage EXCEPTION: " . $e->getMessage());
            throw $e;
        } catch (Error $e) {
            error_log("MessageService: deleteMessage ERROR: " . $e->getMessage());
            throw $e;
        }
    }
';

// Insert the method before the final closing brace
$fixed = substr_replace($fixed, $deleteMessageMethod . "\n", $classEndPos, 0);

// Write fixed content
file_put_contents($file, $fixed);

echo "Fixed content written\n";

// Check PHP syntax
exec("php -l \"$file\" 2>&1", $output, $return);
echo implode("\n", $output) . "\n";

if ($return === 0) {
    echo "\n✓ SUCCESS: No syntax errors!\n";
    echo "File fixed successfully!\n";
} else {
    echo "\n✗ ERROR: Syntax errors found!\n";
    echo "Restoring backup...\n";
    copy($file . '.broken_version', $file);
    echo "Backup restored\n";
}
