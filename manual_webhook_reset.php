<?php
// Manual webhook management (since CLI PHP doesn't have HTTPS)
// This creates URLs that can be opened in a browser manually

$bot_token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$webhook_url = 'https://ozna-bot.ozonetechpvtltd.com/tg/ozna/hook.php';

echo "=== Manual Webhook Management ===\n\n";

echo "STEP 1: Check current webhook status\n";
echo "Open this URL in your browser:\n";
echo "https://api.telegram.org/bot{$bot_token}/getWebhookInfo\n\n";

echo "STEP 2: Delete current webhook (if needed)\n";
echo "Open this URL in your browser:\n";
echo "https://api.telegram.org/bot{$bot_token}/deleteWebhook\n\n";

echo "STEP 3: Set new webhook to fixed hook.php\n";
echo "Open this URL in your browser:\n";
echo "https://api.telegram.org/bot{$bot_token}/setWebhook?url=" . urlencode($webhook_url) . "\n\n";

echo "STEP 4: Verify the webhook was set correctly\n";
echo "Open this URL in your browser:\n";
echo "https://api.telegram.org/bot{$bot_token}/getWebhookInfo\n\n";

echo "=== Instructions ===\n";
echo "1. Open each URL above in your browser in order\n";
echo "2. Check the JSON responses\n";
echo "3. After setting the webhook, test the warranty equipment buttons\n";
echo "4. If still getting errors, the issue might be elsewhere\n";

// Let me also check if our current hook.php has any issues
echo "\n=== Current hook.php Status ===\n";
if (file_exists('hook.php')) {
    echo "✓ hook.php exists\n";
    $content = file_get_contents('hook.php');
    echo "File size: " . strlen($content) . " bytes\n";
    
    // Check for common issues
    if (strpos($content, '<?php') === 0) {
        echo "✓ Starts with PHP tag\n";
    } else {
        echo "✗ Does not start with PHP tag\n";
    }
    
    if (strpos($content, 'file_put_contents') !== false) {
        echo "⚠ Contains file_put_contents calls\n";
    } else {
        echo "✓ No file_put_contents calls\n";
    }
    
    if (strpos($content, 'РабГарантии') !== false) {
        echo "✓ Contains warranty equipment debugging\n";
    } else {
        echo "✗ No warranty equipment debugging found\n";
    }
} else {
    echo "✗ hook.php does not exist!\n";
}

echo "\n=== Next Steps ===\n";
echo "After using the browser URLs above to reset the webhook:\n";
echo "1. Test warranty equipment buttons\n";
echo "2. Run: php check_recent_logs.php\n";
echo "3. Look for the debug messages we added\n";
?>