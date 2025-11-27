<?php
/**
 * Diagnostic script to test notes database connection and table
 */

echo "=== Testing Notes Database ===\n\n";

try {
    // Test PDO connection
    echo "1. Testing PDO connection...\n";
    $pdo = new PDO(
        'mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4',
        'WizardJIOCb',
        '7qQ#28051961',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    echo "   ✅ PDO connection successful\n\n";
    
    // Check if table exists
    echo "2. Checking if project_notes table exists...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'project_notes'");
    $table = $stmt->fetch();
    
    if ($table) {
        echo "   ✅ Table 'project_notes' exists\n\n";
        
        // Show table structure
        echo "3. Table structure:\n";
        $stmt = $pdo->query("DESCRIBE project_notes");
        $columns = $stmt->fetchAll();
        foreach ($columns as $col) {
            echo "   - {$col['Field']} ({$col['Type']})\n";
        }
        echo "\n";
        
        // Count existing notes
        echo "4. Counting existing notes...\n";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM project_notes");
        $result = $stmt->fetch();
        echo "   Total notes in database: {$result['count']}\n\n";
        
        // Test insert (then delete)
        echo "5. Testing INSERT operation...\n";
        $testData = [
            'project_id' => 999,
            'user_id' => 123456789,
            'user_fio' => 'Test User',
            'department' => 'ПНР',
            'context_menu' => 'ПНР',
            'stage' => 'Прочее',
            'note_text' => 'This is a test note'
        ];
        
        $sql = "INSERT INTO project_notes 
                (project_id, user_id, user_fio, department, context_menu, stage, note_text, status) 
                VALUES 
                (:project_id, :user_id, :user_fio, :department, :context_menu, :stage, :note_text, 'active')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':project_id' => $testData['project_id'],
            ':user_id' => $testData['user_id'],
            ':user_fio' => $testData['user_fio'],
            ':department' => $testData['department'],
            ':context_menu' => $testData['context_menu'],
            ':stage' => $testData['stage'],
            ':note_text' => $testData['note_text']
        ]);
        
        $testNoteId = $pdo->lastInsertId();
        echo "   ✅ Test note created with ID: {$testNoteId}\n";
        
        // Delete test note
        echo "6. Cleaning up test note...\n";
        $stmt = $pdo->prepare("DELETE FROM project_notes WHERE id = :id");
        $stmt->execute([':id' => $testNoteId]);
        echo "   ✅ Test note deleted\n\n";
        
        echo "=== All Tests Passed! ===\n";
        echo "The database and table are working correctly.\n";
        echo "If notes creation still fails, check the error logs for PDO connection issues in NotesHandler.\n";
        
    } else {
        echo "   ❌ ERROR: Table 'project_notes' does NOT exist!\n";
        echo "   Please run the migration script: NOTES_SYSTEM_MIGRATION.sql\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}
