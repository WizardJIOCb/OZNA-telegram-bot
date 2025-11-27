<?php
// Test TabelEntry warranty activity message generation
echo "=== TESTING TABELENTRY WARRANTY MESSAGES ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

try {
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    
    echo "✅ Successfully created TabelEntry instance\n\n";
    
    // Test warranty activity types
    $warrantyActivities = [
        'РабГарантии_ЭлектрОб' => 'Electrical Equipment',
        'РабГарантии_НасОб' => 'Pumping Equipment',
        'РабГарантии_СрИзм' => 'Measuring Instruments',
        'РабГарантии_ТрубАрм' => 'Pipeline Fittings',
        'РабГарантии_ИспМех' => 'Executive Mechanisms',
        'РабГарантии_КонтрОб' => 'Controller Equipment',
        'РабГарантии_Прочее' => 'Other Equipment'
    ];
    
    echo "Testing warranty activity confirmation messages:\n";
    echo "==============================================\n";
    
    foreach ($warrantyActivities as $activityType => $description) {
        echo "\nTesting: {$activityType} ({$description})\n";
        echo "Activity type length: " . strlen($activityType) . " characters\n";
        
        try {
            $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, 'Test Project');
            echo "✅ SUCCESS: {$confirmationMessage}\n";
        } catch (Exception $e) {
            echo "❌ ERROR: {$e->getMessage()}\n";
            echo "   This activity type is not defined in TabelEntry!\n";
        }
    }
    
    echo "\n\nTesting confirmation keyboard creation:\n";
    echo "======================================\n";
    
    foreach ($warrantyActivities as $activityType => $description) {
        $callbackData = "category_Табель_{$activityType}_yes";
        $length = strlen($callbackData);
        echo "Callback: {$callbackData} ({$length} bytes)\n";
        
        if ($length > 64) {
            echo "⚠️  WARNING: Callback exceeds Telegram's 64-byte limit!\n";
        } else {
            echo "✅ OK: Within Telegram's callback limit\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ FATAL ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}

echo "\n\nCONCLUSION:\n";
echo "===========\n";
echo "If you see any ❌ ERROR messages above, those warranty activity types\n";
echo "are not properly defined in the TabelEntry class, which is why the\n";
echo "warranty equipment buttons don't work.\n\n";
echo "All warranty activity types must be defined in both:\n";
echo "1. TabelEntry->activityTypeMappings array\n";
echo "2. TabelEntry->fullWorkTypeNames array\n";
?>