<?php

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$db = new DatabaseService('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$conn = $db->getConnection();

$result = $conn->query('SHOW TABLES');

echo "Tables in tg_bot database:\n";
echo str_repeat('=', 40) . "\n";
while ($row = $result->fetch_array()) {
    echo $row[0] . "\n";
}
