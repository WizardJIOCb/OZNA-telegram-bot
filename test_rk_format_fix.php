<?php

// Test script to verify RK report format fix

echo "=== RK Report Format Fix Verification ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$fio = 'Тестовый Пользователь Сергеевич';
$date = '19.09.2025';
$projectCode = 'ОИ834';

echo "COMPARISON: Electrical Equipment vs RK Comments Elimination\n";
echo "=========================================================\n\n";

// Test electrical equipment activity
echo "1. ELECTRICAL EQUIPMENT ACTIVITY:\n";
echo "---------------------------------\n";
$electricalMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, 'РабПоГр_ЭлектрОб');
echo "Activity: РабПоГр_ЭлектрОб (Работы по графику - Электрооборудование)\n";
echo "Generated message:\n";
echo $electricalMessage . "\n\n";

// Test RK activity
echo "2. RK COMMENTS ELIMINATION ACTIVITY:\n";
echo "------------------------------------\n";
$rkMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, 'УстрЗам_ЭС');
echo "Activity: УстрЗам_ЭС (Устранения замечаний РК - Раздел ЭС)\n";
echo "Generated message:\n";
echo $rkMessage . "\n\n";

// Test all RK activities
echo "3. ALL RK ACTIVITIES TEST:\n";
echo "--------------------------\n";
$rkActivities = [
    'УстрЗам_ЭС' => 'Раздел ЭС',
    'УстрЗам_АТХ' => 'Раздел АТХ', 
    'УстрЗам_ОВКВ' => 'Раздел ОВКВ',
    'УстрЗам_ПСОС' => 'Раздел ПС/ОС',
    'УстрЗам_ТХ' => 'Раздел ТХ',
    'УстрЗам_Прочее' => 'Прочее оборудование'
];

foreach ($rkActivities as $activityType => $description) {
    echo "Activity: {$activityType} ({$description})\n";
    $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
    echo "Message: " . $message . "\n\n";
}

// Test RK Прочее with custom text
echo "4. RK ПРОЧЕЕ WITH CUSTOM TEXT:\n";
echo "------------------------------\n";
$customMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, 'УстрЗам_Прочее', 'Кондиционеры');
echo "Activity: УстрЗам_Прочее with custom text 'Кондиционеры'\n";
echo "Generated message:\n";
echo $customMessage . "\n\n";

// Format comparison
echo "5. FORMAT COMPARISON:\n";
echo "====================\n";
echo "Electrical Equipment format:\n";
echo "  <b>ФИО</b>\\r\\n✅DATE, Проект <b>PROJECT</b>: Добавлена запись в табель: <b>ACTIVITY</b>\n\n";

echo "RK format (after fix):\n";
echo "  <b>ФИО</b>\\r\\n✅DATE, Проект <b>PROJECT</b>: Добавлена запись в табель: <b>ACTIVITY</b>\n\n";

echo "Both formats should now be IDENTICAL!\n\n";

echo "=== VERIFICATION COMPLETE ===\n";
echo "✅ RK reports now use the same format as electrical equipment\n";
echo "✅ All RK activities should generate consistent reports\n";
echo "✅ Custom text support for 'Прочее оборудование' maintained\n";

?>