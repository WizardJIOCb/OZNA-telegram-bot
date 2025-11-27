<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

echo "=== АНАЛИЗ: ИДРИСОВ ===\n\n";

// Найдем Идрисова в базе
$conn = $dbService->getConnection();
$query = "SELECT user_id, data FROM ozna_data WHERE data LIKE '%Идрисов%'";
$result = $conn->query($query);

$idrisovUsers = [];
while ($row = $result->fetch_assoc()) {
    $data = json_decode($row['data'], true);
    $fio = $data['fio'] ?? '';
    if (stripos($fio, 'Идрисов') !== false) {
        $idrisovUsers[] = [
            'user_id' => $row['user_id'],
            'fio' => $fio,
            'fn' => $data['functional_direction'] ?? 'Не указано'
        ];
    }
}

if (empty($idrisovUsers)) {
    echo "❌ Идрисов не найден в базе\n";
    exit;
}

echo "Найдено пользователей с фамилией Идрисов: " . count($idrisovUsers) . "\n\n";

foreach ($idrisovUsers as $user) {
    echo "=" . str_repeat("=", 70) . "\n";
    echo "ФИО: {$user['fio']}\n";
    echo "User ID: {$user['user_id']}\n";
    echo "ФН: {$user['fn']}\n";
    echo str_repeat("-", 70) . "\n\n";
    
    $userId = $user['user_id'];
    
    // Получаем последние записи из табеля
    $query = "SELECT dt, message FROM ozna_tabel 
              WHERE user_id = ? 
              ORDER BY dt DESC
              LIMIT 20";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    
    echo "📊 Последние 20 записей:\n\n";
    
    if (empty($entries)) {
        echo "⚠️  НЕТ ЗАПИСЕЙ В ТАБЕЛЕ\n\n";
        continue;
    }
    
    // Показываем последние записи с классификацией
    $reflection = new ReflectionClass($personnelService);
    $isWorkMethod = $reflection->getMethod('isWorkActivity');
    $isWorkMethod->setAccessible(true);
    
    $now = new DateTime();
    
    foreach ($entries as $i => $entry) {
        $isWork = $isWorkMethod->invoke($personnelService, $entry['message']);
        $type = $isWork ? "🔧 РАБОЧАЯ" : "🏢 НЕРАБОЧАЯ";
        
        // Извлекаем краткое название активности
        preg_match('/запись в табель:\s*<b>(.+?)<\/b>/', $entry['message'], $matches);
        $activity = $matches[1] ?? 'Неизвестно';
        
        $date = new DateTime($entry['dt']);
        $hoursAgo = round(($now->getTimestamp() - $date->getTimestamp()) / 3600, 1);
        
        $timeInfo = $hoursAgo < 24 ? "⏰ $hoursAgo ч. назад" : "";
        
        echo sprintf("%2d. %s [%s] %s %s\n", 
            $i + 1, 
            $date->format('d.m.Y H:i'), 
            $type,
            $activity,
            $timeInfo
        );
    }
    
    echo "\n" . str_repeat("-", 70) . "\n";
    
    // Анализ через приватные методы
    $firstSessionMethod = $reflection->getMethod('findFirstEntryInCurrentSession');
    $firstSessionMethod->setAccessible(true);
    $sessionStart = $firstSessionMethod->invoke($personnelService, $userId);
    
    $freeTimeMethod = $reflection->getMethod('findLastContinuousWorkPeriod');
    $freeTimeMethod->setAccessible(true);
    $freeTimeStart = $freeTimeMethod->invoke($personnelService, $userId);
    
    echo "🔍 ТЕКУЩИЙ СТАТУС (ПРЯМО СЕЙЧАС):\n";
    echo str_repeat("-", 70) . "\n";
    
    if ($sessionStart) {
        echo "✅ НА ОБЪЕКТЕ\n";
        echo "   Начало вахты: " . $sessionStart->format('d.m.Y') . "\n";
        $days = $personnelService->getDaysBetween($sessionStart, $now);
        echo "   На объекте: $days " . $personnelService->getDaysWord($days) . "\n";
    } else if ($freeTimeStart) {
        echo "🏖️  НА СВОБОДНОЙ ВАХТЕ\n";
        echo "   Начало свободной вахты: " . $freeTimeStart->format('d.m.Y') . "\n";
        $days = $personnelService->getDaysBetween($freeTimeStart, $now);
        echo "   На отдыхе: $days " . $personnelService->getDaysWord($days) . "\n";
    } else {
        echo "⚪ БЕЗ ДАННЫХ\n";
    }
    
    // Проверяем, есть ли сегодняшние записи
    echo "\n📅 ЗАПИСИ СЕГОДНЯ (20.10.2025):\n";
    echo str_repeat("-", 70) . "\n";
    
    $todayEntries = array_filter($entries, function($entry) {
        return date('Y-m-d', strtotime($entry['dt'])) === '2025-10-20';
    });
    
    if (!empty($todayEntries)) {
        echo "✅ Есть записи сегодня:\n";
        foreach ($todayEntries as $entry) {
            $isWork = $isWorkMethod->invoke($personnelService, $entry['message']);
            $type = $isWork ? "🔧 РАБОЧАЯ" : "🏢 НЕРАБОЧАЯ";
            preg_match('/запись в табель:\s*<b>(.+?)<\/b>/', $entry['message'], $matches);
            $activity = $matches[1] ?? 'Неизвестно';
            
            $date = new DateTime($entry['dt']);
            echo "   • " . $date->format('H:i') . " [$type] $activity\n";
        }
    } else {
        echo "❌ Сегодня записей нет\n";
    }
    
    echo "\n" . str_repeat("=", 70) . "\n\n";
}
