<?php
// Check user table structure and data for user 4225504

try {
    $host = '82.146.42.213';
    $port = 3306;
    $dbname = 'tg_bot';
    $username = 'WizardJIOCb';
    $password = '7qQ#28051961';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check table structure
    echo "=== USER TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE user");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Column: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Default: {$row['Default']}\n";
    }
    
    // Check specific user data
    echo "\n=== USER 4225504 DATA ===\n";
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([4225504]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        foreach ($user as $key => $value) {
            echo "$key: " . ($value ?? 'NULL') . "\n";
        }
    } else {
        echo "User 4225504 not found!\n";
    }
    
    // Check if control_projects column exists
    echo "\n=== CHECKING control_projects COLUMN ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM user LIKE 'control_projects'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "control_projects column exists: " . print_r($column, true);
    } else {
        echo "control_projects column does NOT exist!\n";
        
        echo "Adding control_projects column...\n";
        $pdo->exec("ALTER TABLE user ADD COLUMN control_projects TEXT NULL");
        echo "Column added successfully!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>