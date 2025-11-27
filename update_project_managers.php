<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();
$conn = $dbService->getConnection();

echo "=== ИЗМЕНЕНИЕ ФН ДЛЯ РУКОВОДИТЕЛЕЙ ПРОЕКТОВ ===\n\n";

// Ищем Навширванова и Ткачева
$names = ['Навширванов', 'Ткачев'];
$users = [];

foreach ($names as $name) {
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
    echo "   → ИЗМЕНИТЬ на: Руководитель проектов\n\n";
}

echo str_repeat("=", 70) . "\n";
echo "НАЧИНАЮ ОБНОВЛЕНИЕ...\n\n";

foreach ($users as $i => $user) {
    $userId = $user['user_id'];
    $data = $user['data'];
    
    // Изменяем функциональное направление
    $data['functional_direction'] = 'Руководитель проектов';
    
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    
    $query = "UPDATE ozna_data SET data = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $jsonData, $userId);
    
    echo ($i + 1) . ". {$user['fio']}... ";
    
    if ($stmt->execute()) {
        echo "✅ Обновлено\n";
    } else {
        echo "❌ Ошибка обновления\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ ОБНОВЛЕНИЕ ЗАВЕРШЕНО!\n";
echo "Обновлено пользователей: " . count($users) . "\n";
