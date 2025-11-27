<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();
$conn = $dbService->getConnection();

echo "=== ПОИСК ПОЛЬЗОВАТЕЛЕЙ БЕЗ ФИО ===\n\n";

// Ищем всех пользователей ПНР
$query = "SELECT user_id, data FROM ozna_data WHERE JSON_EXTRACT(data, '$.functional_direction') = 'Пусконаладка'";
$result = $conn->query($query);

$noFioUsers = [];

while ($row = $result->fetch_assoc()) {
    $data = json_decode($row['data'], true);
    $fio = $data['fio'] ?? '';
    
    // Проверяем пустое ФИО или "Без ФИО"
    if (empty($fio) || trim($fio) === '' || $fio === 'Без ФИО') {
        $noFioUsers[] = [
            'user_id' => $row['user_id'],
            'fio' => $fio,
            'data' => $data
        ];
    }
}

echo "Найдено пользователей без ФИО: " . count($noFioUsers) . "\n\n";

if (empty($noFioUsers)) {
    echo "✅ Пользователей без ФИО не найдено!\n";
    exit;
}

foreach ($noFioUsers as $i => $user) {
    echo ($i + 1) . ". User ID: {$user['user_id']}\n";
    echo "   ФИО: '" . ($user['fio'] ?: '(пусто)') . "'\n";
    echo "   ФН: " . ($user['data']['functional_direction'] ?? 'не указано') . "\n";
    
    // Проверяем есть ли записи в табеле
    $query = "SELECT COUNT(*) as count FROM ozna_tabel WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $tabelCount = $result->fetch_assoc()['count'];
    
    echo "   Записей в табеле: $tabelCount\n";
    
    // Проверяем есть ли заметки
    $query = "SELECT COUNT(*) as count FROM ozna_notes WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $notesCount = $result->fetch_assoc()['count'];
    
    echo "   Заметок: $notesCount\n";
    echo "\n";
}

echo str_repeat("=", 70) . "\n";
echo "ГОТОВ К УДАЛЕНИЮ: " . count($noFioUsers) . " пользователей\n";
