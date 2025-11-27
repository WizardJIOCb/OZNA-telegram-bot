<?php
// Final test to verify commissioning keyboard functionality without Метрология button

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Final Commissioning Keyboard Test ===\n\n";

$messageService = new MessageService();
$keyboard = $messageService->createCommissioningWorksKeyboardWithMobileActivities();

echo "Commissioning works keyboard created successfully!\n";
echo "Total rows in keyboard: " . count($keyboard) . "\n\n";

echo "Keyboard contents:\n";
$metrologyFound = false;
$rowNumber = 1;
foreach ($keyboard as $row) {
    echo "Row {$rowNumber}:\n";
    foreach ($row as $button) {
        $buttonText = $button['text'] ?? 'N/A';
        $callbackData = $button['callback_data'] ?? 'N/A';
        echo "  - {$buttonText} ({$callbackData})\n";
        
        if (strpos($buttonText, 'Метрология') !== false) {
            $metrologyFound = true;
        }
    }
    $rowNumber++;
}

echo "\n=== Results ===\n";
echo "Метрология button found: " . ($metrologyFound ? 'YES' : 'NO') . "\n";

if (!$metrologyFound) {
    echo "✅ SUCCESS: Commissioning keyboard is working correctly without Метрология button!\n";
    echo "   The button has been successfully removed from the UI.\n";
} else {
    echo "❌ FAILURE: Метрология button still present in keyboard.\n";
}

echo "\nNote: The commissioning works keyboard should now display all equipment types\n";
echo "      except for Метрология, which has been removed as requested.\n";
?>