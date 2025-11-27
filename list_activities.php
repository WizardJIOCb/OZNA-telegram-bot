<?php
/**
 * List all unique activities from tabel
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$db = new DatabaseService();
$conn = $db->getConnection();

echo "=== ALL UNIQUE ACTIVITIES ===\n\n";

// Extract activity name from message format:
// "<b>Name</b> ✅DATE, Проект <b>PROJECT</b>: Добавлена запись в табель: <b>🏢 Activity Name</b>"
$query = "SELECT DISTINCT 
          TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(message, 'табель: <b>', -1), '</b>', 1)) as activity 
          FROM ozna_tabel 
          WHERE message LIKE '%Добавлена запись в табель:%'
          ORDER BY activity";

$result = $conn->query($query);

$activities = [];
while ($row = $result->fetch_assoc()) {
    $activity = $row['activity'];
    if (!empty($activity)) {
        $activities[] = $activity;
        echo $activity . "\n";
    }
}

echo "\n\n=== TOTAL: " . count($activities) . " unique activities ===\n";
