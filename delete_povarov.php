<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();
$conn = $dbService->getConnection();

echo "=== УДАЛЕНИЕ ПОЛЬЗОВАТЕЛЯ: ПОВАРОВ ===\n\n";

// Ищем Поварова
$query = "SELECT user_id, data FROM ozna_data WHERE data LIKE '%Поваров%'";
$result = $conn->query($query);

$users = [];
while ($row = $result->fetch_assoc()) {
    $data = json_decode($row['data'], true);
    $fio = $data['fio'] ?? '';
    if (stripos($fio, 'Поваров') !== false) {
        $users[] = [
            'user_id' => $row['user_id'],
            'fio' => $fio,
            'fn' => $data['functional_direction'] ?? 'не указано'
        ];
    }
}

echo "Найдено пользователей: " . count($users) . "\n\n";

if (empty($users)) {
    echo "❌ Поваров не найден!\n";
    exit;
}

foreach ($users as $i => $user) {
    echo ($i + 1) . ". ФИО: {$user['fio']}\n";
    echo "   User ID: {$user['user_id']}\n";
    echo "   ФН: {$user['fn']}\n";
    
    // Проверяем количество записей в табеле
    $query = "SELECT COUNT(*) as count FROM ozna_tabel WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $tabelCount = $result->fetch_assoc()['count'];
    
    echo "   Записей в табеле: $tabelCount\n\n";
}

echo str_repeat("=", 70) . "\n";
echo "НАЧИНАЮ УДАЛЕНИЕ...\n\n";

foreach ($users as $i => $user) {
    $userId = $user['user_id'];
    
    echo ($i + 1) . ". {$user['fio']} (User ID: $userId)\n";
    
    // Удаляем из ozna_data
    $query = "DELETE FROM ozna_data WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        echo "   ✅ Удален из ozna_data\n";
    } else {
        echo "   ❌ Ошибка удаления из ozna_data\n";
    }
    
    // Удаляем записи из табеля
    $query = "DELETE FROM ozna_tabel WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    
    if ($affected > 0) {
        echo "   ✅ Удалено записей из табеля: $affected\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 70) . "\n";
echo "✅ УДАЛЕНИЕ ЗАВЕРШЕНО!\n";
echo "Удалено пользователей: " . count($users) . "\n";
