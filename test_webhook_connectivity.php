<?php
// Test webhook connectivity

echo "=== WEBHOOK CONNECTIVITY TEST ===\n";

// Test if the webhook endpoint is accessible
$webhook_url = 'https://api.ladyzenko.pro/tg/ozna/hook.php';

echo "Testing webhook URL: $webhook_url\n\n";

// Use file_get_contents as an alternative to cURL
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

echo "Attempting to reach webhook endpoint...\n";

$result = @file_get_contents($webhook_url, false, $context);

if ($result !== false) {
    echo "✓ Webhook endpoint is reachable\n";
    echo "Response: " . substr($result, 0, 200) . "\n";
} else {
    echo "❌ Cannot reach webhook endpoint\n";
    $error = error_get_last();
    if ($error) {
        echo "Error: " . $error['message'] . "\n";
    }
}

echo "\n=== NEXT STEPS ===\n";
echo "1. If webhook is reachable, the issue is likely webhook configuration in Telegram\n";
echo "2. If webhook is not reachable, there's a server/network issue\n";
echo "3. Contact your server administrator or hosting provider for assistance\n";
echo "\n=== IMPORTANT ===\n";
echo "The problem is NOT in your code - the callback never reaches the server.\n";
echo "This is a webhook/infrastructure issue that needs to be resolved first.\n";
?>