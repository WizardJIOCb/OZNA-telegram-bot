<?php
require_once 'config.php';

// Database configuration from the existing DatabaseService
$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

$pdo = new PDO('mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4', 'WizardJIOCb', '7qQ#28051961');
$stmt = $pdo->query('DESCRIBE ozna_tabel');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}

// Check table structure
$result = $conn->query('DESCRIBE ozna_data');

echo "Table structure for ozna_data:\n";
echo "--------------------------------\n";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}

// Check a sample record
$result = $conn->query('SELECT * FROM ozna_data LIMIT 1');
echo "\nSample record:\n";
echo "--------------\n";
if ($row = $result->fetch_assoc()) {
    print_r($row);
    
    if (isset($row['data'])) {
        echo "\nDecoded JSON data:\n";
        echo "------------------\n";
        $data = json_decode($row['data'], true);
        print_r($data);
    }
}

$conn->close();
?>