<?php
// Test МонтОб_ЭлектрОб formatting fix

require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Mock the services for testing
$mockDbService = new class extends DatabaseService {
    public function __construct() {} // Empty constructor to avoid database connection
};

$mockMsgService = new class extends MessageService {
    public function __construct() {} // Empty constructor to avoid Telegram connection
};

$tabelEntry = new TabelEntry($mockDbService, $mockMsgService);

echo "Testing МонтОб_ЭлектрОб Formatting Fix\n";
echo "====================================\n\n";

// Test data
$fio = "Богданов Борис Сергеевич";
$date = "19.09.2025";
$projectCode = "ОИ834";
$activityType = "МонтОб_ЭлектрОб";

echo "Testing activity type: {$activityType}\n\n";

echo "1. Testing Confirmation Message:\n";
echo "--------------------------------\n";
$confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
echo "Confirmation message: {$confirmationMessage}\n";

// Check if follows the new format
$isNewFormat = strpos($confirmationMessage, "Проект <b>{$projectCode}</b>:") !== false;
echo "Uses new format: " . ($isNewFormat ? "✅ YES" : "❌ NO") . "\n\n";

echo "2. Testing Final Report Message:\n";
echo "--------------------------------\n";
$finalMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
echo "Final message: {$finalMessage}\n";

// Check if follows the new format
$isNewFormat = strpos($finalMessage, "<b>{$fio}</b>\r\n✅{$date}, Проект <b>{$projectCode}</b>: Добавлена запись в табель:") !== false;
echo "Uses new format: " . ($isNewFormat ? "✅ YES" : "❌ NO") . "\n\n";

echo "3. Comparison with other electrical equipment activities:\n";
echo "-------------------------------------------------------\n";

$otherActivities = [
    'РабПоГр_ЭлектрОб' => 'Работы по графику',
    'ФормИД_ЭлектрОб' => 'Формирование ИД',
    'МонтКбЛн_ЭлектрОб' => 'Прокладка кабеля'
];

foreach ($otherActivities as $actType => $description) {
    echo "Activity: {$actType} ({$description})\n";
    $confirmationMsg = $tabelEntry->getConfirmationMessage($actType, $projectCode);
    $finalMsg = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $actType);
    
    $confirmationFormat = strpos($confirmationMsg, "Проект <b>{$projectCode}</b>:") !== false;
    $finalFormat = strpos($finalMsg, "<b>{$fio}</b>\r\n✅{$date}, Проект <b>{$projectCode}</b>: Добавлена запись в табель:") !== false;
    
    echo "  Confirmation format: " . ($confirmationFormat ? "✅" : "❌") . "\n";
    echo "  Final format: " . ($finalFormat ? "✅" : "❌") . "\n\n";
}

echo "✅ Test completed!\n";
echo "МонтОб_ЭлектрОб should now work with the same professional formatting as other electrical equipment activities.\n";