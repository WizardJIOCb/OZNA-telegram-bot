<?php
require_once 'vendor/autoload.php';

$conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Connected successfully\n";

// Insert test export log using direct query
$userId = 123456789;
$userFio = $conn->real_escape_string('Тестовый Пользователь');
$exportType = 'report';
$exportScope = $conn->real_escape_string('Простой отчёт (Все ФН)');
$recordsCount = 657;
$fileName = 'test_export.csv';

$query = "INSERT INTO export_logs 
          (user_id, user_fio, export_type, export_scope, project_code, 
           period_start, period_end, status_filter, records_count, file_name) 
          VALUES ({$userId}, '{$userFio}', '{$exportType}', '{$exportScope}', NULL, 
                  '2025-10-01 00:00:00', '2025-10-14 23:59:59', NULL, {$recordsCount}, '{$fileName}')";

if ($conn->query($query)) {
    echo "Test export log inserted successfully\n";
    echo "Insert ID: " . $conn->insert_id . "\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

// Now check if we can read it back
echo "\n--- Reading back export logs ---\n";
$result = $conn->query("SELECT * FROM export_logs ORDER BY exported_at DESC LIMIT 5");

if ($result) {
    echo "Found " . $result->num_rows . " records\n\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}\n";
        echo "User: {$row['user_fio']} (ID: {$row['user_id']})\n";
        echo "Type: {$row['export_type']}\n";
        echo "Scope: {$row['export_scope']}\n";
        echo "Records: {$row['records_count']}\n";
        echo "Date: {$row['exported_at']}\n";
        echo "---\n";
    }
} else {
    echo "Error reading: " . $conn->error . "\n";
}

$conn->close();
