<?php

// Test BOM removal functionality
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Create a test AdminService to access the removeBOM method
class TestAdminService extends AdminService {
    public function testRemoveBOM($data) {
        return $this->removeBOM($data);
    }
    
    // Override constructor to avoid database connection for this test
    public function __construct() {
        // Empty constructor for testing
    }
}

$adminService = new TestAdminService();

// Test data with BOM
$bomData = "\xEF\xBB\xBF" . '[]';
$noBomData = '[]';

echo "Testing BOM removal:\n";
echo "Original with BOM: " . bin2hex($bomData) . "\n";
echo "After removal: " . bin2hex($adminService->testRemoveBOM($bomData)) . "\n";
echo "Original without BOM: " . bin2hex($noBomData) . "\n";
echo "After removal: " . bin2hex($adminService->testRemoveBOM($noBomData)) . "\n";

echo "\nTest completed.\n";