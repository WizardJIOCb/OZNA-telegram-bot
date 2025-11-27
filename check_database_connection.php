<?php

// Check database connection
try {
    $host = '82.146.42.213';
    $port = 3306;
    $dbname = 'tg_bot';
    $username = 'WizardJIOCb';
    $password = '7qQ#28051961';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "Query result: " . print_r($result, true) . "\n";
    
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}