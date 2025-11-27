<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();
$conn = $dbService->getConnection();

echo "=== ОБНОВЛЕНИЕ ФН ДЛЯ СОТРУДНИКОВ ШМР ===\n\n";

// Список изменений
$updates = [
    'Пушкарев' => 'Начальник управления',
    'Фаткуллин' => 'Руководитель проектов',
    'Калимуллин' => 'Руководитель проектов',
    'Ахметзянова' => 'Администратор'
];

$users = [];

foreach ($updates as $name => $newFn) {
    $query = "SELECT user_id, data FROM ozna_data WHERE data LIKE ?";
    $searchPattern = "%$name%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $data = json_decode($row['data'], true);
        $fio = $data['fio'] ?? '';
        
        if (stripos($fio, $name) !== false) {
            $users[] = [
                'user_id' => $row['user_id'],
                'fio' => $fio,
                'current_fn' => $data['functional_direction'] ?? 'не указано',
                'new_fn' => $newFn,
                'data' => $data
            ];
        }
    }
}

echo "Найдено пользователей: " . count($users) . "\n\n";

if (empty($users)) {
    echo "❌ Пользователи не найдены!\n";
    exit;
}

foreach ($users as $i => $user) {
    echo ($i + 1) . ". ФИО: {$user['fio']}\n";
    echo "   User ID: {$user['user_id']}\n";
    echo "   Текущее ФН: {$user['current_fn']}\n";
    echo "   → ИЗМЕНИТЬ на: {$user['new_fn']}\n\n";
}

echo str_repeat("=", 70) . "\n";
echo "НАЧИНАЮ ОБНОВЛЕНИЕ...\n\n";

foreach ($users as $i => $user) {
    $userId = $user['user_id'];
    $data = $user['data'];
    
    // Изменяем функциональное направление
    $data['functional_direction'] = $user['new_fn'];
    
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    
    $query = "UPDATE ozna_data SET data = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $jsonData, $userId);
    
    echo ($i + 1) . ". {$user['fio']} → {$user['new_fn']}... ";
    
    if ($stmt->execute()) {
        echo "✅\n";
    } else {
        echo "❌\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ ОБНОВЛЕНИЕ ЗАВЕРШЕНО!\n";
echo "Обновлено пользователей: " . count($users) . "\n";
