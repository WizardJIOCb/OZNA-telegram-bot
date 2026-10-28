<?php
require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$db = new DatabaseService();
$userData = $db->getUserData(5499115173);

echo "User 5499115173 (Nochnoy Pavel Aleksandrovich):\n";
echo "FIO: " . ($userData['fio'] ?? 'NOT SET') . "\n";
echo "Functional Direction: " . ($userData['functional_direction'] ?? 'NOT SET') . "\n";
