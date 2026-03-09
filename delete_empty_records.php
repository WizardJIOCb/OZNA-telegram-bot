<?php

// Delete empty PNR records without Level 2 (work type)
// These are invalid records that should not be counted

require_once __DIR__ . '/vendor/autoload.php';

$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

$conn = new mysqli($host, $username, $password, $database);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Подключение к базе успешно\n\n";

// Find and display records without Level 2 for project 815-01
$projectCode = '815-01';

// Find records from user 5499115173 (Nochnoy) for project 815-01
$userId = 5499115173;

$stmt = $conn->prepare("
    SELECT ot.id, ot.user_id, ot.message
    FROM ozna_tabel ot
    WHERE ot.user_id = ?
    AND ot.message LIKE CONCAT('%', ?, '%')
    ORDER BY ot.id
");

$stmt->bind_param('is', $userId, $projectCode);
$stmt->execute();
$result = $stmt->get_result();

echo "Найденные записи без Уровень 2 (без типа работ):\n";
echo "===========================================\n\n";

$recordsToDelete = [];

while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}\n";
    echo "User ID: {$row['user_id']}\n";
    echo "Message: {$row['message']}\n";
    echo "---\n\n";
    
    $recordsToDelete[] = $row['id'];
}

if (empty($recordsToDelete)) {
    echo "Нет записей для удаления.\n";
    $conn->close();
    exit(0);
}

echo "Всего найдено записей: " . count($recordsToDelete) . "\n\n";

// Ask for confirmation
echo "Вы хотите удалить эти записи? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if ($line !== 'yes') {
    echo "Удаление отменено.\n";
    $conn->close();
    exit(0);
}

// Delete records
$placeholders = implode(',', array_fill(0, count($recordsToDelete), '?'));
$deleteQuery = "DELETE FROM ozna_tabel WHERE id IN ($placeholders)";
$stmt = $conn->prepare($deleteQuery);

// Bind parameters dynamically
$types = str_repeat('i', count($recordsToDelete));
$stmt->bind_param($types, ...$recordsToDelete);

if ($stmt->execute()) {
    echo "\n✅ Успешно удалено записей: " . $stmt->affected_rows . "\n";
} else {
    echo "\n❌ Ошибка при удалении: " . $stmt->error . "\n";
}

$conn->close();
