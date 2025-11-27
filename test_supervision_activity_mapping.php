<?php
/**
 * Test script to verify supervision installation works activity mapping
 */

echo "🧪 TESTING SUPERVISION INSTALLATION WORKS ACTIVITY MAPPING\n";
echo "========================================================\n\n";

// Include the TabelEntry class
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

// Create a mock DatabaseService
class MockDatabaseService {
    public function hasRecentTabelEntry($userId, $isSuperAdmin) {
        return null;
    }
    
    public function addUserTabel($userId, $message) {
        return true;
    }
}

// Create a mock MessageService
class MockMessageService {
    public function sendToHistoryChatByFunctionalDirection($message, $functionalDirection) {
        // Mock implementation
    }
}

echo "1. TESTING ACTIVITY TYPE MAPPINGS\n";
echo "=================================\n";

$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);

// Test the specific activity types
$testActivityTypes = [
    'ШМ_РабПоГрафику' => 'Expected: 📅 Работы по графику',
    'ШМ_ПодготовкаКПоездке' => 'Expected: 📋 Подготовка к служебной поездке', 
    'ШМ_Дорога' => 'Expected: 🚗 Дорога',
    'ШМ_Инструктаж' => 'Expected: 📋 Инструктаж',
    'ШМ_СдачаАвансовогоОтчета' => 'Expected: 💰 Сдача авансового отчета'
];

foreach ($testActivityTypes as $activityType => $expected) {
    echo "Testing: '{$activityType}'\n";
    echo "  {$expected}\n";
    
    // Test createTabelMessage
    $result = $tabelEntry->createTabelMessage(
        'Test User',
        '27.09.2025', 
        'ОИ837',
        $activityType
    );
    
    echo "  Actual result:\n";
    echo "  " . str_replace(["\r\n", "\n"], " ", $result) . "\n";
    
    // Check if it contains the expected automation format
    if (strpos($result, '✅27.09.2025, Проект ОИ837: Добавлена запись в табель:') !== false) {
        echo "  ✅ USES AUTOMATION FORMAT\n";
    } else {
        echo "  ❌ USES OLD FORMAT\n";
    }
    
    echo "\n";
}

echo "2. TESTING SPECIFIC PROBLEM CASE\n";
echo "================================\n";

echo "Testing the exact case from user report:\n";
$problemResult = $tabelEntry->createTabelMessage(
    'Test User',
    '27.09.2025',
    'ОИ837', 
    'ШМ_СдачаАвансовогоОтчета'
);

echo "Result: " . str_replace(["\r\n", "\n"], " ", $problemResult) . "\n\n";

if (strpos($problemResult, '💰 Сдача авансового отчета') !== false) {
    echo "✅ Contains expected emoji and text\n";
} else {
    echo "❌ Missing emoji and/or text\n";
}

if (strpos($problemResult, '✅27.09.2025, Проект ОИ837: Добавлена запись в табель:') !== false) {
    echo "✅ Uses automation format\n";
} else {
    echo "❌ Uses old format\n";
}

echo "\n3. CHECKING CHARACTER ENCODING\n";
echo "==============================\n";

$testString = 'ШМ_СдачаАвансовогоОтчета';
echo "Test string: '{$testString}'\n";
echo "String length: " . strlen($testString) . "\n";
echo "Multibyte length: " . mb_strlen($testString) . "\n";
echo "Encoding: " . mb_detect_encoding($testString) . "\n";

echo "\nTest completed.\n";
?>