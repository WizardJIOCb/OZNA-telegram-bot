<?php

require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "🔥 Testing Fire Alarm Equipment Activities\n";
echo "==========================================\n\n";

// Create mock dependencies
$mockDb = new class extends DatabaseService {
    public function __construct() {}
    public function hasRecentTabelEntry($userId, $isSuperAdmin) { return null; }
};

$mockMsg = new class extends MessageService {
    public function __construct() {}
    public function sendToChannel($message, $channel) { return true; }
};

$tabelEntry = new TabelEntry($mockDb, $mockMsg);

// Test all Fire Alarm Equipment activities
$fireAlarmActivities = [
    'РабПоГр_ПожСигн' => '🔧 Работы по графику',
    'ФормИД_ПожСигн' => '📋 Формирование ИД',
    'ПусНал_ПожСигн' => '⚙️ Пусконаладочные работы',
    'МонтОб_ПожСигн' => '🖥️ Монтаж оборудования', // Renamed from МонтАСУ_ПожСигн
    'ПодтВех_ПожСигн' => '🎯 Подтвердить достижение вехи'
];

echo "1. Testing activity mappings (УдалПО_ПожСигн should be removed):\n";
foreach ($fireAlarmActivities as $activityType => $description) {
    try {
        $confirmation = $tabelEntry->getConfirmationMessage($activityType, 'ОИ834', 'Test User');
        echo "✅ $activityType ($description): Confirmation message generated\n";
        
        $report = $tabelEntry->createTabelMessage($activityType, 'ОИ834', 'Test User', '2025-09-20');
        echo "✅ $activityType ($description): Report message generated\n";
        echo "   Report: " . substr($report, 0, 100) . "...\n\n";
    } catch (Exception $e) {
        echo "❌ $activityType ($description): Error - " . $e->getMessage() . "\n\n";
    }
}

echo "2. Testing professional formatting (should match Electrical Equipment style):\n";
$professionalTest = $tabelEntry->getConfirmationMessage('МонтОб_ПожСигн', 'ОИ834', 'Test User');
echo "Confirmation: $professionalTest\n";

$professionalReport = $tabelEntry->createTabelMessage('МонтОб_ПожСигн', 'ОИ834', 'Test User', '2025-09-20');
echo "Report: $professionalReport\n";

echo "\n🎉 Fire Alarm Equipment test completed!\n";

?>