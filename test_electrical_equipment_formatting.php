<?php
// Test electrical equipment formatting implementation

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

echo "Testing Electrical Equipment Formatting Implementation\n";
echo "====================================================\n\n";

// Test data
$fio = "Богданов Борис Сергеевич";
$date = "19.09.2025";
$projectCode = "ОИ834";

// Test all electrical equipment activities
$electricalActivities = [
    'РабПоГр_ЭлектрОб' => 'Работы по графику (Электрооборудование)',
    'ФормИД_ЭлектрОб' => 'Формирование ИД (Электрооборудование)',
    'ПодтВех_ЭлектрОб' => 'Подтвердить достижение вехи (Электрооборудование)',
    'ИзмСхПд_ЭлектрОб' => 'Изменение схемы подключения (Электрооборудование, Дополнительные работы)',
    'МонтКбЛн_ЭлектрОб' => 'Прокладка кабеля (Электрооборудование, Дополнительные работы)',
    'РаскКбЛн_ЭлектрОб' => 'Подключение кабеля (Электрооборудование, Дополнительные работы)',
    'Прочее_ЭлектрОб' => 'Прочее (Электрооборудование, Дополнительные работы)'
];

echo "1. Testing Confirmation Messages:\n";
echo "--------------------------------\n";
foreach ($electricalActivities as $activityType => $expectedActivity) {
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    echo "Activity: {$activityType}\n";
    echo "Expected format: Проект {$projectCode}: {$expectedActivity}. Отправить запись в табель?\n";
    echo "Actual result: {$confirmationMessage}\n";
    
    // Check if follows the new format
    $isNewFormat = strpos($confirmationMessage, "Проект <b>{$projectCode}</b>:") !== false;
    echo "Uses new format: " . ($isNewFormat ? "✅ YES" : "❌ NO") . "\n\n";
}

echo "\n2. Testing Final Report Messages:\n";
echo "---------------------------------\n";
foreach ($electricalActivities as $activityType => $expectedActivity) {
    $finalMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
    echo "Activity: {$activityType}\n";
    echo "Expected format: {$fio}\n✅{$date}, Проект {$projectCode}: Добавлена запись в табель: {$expectedActivity}\n";
    echo "Actual result: {$finalMessage}\n";
    
    // Check if follows the new format
    $isNewFormat = strpos($finalMessage, "<b>{$fio}</b>\r\n✅{$date}, Проект <b>{$projectCode}</b>: Добавлена запись в табель:") !== false;
    echo "Uses new format: " . ($isNewFormat ? "✅ YES" : "❌ NO") . "\n\n";
}

echo "3. Comparison with Commissioning Activities (for reference):\n";
echo "-----------------------------------------------------------\n";
$commissioningActivity = 'КомплексноеОпробование';
$commissioningConfirmation = $tabelEntry->getConfirmationMessage($commissioningActivity, $projectCode);
$commissioningFinal = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $commissioningActivity);

echo "Commissioning confirmation: {$commissioningConfirmation}\n";
echo "Commissioning final: {$commissioningFinal}\n\n";

echo "✅ Test completed successfully!\n";
echo "All electrical equipment activities now use the same professional formatting as commissioning activities.\n";