<?php
/**
 * Diagnostic script to simulate note creation and identify SQL issues
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\NotesService;

echo "=== Note Creation Diagnostic ===\n\n";

// Test 1: PDO Connection
echo "1. Testing PDO connection...\n";
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
    echo "   ✅ PDO connection successful\n\n";
} catch (Exception $e) {
    echo "   ❌ PDO connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Table exists
echo "2. Checking project_notes table...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'project_notes'");
    if ($stmt->rowCount() > 0) {
        echo "   ✅ Table exists\n\n";
    } else {
        echo "   ❌ Table does NOT exist!\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Create NotesService and test with REAL user data
echo "3. Testing NotesService with realistic data...\n";
try {
    $notesService = new NotesService($pdo);
    
    // Get a real user's FIO
    $testUserId = 230379668; // From your previous tests
    $userData = pDB::getUserData($testUserId);
    $userFio = $userData['fio'] ?? 'Test User';
    
    echo "   Using user ID: {$testUserId}\n";
    echo "   User FIO: {$userFio}\n";
    
    // Create test note with realistic data
    $testNoteData = [
        'project_id' => 1,  // Using project ID 1 (should exist)
        'user_id' => $testUserId,
        'user_fio' => $userFio,
        'department' => 'ПНР',
        'context_menu' => 'ПНР',
        'stage' => 'Прочее',
        'note_text' => 'Диагностическая заметка - проверка системы ' . date('Y-m-d H:i:s')
    ];
    
    echo "   Note data prepared:\n";
    echo "   " . json_encode($testNoteData, JSON_UNESCAPED_UNICODE) . "\n";
    
    $noteId = $notesService->createNote($testNoteData);
    
    if ($noteId) {
        echo "   ✅ Note created successfully with ID: {$noteId}\n";
        
        // Verify it exists
        $stmt = $pdo->prepare("SELECT * FROM project_notes WHERE id = :id");
        $stmt->execute([':id' => $noteId]);
        $note = $stmt->fetch();
        
        if ($note) {
            echo "   ✅ Note verified in database:\n";
            echo "      - ID: {$note['id']}\n";
            echo "      - Project: {$note['project_id']}\n";
            echo "      - User: {$note['user_fio']}\n";
            echo "      - Stage: {$note['stage']}\n";
            echo "      - Status: {$note['status']}\n";
            
            // Clean up
            $stmt = $pdo->prepare("DELETE FROM project_notes WHERE id = :id");
            $stmt->execute([':id' => $noteId]);
            echo "   ✅ Test note cleaned up\n\n";
        }
    } else {
        echo "   ❌ Note creation FAILED!\n";
        echo "   NotesService::createNote() returned FALSE\n";
        echo "   Check error_log for 'NotesService::createNote error'\n\n";
        
        // Try manual insert to see exact error
        echo "4. Attempting manual INSERT to see exact SQL error...\n";
        try {
            $sql = "INSERT INTO project_notes 
                    (project_id, user_id, user_fio, department, context_menu, stage, note_text, status) 
                    VALUES 
                    (:project_id, :user_id, :user_fio, :department, :context_menu, :stage, :note_text, 'active')";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                ':project_id' => $testNoteData['project_id'],
                ':user_id' => $testNoteData['user_id'],
                ':user_fio' => $testNoteData['user_fio'],
                ':department' => $testNoteData['department'],
                ':context_menu' => $testNoteData['context_menu'],
                ':stage' => $testNoteData['stage'],
                ':note_text' => $testNoteData['note_text']
            ]);
            
            if ($result) {
                $manualNoteId = $pdo->lastInsertId();
                echo "   ✅ Manual INSERT succeeded with ID: {$manualNoteId}\n";
                
                // Clean up
                $stmt = $pdo->prepare("DELETE FROM project_notes WHERE id = :id");
                $stmt->execute([':id' => $manualNoteId]);
                echo "   ✅ Manual note cleaned up\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Manual INSERT failed with error:\n";
            echo "   " . $e->getMessage() . "\n";
            echo "   Error Code: " . $e->getCode() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
echo "If note creation failed, the error details above should indicate the problem.\n";
