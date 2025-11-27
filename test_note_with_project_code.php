<?php
/**
 * Test note creation with project CODE (string like 'ХХХ')
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\NotesService;

echo "=== Testing Note Creation with Project CODE ===\n\n";

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
    
    $notesService = new NotesService($pdo);
    
    // Test with project CODE (string)
    $testNoteData = [
        'project_id' => 'ХХХ',  // Project CODE, not numeric ID
        'user_id' => 5261465966,
        'user_fio' => 'Тест тест тест',
        'department' => 'Проект',
        'context_menu' => 'Проект',
        'stage' => 'Автоматизация',
        'note_text' => 'ТЕСТ заметка с кодом проекта ХХХ'
    ];
    
    echo "Creating note with project CODE 'ХХХ'...\n";
    echo "Data: " . json_encode($testNoteData, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    $noteId = $notesService->createNote($testNoteData);
    
    if ($noteId) {
        echo "✅ SUCCESS! Note created with ID: {$noteId}\n\n";
        
        // Verify
        $stmt = $pdo->prepare("SELECT * FROM project_notes WHERE id = :id");
        $stmt->execute([':id' => $noteId]);
        $note = $stmt->fetch();
        
        echo "Verified note in database:\n";
        echo "  - ID: {$note['id']}\n";
        echo "  - Project ID: {$note['project_id']}\n";
        echo "  - User: {$note['user_fio']}\n";
        echo "  - Department: {$note['department']}\n";
        echo "  - Stage: {$note['stage']}\n";
        echo "  - Text: {$note['note_text']}\n";
        echo "  - Status: {$note['status']}\n";
        
        // Clean up
        $stmt = $pdo->prepare("DELETE FROM project_notes WHERE id = :id");
        $stmt->execute([':id' => $noteId]);
        echo "\n✅ Test note cleaned up\n";
        
        echo "\n🎉 SUCCESS! The notes system now works with project CODES!\n";
    } else {
        echo "❌ FAILED to create note\n";
        echo "Check error_log for details\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
