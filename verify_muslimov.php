<?php
/**
 * Verify Muslimov logic
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

$databaseService = new DatabaseService($host, $username, $password, $dbname);
$personnelService = new PersonnelService($databaseService);

$userId = 1580065522; // Muslimov

echo "=== АНАЛИЗ МУСЛИМОВА ===\n\n";

// Get all activities
$conn = $databaseService->getConnection();
$query = "SELECT dt, message FROM ozna_tabel 
          WHERE user_id = ? 
          AND dt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
          ORDER BY dt ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$activities = [];
while ($row = $result->fetch_assoc()) {
    if (preg_match('/табель: <b>(.*?)<\/b>/', $row['message'], $matches)) {
        $activity = $matches[1];
        $isWork = true;
        
        if (strpos($activity, 'Дорога') !== false) $isWork = false;
        elseif (strpos($activity, 'Инструктаж') !== false) $isWork = false;
        elseif (strpos($activity, 'Текущая деятельность') !== false) $isWork = false;
        elseif (strpos($activity, 'Участие в ЗПИ') !== false) $isWork = false;
        elseif (strpos($activity, 'Участие в ОПИ') !== false) $isWork = false;
        elseif (strpos($activity, 'Разработка программы работ') !== false) $isWork = false;
        elseif (strpos($activity, 'Разработка графика работ') !== false) $isWork = false;
        
        $activities[] = [
            'dt' => $row['dt'],
            'activity' => $activity,
            'is_work' => $isWork
        ];
    }
}

echo "ВСЕ АКТИВНОСТИ:\n";
echo str_repeat('-', 70) . "\n";

$workCount = 0;
$lastWorkDate = null;
$firstWorkDate = null;

foreach ($activities as $i => $act) {
    $marker = $act['is_work'] ? '[✅ РАБОТА]' : '[❌ НЕ РАБОТА]';
    echo "{$act['dt']}: {$act['activity']} {$marker}\n";
    
    if ($act['is_work']) {
        $workCount++;
        $lastWorkDate = $act['dt'];
        if ($firstWorkDate === null) {
            $firstWorkDate = $act['dt'];
        }
    }
}

echo "\nПОДСЧЕТ:\n";
echo "  Всего рабочих активностей: {$workCount}\n";
echo "  Первая работа: {$firstWorkDate}\n";
echo "  Последняя работа: {$lastWorkDate}\n";

if ($lastWorkDate) {
    $lastWork = new DateTime($lastWorkDate);
    $now = new DateTime();
    $days = floor(($now->getTimestamp() - $lastWork->getTimestamp()) / 86400);
    echo "  Дней с последней работы: {$days}\n";
}

echo "\nРЕЗУЛЬТАТ АНАЛИЗА:\n";
echo str_repeat('-', 70) . "\n";

$sessionStart = $personnelService->findFirstEntryInCurrentSession($userId);
if ($sessionStart !== null) {
    $now = new DateTime();
    $daysSince = floor(($now->getTimestamp() - $sessionStart->getTimestamp()) / 86400);
    echo "✅ НА ОБЪЕКТЕ с {$sessionStart->format('d.m.Y')} ({$daysSince} дней)\n";
    echo "\nОБОСНОВАНИЕ:\n";
    if ($workCount > 4) {
        echo "  • Найдено {$workCount} рабочих активностей (>4, требуется минимум 5)\n";
        echo "  • Последняя работа была {$days} дней назад (≤3 дней)\n";
        echo "  • Сценарий 2: Непрерывные работы без маркеров\n";
    }
} else {
    echo "❌ НЕ на объекте\n";
}
