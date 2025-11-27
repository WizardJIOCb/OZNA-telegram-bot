<?php
// Debug script to check tabel entries for project

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ815-01';

echo "=== Записи табеля для проекта {$projectCode} ===\n\n";

// First check table structure
$result = $conn->query("SHOW COLUMNS FROM ozna_tabel");
echo "Структура таблицы ozna_tabel:\n";
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} ({$row['Type']})\n";
}
echo "\n";

$stmt = $conn->prepare("
    SELECT * 
    FROM ozna_tabel 
    WHERE message LIKE CONCAT('%', ?, '%')
    ORDER BY id DESC
    LIMIT 5
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    echo "#{$count}\n";
    echo "ID: {$row['id']}\n";
    if (isset($row['date'])) echo "Дата: {$row['date']}\n";
    if (isset($row['created_at'])) echo "Создано: {$row['created_at']}\n";
    echo "Сообщение: {$row['message']}\n";
    echo "---\n\n";
}

echo "\nВсего найдено записей: {$count}\n";

// Check for specific keywords
echo "\n=== Проверка ключевых слов ===\n\n";

$keywords = [
    'ШМР_ВходнойКонтроль_ПодтВех',
    'Табель_ШМР_ВходнойКонтроль',
    'ПодтВех_ЭлектрОб',
    'Табель_ПодтВех_ЭлектрОб',
    'Досборка'
];

foreach ($keywords as $keyword) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as cnt 
        FROM ozna_tabel 
        WHERE message LIKE CONCAT('%', ?, '%', ?, '%')
    ");
    $stmt->bind_param('ss', $projectCode, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo "{$keyword}: {$row['cnt']} записей\n";
}
