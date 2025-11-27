<?php
require_once 'Custome/DB.php';

try {
    $conn = new PDO('mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4', 'WizardJIOCb', '7qQ#28051961');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== USER_CHAT TABLE STRUCTURE ===\n";
    $stmt = $conn->prepare('DESCRIBE user_chat');
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . ' - ' . $col['Type'] . "\n";
    }
    
    echo "\n=== SAMPLE DATA ===\n";
    $stmt = $conn->prepare('SELECT * FROM user_chat LIMIT 3');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $i => $row) {
        echo "Row " . ($i+1) . ": " . json_encode($row) . "\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>