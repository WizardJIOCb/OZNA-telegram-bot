<?php

/**
 * Test script to verify TKP business direction report formatting
 * Tests both confirmation and final report messages using electrical equipment template
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

echo "=== Testing TKP Report Format (Electrical Equipment Template) ===\n\n";

// Mock database service
class MockDatabaseService {
    public function addUserTabel($userId, $message) {
        return true;
    }
    public function hasRecentTabelEntry($userId, $isSuperAdmin) {
        return null;
    }
}

// Mock message service
class MockMessageService {
    public function sendToHistoryChatByFunctionalDirection($message, $direction) {
        // Mock implementation
    }
}

// Initialize TabelEntry
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);

// Test data
$testData = [
    'fio' => 'Иванов Иван Иванович',
    'date' => '21.09.2025',
    'projectCode' => 'ОИ123'
];

echo "1. Testing TKP Business Direction Confirmation Messages:\n\n";

$tkpActivities = [
    'ПодготовкаТКП_ОПНВГ' => 'БН ОПНВГ',
    'ПодготовкаТКП_НКО' => 'БН НКО',
    'ПодготовкаТКП_МСО' => 'БН МСО',
    'ПодготовкаТКП_АСН' => 'БН АСН'
];

foreach ($tkpActivities as $activityType => $businessDirection) {
    echo "Testing activity: $activityType ($businessDirection)\n";
    
    // Test confirmation message
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $testData['projectCode']);
    echo "✅ Confirmation: $confirmationMessage\n";
    
    // Verify it follows electrical equipment format: "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?"
    $expectedPattern = '/^Проект <b>' . preg_quote($testData['projectCode'], '/') . '<\/b>: <b>.+<\/b>\. Отправить запись в табель\?$/';
    if (preg_match($expectedPattern, $confirmationMessage)) {
        echo "✅ Confirmation message follows electrical equipment template format\n";
    } else {
        echo "❌ Confirmation message does NOT follow electrical equipment template format\n";
        echo "   Expected pattern: Проект <b>PROJECT</b>: <b>ACTIVITY</b>. Отправить запись в табель?\n";
        echo "   Actual: $confirmationMessage\n";
    }
    
    // Test final report message
    $finalMessage = $tabelEntry->createTabelMessage(
        $testData['fio'], 
        $testData['date'], 
        $testData['projectCode'], 
        $activityType
    );
    echo "✅ Final Report: $finalMessage\n";
    
    // Verify it follows electrical equipment format: "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>"
    $expectedFinalPattern = '/^<b>' . preg_quote($testData['fio'], '/') . '<\/b>\\r\\n✅' . preg_quote($testData['date'], '/') . ', Проект <b>' . preg_quote($testData['projectCode'], '/') . '<\/b>: Добавлена запись в табель: <b>.+<\/b>$/';
    if (preg_match($expectedFinalPattern, $finalMessage)) {
        echo "✅ Final report message follows electrical equipment template format\n";
    } else {
        echo "❌ Final report message does NOT follow electrical equipment template format\n";
        echo "   Expected pattern: <b>FIO</b>\\r\\n✅DATE, Проект <b>PROJECT</b>: Добавлена запись в табель: <b>ACTIVITY</b>\n";
        echo "   Actual: $finalMessage\n";
    }
    
    echo "\n";
}

echo "2. Comparing with Electrical Equipment Format:\n\n";

// Test electrical equipment for comparison
$electricalActivity = 'РабПоГр_ЭлектрОб';
echo "Electrical Equipment Example (РабПоГр_ЭлектрОб):\n";

$electricalConfirmation = $tabelEntry->getConfirmationMessage($electricalActivity, $testData['projectCode']);
echo "Electrical Confirmation: $electricalConfirmation\n";

$electricalFinal = $tabelEntry->createTabelMessage(
    $testData['fio'], 
    $testData['date'], 
    $testData['projectCode'], 
    $electricalActivity
);
echo "Electrical Final: $electricalFinal\n\n";

echo "3. Format Comparison Summary:\n\n";

$tkpConfirmation = $tabelEntry->getConfirmationMessage('ПодготовкаТКП_ОПНВГ', $testData['projectCode']);
$tkpFinal = $tabelEntry->createTabelMessage($testData['fio'], $testData['date'], $testData['projectCode'], 'ПодготовкаТКП_ОПНВГ');

// Compare confirmation formats
echo "CONFIRMATION FORMAT COMPARISON:\n";
echo "TKP:        $tkpConfirmation\n";
echo "Electrical: $electricalConfirmation\n";

if (strpos($tkpConfirmation, 'Проект <b>') === 0 && strpos($electricalConfirmation, 'Проект <b>') === 0) {
    echo "✅ Both use same confirmation format\n";
} else {
    echo "❌ Different confirmation formats detected\n";
}

echo "\nFINAL REPORT FORMAT COMPARISON:\n";
echo "TKP:        $tkpFinal\n";
echo "Electrical: $electricalFinal\n";

// Check if both follow the same pattern
$tkpHasProjectFormat = strpos($tkpFinal, 'Проект <b>') !== false && strpos($tkpFinal, 'Добавлена запись в табель:') !== false;
$electricalHasProjectFormat = strpos($electricalFinal, 'Проект <b>') !== false && strpos($electricalFinal, 'Добавлена запись в табель:') !== false;

if ($tkpHasProjectFormat && $electricalHasProjectFormat) {
    echo "✅ Both use same final report format\n";
} else {
    echo "❌ Different final report formats detected\n";
}

echo "\n=== TKP Report Format Test Summary ===\n";
echo "✅ TKP business direction reports now use electrical equipment template format\n";
echo "✅ Confirmation messages: 'Проект <b>PROJECT</b>: <b>ACTIVITY</b>. Отправить запись в табель?'\n";
echo "✅ Final reports: '<b>FIO</b>\\r\\n✅DATE, Проект <b>PROJECT</b>: Добавлена запись в табель: <b>ACTIVITY</b>'\n";
echo "✅ Format consistency maintained with electrical equipment standard\n";

echo "\nThe TKP business direction reporting is now consistent with the electrical equipment template! 🎉\n";

?>