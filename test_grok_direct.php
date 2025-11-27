<?php
/**
 * Direct test of Grok API with minimal dependencies
 * Run on production server: php test_grok_direct.php
 */

echo "=== DIRECT GROK API TEST ===\n\n";

// API Configuration
$apiKey = 'xai-cHb9kO337kfWzpTHAPV7uHSPcQwl9djhAJhvSD5rvd9zdl9KGPIoPdNxkrH6o5hCB0pAgKhwIau2OS5s';
$apiUrl = 'https://api.x.ai/v1/chat/completions';
$model = 'grok-beta';

echo "1. Configuration:\n";
echo "   API URL: {$apiUrl}\n";
echo "   Model: {$model}\n";
echo "   API Key: " . substr($apiKey, 0, 10) . "..." . substr($apiKey, -10) . "\n\n";

// Check curl
if (!function_exists('curl_init')) {
    die("ERROR: curl extension not available!\n");
}
echo "2. CURL extension: Available ✓\n\n";

// Prepare simple test request
$messages = [
    [
        'role' => 'system',
        'content' => 'You are a helpful assistant. Answer in Russian.'
    ],
    [
        'role' => 'user',
        'content' => 'Привет! Скажи одно короткое приветствие на русском.'
    ]
];

$data = [
    'messages' => $messages,
    'model' => $model,
    'stream' => false,
    'temperature' => 0.7,
    'max_tokens' => 100
];

echo "3. Preparing request...\n";
echo "   Payload: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";

// Make request
echo "4. Sending request to Grok API...\n";
$startTime = microtime(true);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlErrno = curl_errno($ch);
curl_close($ch);

$duration = round(microtime(true) - $startTime, 2);

echo "5. Response received in {$duration} seconds\n\n";

// Check for CURL errors
if ($curlErrno !== 0) {
    echo "ERROR: CURL Error #{$curlErrno}: {$curlError}\n";
    exit(1);
}

echo "6. HTTP Status Code: {$httpCode}\n";

if ($httpCode !== 200) {
    echo "ERROR: Non-200 response!\n";
    echo "Response body: {$response}\n";
    exit(1);
}

echo "7. HTTP Status: OK ✓\n\n";

// Parse JSON
$decoded = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "ERROR: JSON decode failed: " . json_last_error_msg() . "\n";
    echo "Raw response: {$response}\n";
    exit(1);
}

echo "8. JSON parsed successfully ✓\n\n";

// Display response structure
echo "9. Response structure:\n";
echo "   Keys: " . implode(', ', array_keys($decoded)) . "\n";

if (isset($decoded['choices'][0]['message']['content'])) {
    $answer = $decoded['choices'][0]['message']['content'];
    echo "\n10. SUCCESS! ✓\n";
    echo "    Answer: {$answer}\n";
    
    if (isset($decoded['usage'])) {
        echo "\n11. Token usage:\n";
        echo "    Prompt tokens: " . ($decoded['usage']['prompt_tokens'] ?? 'N/A') . "\n";
        echo "    Completion tokens: " . ($decoded['usage']['completion_tokens'] ?? 'N/A') . "\n";
        echo "    Total tokens: " . ($decoded['usage']['total_tokens'] ?? 'N/A') . "\n";
    }
} else {
    echo "\nERROR: Expected response structure not found!\n";
    echo "Full response:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    exit(1);
}

echo "\n=== TEST COMPLETE ===\n";
echo "Grok API is working correctly! ✓\n";
