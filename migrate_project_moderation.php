<?php
/**
 * Migration script to create project_moderation table
 */

echo "=== Project Moderation Table Migration ===\n\n";

// Database connection
$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✓ Database connection successful\n\n";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/database/migrations/create_project_moderation_table.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove comments from SQL
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Split by semicolon to execute multiple statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $index => $statement) {
        if (empty($statement)) {
            continue;
        }
        
        echo "Executing statement " . ($index + 1) . "...\n";
        
        if ($conn->query($statement) === TRUE) {
            echo "✓ Statement executed successfully\n";
        } else {
            echo "✗ Error: " . $conn->error . "\n";
        }
    }
    
    echo "\n=== Checking table creation ===\n";
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'project_moderation'");
    
    if ($result->num_rows > 0) {
        echo "✓ Table 'project_moderation' created successfully!\n\n";
        
        // Show table structure
        echo "Table structure:\n";
        $result = $conn->query("DESCRIBE project_moderation");
        
        while ($row = $result->fetch_assoc()) {
            echo "  - {$row['Field']} ({$row['Type']})\n";
        }
        
        echo "\n✓ Migration completed successfully!\n";
    } else {
        echo "✗ Table 'project_moderation' was not created\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
