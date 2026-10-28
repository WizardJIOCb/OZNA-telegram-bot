<?php
require_once __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config.php';

$conn = new mysqli(
    $config['mysql']['host'],
    $config['mysql']['user'],
    $config['mysql']['password'],
    $config['mysql']['database']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== project_cards table columns ===\n";
$result = $conn->query("DESCRIBE project_cards");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== Sample data from project_cards ===\n";
$result = $conn->query("SELECT project_code, name_1c FROM project_cards LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['project_code'] . " | name_1c: " . ($row['name_1c'] ?? 'NULL') . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
