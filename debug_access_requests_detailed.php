<?php

// Detailed debug script for access requests
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== Detailed Access Requests Debug ===\n";

// Check file content first
$filePath = __DIR__ . '/data/access_requests.json';
echo "1. Checking file content:\n";
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    echo "   File exists: yes\n";
    echo "   File size: " . filesize($filePath) . " bytes\n";
    echo "   Raw content: " . var_export($content, true) . "\n";
    echo "   Content length: " . strlen($content) . "\n";
    echo "   Hex content: " . bin2hex($content) . "\n";
    
    // Try to decode JSON
    $decoded = json_decode($content, true);
    echo "   JSON decode result: " . (json_last_error() === JSON_ERROR_NONE ? 'success' : 'failed') . "\n";
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "   JSON error: " . json_last_error_msg() . "\n";
    } else {
        echo "   Decoded data: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
    }
    
    // Check for BOM
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        echo "   BOM detected and will be removed\n";
        $contentWithoutBOM = substr($content, 3);
        echo "   Content after BOM removal: " . var_export($contentWithoutBOM, true) . "\n";
        $decodedWithoutBOM = json_decode($contentWithoutBOM, true);
        echo "   JSON decode after BOM removal: " . (json_last_error() === JSON_ERROR_NONE ? 'success' : 'failed') . "\n";
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "   Decoded data after BOM removal: " . json_encode($decodedWithoutBOM, JSON_PRETTY_PRINT) . "\n";
        }
    }
} else {
    echo "   File exists: no\n";
}

echo "\n2. Testing AdminService:\n";
// Initialize services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Test removeBOM method
echo "   Testing removeBOM method:\n";
$testWithBOM = "\xEF\xBB\xBF" . '[]';
$testWithoutBOM = '[]';
echo "   Test with BOM: " . var_export($testWithBOM, true) . "\n";
echo "   After removeBOM: " . var_export($adminService->removeBOM($testWithBOM), true) . "\n";
echo "   Test without BOM: " . var_export($testWithoutBOM, true) . "\n";
echo "   After removeBOM: " . var_export($adminService->removeBOM($testWithoutBOM), true) . "\n";

// Get access requests
echo "\n3. Getting access requests from AdminService:\n";
$requests = $adminService->getAllAccessRequests();
echo "   Request count: " . count($requests) . "\n";
echo "   Requests data: " . json_encode($requests, JSON_PRETTY_PRINT) . "\n";

echo "\n=== Debug completed ===\n";