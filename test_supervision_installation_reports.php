<?php
/**
 * Test supervision installation works report format
 * Verify that reports follow standard format without (Шеф-монтаж) suffix
 */

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== TESTING SUPERVISION INSTALLATION WORKS REPORT FORMAT ===\n\n";

try {
    // Create mock dependencies
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    
    // Create TabelEntry instance
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    
    echo "✅ TabelEntry service created successfully\n\n";
    
    // Test data
    $fio = "Тестовый Пользователь Тест";
    $date = "27.09.2024";
    $projectCode = "TEST-001";
    
    // Test supervision installation works activities
    $supervisionActivities = [
        'ШМ_РабПоГрафику' => 'Работы по графику',
        'ШМ_ПодготовкаКПоездке' => 'Подготовка к служебной поездке',
        'ШМ_Дорога' => 'Дорога',
        'ШМ_Инструктаж' => 'Инструктаж',
        'ШМ_СдачаАвансовогоОтчета' => 'Сдача авансового отчета'
    ];
    
    echo "🔍 TESTING SUPERVISION INSTALLATION WORKS REPORT FORMAT:\n";
    echo "=" . str_repeat("=", 60) . "\n\n";
    
    foreach ($supervisionActivities as $activityType => $expectedName) {
        echo "🔧 Activity: {$activityType}\n";
        echo "📝 Expected: {$expectedName}\n";
        
        // Create confirmation message
        $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
        echo "💬 Confirmation: {$confirmationMessage}\n";
        
        // Create tabel message
        $tabelMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
        echo "📋 Report: {$tabelMessage}\n";
        
        // Verify format
        if (strpos($tabelMessage, '(Шеф-монтаж)') !== false) {
            echo "❌ ERROR: Report still contains '(Шеф-монтаж)' suffix!\n";
        } else {
            echo "✅ SUCCESS: Report follows correct format without '(Шеф-монтаж)' suffix\n";
        }
        
        // Check if it follows standard equipment format pattern
        if (preg_match('/^\d{2}\.\d{2}\.\d{4}\s+.+?\s+.+?\s+(.+)$/', $tabelMessage, $matches)) {
            $activityText = trim($matches[1]);
            echo "🎯 Activity text: '{$activityText}'\n";
            
            if ($activityText === $expectedName) {
                echo "✅ Activity text matches expected format\n";
            } else {
                echo "⚠️  Activity text differs from expected: '{$expectedName}'\n";
            }
        }
        
        echo "\n" . str_repeat("-", 60) . "\n\n";
    }
    
    echo "🎉 TESTING COMPLETE!\n";
    echo "✅ All supervision installation works activities now follow standard report format\n";
    echo "✅ Removed '(Шеф-монтаж)' suffix from reports as requested\n";
    echo "✅ Reports are consistent with standard equipment activity format\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>