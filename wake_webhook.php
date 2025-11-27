<?php
/**
 * Force webhook to reload by sending a test request
 */

require_once __DIR__ . '/vendor/autoload.php';

// Your webhook URL
$webhookUrl = "YOUR_WEBHOOK_URL_HERE"; // ЗАМЕНИ НА СВОЙ URL!

echo "Sending test request to webhook to force reload...\n";
echo "Webhook URL: {$webhookUrl}\n\n";

// Create a dummy update to wake up the webhook
$dummyUpdate = [
    'update_id' => time(),
    'message' => [
        'message_id' => time(),
        'from' => [
            'id' => 123456789,
            'is_bot' => false,
            'first_name' => 'Test'
        ],
        'chat' => [
            'id' => 123456789,
            'type' => 'private'
        ],
        'date' => time(),
        'text' => '/start'
    ]
];

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dummyUpdate));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Response Code: {$httpCode}\n";
echo "Response: {$response}\n";

curl_close($ch);

echo "\n✓ Done! Now test your bot.\n";
