<?php
/**
 * Test script for supervisor level functionality
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== Тестирование нового уровня доступа Супервайзер ===\n\n";

// Test 1: Check access level constants
echo "1. Проверка констант уровней доступа:\n";
echo "   ACCESS_LEVEL_GUEST = " . DatabaseService::ACCESS_LEVEL_GUEST . "\n";
echo "   ACCESS_LEVEL_USER = " . DatabaseService::ACCESS_LEVEL_USER . "\n";
echo "   ACCESS_LEVEL_SUPERVISOR = " . DatabaseService::ACCESS_LEVEL_SUPERVISOR . "\n";
echo "   ACCESS_LEVEL_ADMIN = " . DatabaseService::ACCESS_LEVEL_ADMIN . "\n";
echo "   ACCESS_LEVEL_SUPER_ADMIN = " . DatabaseService::ACCESS_LEVEL_SUPER_ADMIN . "\n\n";

// Test 2: Check access level names (mock implementation)
echo "2. Проверка текстовых описаний уровней доступа:\n";
$accessLevelNames = [
    0 => "Гость",
    1 => "Пользователь",
    2 => "Супервайзер",
    3 => "Администратор",
    4 => "Суперадминистратор"
];

foreach ($accessLevelNames as $level => $name) {
    echo "   Уровень {$level}: {$name}\n";
}

echo "\n=== Тест завершен ===\n";
echo "Примечание: Тестирование функциональности с базой данных требует установленного расширения MySQLi.\n";
?>