<?php

/**
 * Clear all bot commands using direct API call
 */

// Load configuration
$config = require __DIR__ . '/config.php';
$api_key = $config['api_key'];

// Define only the commands we want to keep
$commands = [
    ['command' => 'start', 'description' => 'Запустить бота 🚀']
];

// Make API call to Telegram
$url = "https://api.telegram.org/bot{$api_key}/setMyCommands";
$data = ['commands' => json_encode($commands)];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: {$error}\n";
    exit(1);
}

$result = json_decode($response, true);

if ($result && $result['ok']) {
    echo "✅ Bot commands updated successfully!\n\n";
    echo "Active commands:\n";
    echo "  /start - Запустить бота 🚀\n\n";
    echo "All other commands have been removed from Telegram.\n";
    echo "\nNote: It may take a few minutes for the changes to take effect.\n";
} else {
    echo "❌ Failed to update commands\n";
    echo "Response: " . print_r($result, true) . "\n";
}
