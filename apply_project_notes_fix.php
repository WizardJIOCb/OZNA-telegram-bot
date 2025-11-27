<?php
/**
 * Apply the fix to change project_id from INT to VARCHAR
 */

echo "=== Fixing project_notes Table ===\n\n";

try {
    $pdo = new PDO(
        'mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4',
        'WizardJIOCb',
        '7qQ#28051961',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "1. Checking current table structure...\n";
    $stmt = $pdo->query("DESCRIBE project_notes");
    $columns = $stmt->fetchAll();
    
    $projectIdColumn = null;
    foreach ($columns as $col) {
        if ($col['Field'] === 'project_id') {
            $projectIdColumn = $col;
            echo "   Current project_id type: {$col['Type']}\n";
            break;
        }
    }
    
    if (!$projectIdColumn) {
        echo "   ❌ ERROR: project_id column not found!\n";
        exit(1);
    }
    
    if (strpos($projectIdColumn['Type'], 'int') !== false) {
        echo "   ℹ️  project_id is currently INT, needs to be changed to VARCHAR\n\n";
        
        echo "2. Changing column type...\n";
        $sql = "ALTER TABLE project_notes 
                MODIFY COLUMN project_id VARCHAR(50) NOT NULL COMMENT 'Код проекта (например: ХХХ, ОИ834)'";
        
        $pdo->exec($sql);
        echo "   ✅ Column type changed successfully!\n\n";
        
        // Verify
        echo "3. Verifying change...\n";
        $stmt = $pdo->query("DESCRIBE project_notes");
        $columns = $stmt->fetchAll();
        
        foreach ($columns as $col) {
            if ($col['Field'] === 'project_id') {
                echo "   New project_id type: {$col['Type']}\n";
                
                if (strpos($col['Type'], 'varchar') !== false) {
                    echo "   ✅ Successfully changed to VARCHAR!\n";
                } else {
                    echo "   ❌ ERROR: Type not changed correctly\n";
                }
                break;
            }
        }
    } else {
        echo "   ✅ project_id is already VARCHAR, no change needed\n";
    }
    
    echo "\n=== Fix Applied Successfully! ===\n";
    echo "The notes system can now accept project codes like 'ХХХ', 'ОИ834', etc.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
