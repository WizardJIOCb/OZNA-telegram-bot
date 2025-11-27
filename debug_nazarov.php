<?php
/**
 * Debug specific user to understand the logic
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
    $conn = $databaseService->getConnection();
    
    $userId = 2143801829; // Назаров
    
    echo "=== ОТЛАДКА ЛОГИКИ ДЛЯ НАЗАРОВА ===\n\n";
    
    // Get all entries in chronological order
    $query = "SELECT dt, message FROM ozna_tabel 
              WHERE user_id = ? 
              AND dt >= DATE_SUB(NOW(), INTERVAL 180 DAY)
              ORDER BY dt ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        if (preg_match('/табель: <b>(.*?)<\/b>/', $row['message'], $matches)) {
            $activity = $matches[1];
            
            $isDoroga = (strpos($activity, 'Дорога') !== false);
            $isInstruktazh = (strpos($activity, 'Инструктаж') !== false);
            $isAvans = (strpos($activity, 'Сдача авансового отчета') !== false);
            $isOffice = (strpos($activity, 'Текущая деятельность') !== false) ||
                       (strpos($activity, 'Участие в ЗПИ') !== false) ||
                       (strpos($activity, 'Участие в ОПИ') !== false);
            
            $isWork = !$isDoroga && !$isInstruktazh && !$isAvans && !$isOffice;
            
            $entries[] = [
                'dt' => new DateTime($row['dt']),
                'message' => $row['message'],
                'activity' => $activity,
                'is_work' => $isWork,
                'is_doroga' => $isDoroga,
                'is_instruktazh' => $isInstruktazh,
                'is_avans' => $isAvans
            ];
        }
    }
    
    echo "ВСЕГО ЗАПИСЕЙ: " . count($entries) . "\n\n";
    
    echo "ВСЕ ЗАПИСИ (В ХРОНОЛОГИЧЕСКОМ ПОРЯДКЕ):\n";
    echo str_repeat('-', 80) . "\n";
    foreach ($entries as $i => $entry) {
        $type = '';
        if ($entry['is_doroga']) $type = '[ДОРОГА]';
        elseif ($entry['is_instruktazh']) $type = '[ИНСТРУКТАЖ]';
        elseif ($entry['is_avans']) $type = '[АВАНС]';
        elseif ($entry['is_work']) $type = '[РАБОТА]';
        else $type = '[ОФИС]';
        
        echo sprintf("[%d] %s: %s %s\n", $i, $entry['dt']->format('Y-m-d H:i'), $entry['activity'], $type);
    }
    
    echo "\n";
    echo str_repeat('=', 80) . "\n";
    echo "АНАЛИЗ СЦЕНАРИЯ 1: ПОИСК СЕССИИ С МАРКЕРОМ\n";
    echo str_repeat('=', 80) . "\n\n";
    
    $lastEntry = $entries[count($entries) - 1];
    echo "Последняя запись: {$lastEntry['activity']}\n";
    
    if ($lastEntry['is_doroga'] || $lastEntry['is_avans']) {
        echo "✅ Последняя запись - закрывающий маркер (Дорога/Аванс)\n";
        echo "→ СЕССИЯ ЗАКРЫТА, сотрудник НЕ на объекте\n";
    } else {
        echo "❌ Последняя запись НЕ закрывающий маркер\n";
        echo "→ Ищем маркер начала сессии...\n\n";
        
        // Look for most recent start marker
        $markerIndex = -1;
        for ($i = count($entries) - 1; $i >= 0; $i--) {
            if ($entries[$i]['is_doroga'] || $entries[$i]['is_instruktazh']) {
                $markerIndex = $i;
                echo "Найден маркер начала на позиции [{$i}]: {$entries[$i]['activity']} ({$entries[$i]['dt']->format('Y-m-d')})\n";
                break;
            }
        }
        
        if ($markerIndex !== -1) {
            echo "\nПодсчет рабочих активностей ПОСЛЕ маркера:\n";
            $workCount = 0;
            for ($i = $markerIndex + 1; $i < count($entries); $i++) {
                if ($entries[$i]['is_work']) {
                    $workCount++;
                    echo "  [{$i}] {$entries[$i]['dt']->format('Y-m-d')}: {$entries[$i]['activity']}\n";
                }
            }
            
            echo "\nВсего рабочих активностей ПОСЛЕ маркера: {$workCount}\n";
            
            if ($workCount > 2) {
                echo "✅ Найдено >2 активностей (минимум 3)\n";
                echo "→ СЕССИЯ ОТКРЫТА, дата начала: {$entries[$markerIndex]['dt']->format('d.m.Y')}\n";
            } else {
                echo "❌ Недостаточно активностей (нужно >2, т.е. минимум 3)\n";
                echo "→ Переходим к сценарию 2\n";
            }
        } else {
            echo "❌ Маркер начала не найден\n";
            echo "→ Переходим к сценарию 2\n";
        }
    }
    
    echo "\n";
    echo str_repeat('=', 80) . "\n";
    echo "АНАЛИЗ СЦЕНАРИЯ 2: НЕПРЕРЫВНЫЕ РАБОЧИЕ АКТИВНОСТИ БЕЗ МАРКЕРА\n";
    echo str_repeat('=', 80) . "\n\n";
    
    $now = new DateTime();
    $daysSinceLast = floor(($now->getTimestamp() - $lastEntry['dt']->getTimestamp()) / 86400);
    echo "Дней с последней записи: {$daysSinceLast}\n";
    
    if ($daysSinceLast > 3) {
        echo "❌ Прошло >3 дней с последней активности\n";
        echo "→ Сессия прекратилась\n";
    } else {
        echo "✅ Последняя активность была ≤3 дней назад\n\n";
        
        echo "Подсчет непрерывных рабочих активностей:\n";
        $continuousWorkStart = null;
        $continuousWorkCount = 0;
        $prevWorkDate = null;
        
        foreach ($entries as $i => $entry) {
            if (!$entry['is_work']) {
                continue;
            }
            
            if ($prevWorkDate !== null) {
                $gap = floor(($entry['dt']->getTimestamp() - $prevWorkDate->getTimestamp()) / 86400);
                echo "  [{$i}] {$entry['dt']->format('Y-m-d')}: {$entry['activity']} (разрыв: {$gap} дн.)\n";
                
                if ($gap > 3) {
                    echo "    ⚠️ Разрыв >3 дней, сброс счетчика\n";
                    $continuousWorkStart = $entry['dt'];
                    $continuousWorkCount = 1;
                    $prevWorkDate = $entry['dt'];
                    continue;
                }
            } else {
                echo "  [{$i}] {$entry['dt']->format('Y-m-d')}: {$entry['activity']} (первая)\n";
            }
            
            if ($continuousWorkStart === null) {
                $continuousWorkStart = $entry['dt'];
            }
            
            $continuousWorkCount++;
            $prevWorkDate = $entry['dt'];
        }
        
        echo "\nВсего непрерывных рабочих активностей: {$continuousWorkCount}\n";
        
        if ($continuousWorkCount > 4) {
            echo "✅ Найдено >4 активностей (минимум 5)\n";
            echo "→ СОТРУДНИК НА ОБЪЕКТЕ, дата начала: {$continuousWorkStart->format('d.m.Y')}\n";
        } else {
            echo "❌ Недостаточно активностей (нужно >4, т.е. минимум 5)\n";
            echo "→ Сотрудник НЕ на объекте\n";
        }
    }
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
}
