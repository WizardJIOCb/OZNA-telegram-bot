<?php
/**
 * Test the fixed free-time logic
 * Verify that employees with only office activities now appear in the list
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

try {
    $databaseService = new DatabaseService($host, $username, $password, $dbname);
    $personnelService = new PersonnelService($databaseService);
    $conn = $databaseService->getConnection();
    
    echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
    echo "║  ТЕСТ ИСПРАВЛЕНИЯ: СОТРУДНИКИ В СПИСКЕ СВОБОДНОЙ ВАХТЫ             ║\n";
    echo "╚════════════════════════════════════════════════════════════════════╝\n\n";
    
    // Test users that were missing
    $testUsers = [
        ['name' => 'Гайнутдинов', 'fn' => 'Пусконаладка'],
        ['name' => 'Миндикаев', 'fn' => 'Шеф-монтаж'],
        ['name' => 'Шангин', 'fn' => 'Шеф-монтаж'],
        ['name' => 'Ночевной', 'fn' => 'Шеф-монтаж'],
        ['name' => 'Муртазин', 'fn' => 'Шеф-монтаж']
    ];
    
    foreach ($testUsers as $testUser) {
        // Find user in database
        $query = "SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM user WHERE CONCAT(first_name, ' ', last_name) LIKE ?";
        $stmt = $conn->prepare($query);
        $searchTerm = '%' . $testUser['name'] . '%';
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            echo "⚠️  {$testUser['name']}: НЕ НАЙДЕН В БАЗЕ ДАННЫХ\n\n";
            continue;
        }
        
        echo str_repeat('=', 70) . "\n";
        echo "СОТРУДНИК: {$user['full_name']} (ID: {$user['id']})\n";
        echo "ФУНКЦИОНАЛЬНОЕ НАПРАВЛЕНИЕ: {$testUser['fn']}\n";
        echo str_repeat('=', 70) . "\n";
        
        // Check on-site status
        $sessionStart = $personnelService->findFirstEntryInCurrentSession($user['id']);
        
        if ($sessionStart !== null) {
            $now = new DateTime();
            $days = $personnelService->getDaysBetween($sessionStart, $now);
            echo "❌ СТАТУС: НА ОБЪЕКТЕ (с {$sessionStart->format('d.m.Y')}, {$days} дней)\n";
            echo "   Не должен быть в списке свободной вахты\n";
        } else {
            echo "✅ СТАТУС: НЕ НА ОБЪЕКТЕ\n";
        }
        
        // Check if appears in freetime list
        $freetimeEmployees = $personnelService->getFreetimeEmployees($testUser['fn']);
        $foundInList = false;
        $employeeData = null;
        
        foreach ($freetimeEmployees as $emp) {
            if ($emp['user_id'] == $user['id']) {
                $foundInList = true;
                $employeeData = $emp;
                break;
            }
        }
        
        if ($foundInList) {
            echo "✅ НАЙДЕН В СПИСКЕ СВОБОДНОЙ ВАХТЫ:\n";
            echo "   {$employeeData['icon']} {$employeeData['fio']}\n";
            echo "   Дней отдыха: {$employeeData['days']}\n";
            echo "   Последняя активность: {$employeeData['last_work_date']}\n";
        } else {
            echo "❌ НЕ НАЙДЕН В СПИСКЕ СВОБОДНОЙ ВАХТЫ!\n";
            echo "   ⚠️  ПРОБЛЕМА СОХРАНЯЕТСЯ!\n";
        }
        
        echo "\n";
    }
    
    echo str_repeat('=', 70) . "\n";
    echo "ПОЛНЫЙ СПИСОК СВОБОДНОЙ ВАХТЫ ПО НАПРАВЛЕНИЯМ:\n";
    echo str_repeat('=', 70) . "\n\n";
    
    $directions = ['Мобильная бригада', 'Шеф-монтаж', 'Пусконаладка'];
    
    foreach ($directions as $direction) {
        echo "📋 {$direction}:\n";
        echo str_repeat('-', 70) . "\n";
        
        $freetimeEmployees = $personnelService->getFreetimeEmployees($direction);
        
        if (empty($freetimeEmployees)) {
            echo "   (нет сотрудников на свободной вахте)\n";
        } else {
            foreach ($freetimeEmployees as $emp) {
                $daysWord = $personnelService->getDaysWord($emp['days']);
                echo "   {$emp['icon']} {$emp['fio']} - {$emp['days']} {$daysWord}\n";
                echo "      📅 {$emp['last_work_date']}\n";
            }
            echo "\n   Всего: " . count($freetimeEmployees) . "\n";
        }
        
        echo "\n";
    }
    
    echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ ТЕСТ ЗАВЕРШЕН                                                  ║\n";
    echo "╚════════════════════════════════════════════════════════════════════╝\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
