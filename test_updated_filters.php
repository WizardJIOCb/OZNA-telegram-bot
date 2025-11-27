<?php
/**
 * Test Updated Activity Filters
 * Проверка обновленных фильтров активностей
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
    
    echo "╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║  ТЕСТ ОБНОВЛЕННЫХ ФИЛЬТРОВ АКТИВНОСТЕЙ                           ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "НЕРАБОЧИЕ АКТИВНОСТИ (не считаются работой на объекте):\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $nonWorkActivities = [
        '🚗 Дорога' => 'Маркер сессии',
        '👷 Инструктаж' => 'Маркер сессии',
        '💰 Сдача авансового отчета' => 'Маркер сессии',
        '💻 Текущая деятельность' => 'Офисная работа',
        '🏢 Участие в ЗПИ' => 'Может быть удаленно',
        '🤝 Участие в ОПИ' => 'Может быть удаленно',
        '📋 Разработка программы работ' => 'Планирование (офис)',
        '📅 Разработка графика работ' => 'Планирование (офис)'
    ];
    
    foreach ($nonWorkActivities as $activity => $reason) {
        echo "  ❌ {$activity}\n";
        echo "     └─ {$reason}\n";
    }
    
    echo "\n";
    echo "РАБОЧИЕ АКТИВНОСТИ (считаются работой на объекте):\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $workActivities = [
        '📋 Работы по графику' => 'Основная работа',
        '🔧 Монтаж оборудования' => 'Полевая работа',
        '⚡ ПНР работы' => 'Пусконаладка',
        '🔨 Устранение замечаний' => 'Доработки',
        '⚠️ Простой (любой вид)' => 'ВАЖНО: Сотрудник на объекте, ждет!',
        '📦 Организация допоставки' => 'Логистика на объекте',
        '🏗️ Строительно-монтажные работы' => 'Основная работа'
    ];
    
    foreach ($workActivities as $activity => $reason) {
        echo "  ✅ {$activity}\n";
        echo "     └─ {$reason}\n";
    }
    
    echo "\n";
    echo "╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║  ПРОВЕРКА ЛОГИКИ НА РЕАЛЬНЫХ ДАННЫХ                              ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";
    
    // Find users with "Разработка программы работ" or "Разработка графика работ"
    $conn = $databaseService->getConnection();
    
    $query = "SELECT DISTINCT user_id, message, dt 
              FROM ozna_tabel 
              WHERE (message LIKE '%Разработка программы работ%' 
                 OR message LIKE '%Разработка графика работ%')
              AND dt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              ORDER BY dt DESC
              LIMIT 5";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "Найдены сотрудники с планированием:\n";
        echo "─────────────────────────────────────────────────────────────────\n";
        
        while ($row = $result->fetch_assoc()) {
            // Get user info
            $userQuery = "SELECT data FROM ozna_data WHERE user_id = ?";
            $stmt = $conn->prepare($userQuery);
            $stmt->bind_param("i", $row['user_id']);
            $stmt->execute();
            $userResult = $stmt->get_result();
            $userData = $userResult->fetch_assoc();
            
            if ($userData) {
                $data = json_decode($userData['data'], true);
                $fio = $data['fio'] ?? 'Н/Д';
                
                if (preg_match('/табель: <b>(.*?)<\/b>/', $row['message'], $matches)) {
                    $activity = $matches[1];
                    echo "\n{$fio}\n";
                    echo "  Активность: {$activity}\n";
                    echo "  Дата: {$row['dt']}\n";
                    
                    // Check if on-site
                    $sessionStart = $personnelService->findFirstEntryInCurrentSession($row['user_id']);
                    if ($sessionStart !== null) {
                        echo "  ⚠️  ОШИБКА: Показан как 'НА ОБЪЕКТЕ' (должно быть НЕТ)\n";
                    } else {
                        echo "  ✅ Правильно: НЕ на объекте\n";
                    }
                }
            }
        }
    } else {
        echo "⚠️  Не найдено записей с планированием за последние 30 дней\n";
    }
    
    echo "\n";
    echo "╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ ТЕСТ ЗАВЕРШЕН                                                 ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
