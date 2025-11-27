<?php
/**
 * Execute Notes System Migration
 * Run this file to create the project_notes table
 */

echo "📝 Notes System Migration Script\n";
echo "================================\n\n";

// Database connection settings
$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

echo "Connecting to database...\n";

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Connected to database\n\n";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/NOTES_SYSTEM_MIGRATION.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "📄 SQL file loaded\n\n";
    
    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^USE/', $stmt);
        }
    );
    
    echo "Executing " . count($statements) . " SQL statements...\n\n";
    
    // Execute each statement
    $success = 0;
    $failed = 0;
    
    foreach ($statements as $index => $statement) {
        try {
            // Skip comments and empty statements
            if (empty(trim($statement)) || strpos(trim($statement), '--') === 0) {
                continue;
            }
            
            $pdo->exec($statement);
            $success++;
            
            // Show progress for CREATE TABLE
            if (stripos($statement, 'CREATE TABLE') !== false) {
                echo "✅ Created table: project_notes\n";
            }
            // Show progress for DESCRIBE
            else if (stripos($statement, 'DESCRIBE') !== false) {
                echo "✅ Verified table structure\n";
                $stmt = $pdo->query($statement);
                $columns = $stmt->fetchAll();
                echo "\nTable columns:\n";
                foreach ($columns as $col) {
                    echo "  - {$col['Field']} ({$col['Type']})\n";
                }
            }
            // Show progress for SELECT
            else if (stripos($statement, 'SELECT') !== false) {
                $stmt = $pdo->query($statement);
                $result = $stmt->fetch();
                if ($result) {
                    echo "✅ " . reset($result) . "\n";
                }
            }
            
        } catch (PDOException $e) {
            $failed++;
            echo "❌ Error in statement " . ($index + 1) . ": " . $e->getMessage() . "\n";
            // Don't stop on errors, continue with other statements
        }
    }
    
    echo "\n================================\n";
    echo "Migration Summary:\n";
    echo "  ✅ Successful: $success\n";
    echo "  ❌ Failed: $failed\n";
    echo "================================\n\n";
    
    // Verify table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'project_notes'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "✅ Table 'project_notes' exists in database\n";
        
        // Show table info
        $stmt = $pdo->query("SHOW INDEX FROM project_notes");
        $indexes = $stmt->fetchAll();
        echo "\n📊 Table indexes:\n";
        foreach ($indexes as $idx) {
            if (!isset($printed[$idx['Key_name']])) {
                echo "  - {$idx['Key_name']}\n";
                $printed[$idx['Key_name']] = true;
            }
        }
        
        echo "\n🎉 Migration completed successfully!\n";
        echo "The notes system is ready to use.\n";
    } else {
        echo "❌ Table 'project_notes' was not created\n";
        echo "Please check the errors above.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";
