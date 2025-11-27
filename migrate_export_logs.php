<?php
require_once 'vendor/autoload.php';

$conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Connected successfully\n";

$sql = file_get_contents('create_export_logs_table.sql');

if ($conn->multi_query($sql)) {
    echo "Table created successfully\n";
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
