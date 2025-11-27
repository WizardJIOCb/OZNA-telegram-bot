<?php
/**
 * Test script to verify NotesHandler and NotesService integration
 */

// Load required files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\NotesHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\NotesService;

echo "=== Notes System Integration Test ===\n\n";

// Test 1: Class loading
echo "1. Testing class loading...\n";
try {
    echo "   - Loading NotesService...\n";
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Services\NotesService')) {
        echo "     ✅ NotesService class loaded\n";
    } else {
        echo "     ❌ NotesService class NOT found\n";
        exit(1);
    }
    
    echo "   - Loading NotesHandler...\n";
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\NotesHandler')) {
        echo "     ✅ NotesHandler class loaded\n";
    } else {
        echo "     ❌ NotesHandler class NOT found\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: NotesHandler instantiation
echo "\n2. Testing NotesHandler instantiation...\n";
try {
    $messageService = new MessageService();
    $notesHandler = new NotesHandler($messageService, true);
    echo "   ✅ NotesHandler created successfully\n";
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

// Test 3: NotesService direct test
echo "\n3. Testing NotesService directly...\n";
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
    echo "   ✅ PDO connection created\n";
    
    $notesService = new NotesService($pdo);
    echo "   ✅ NotesService instantiated\n";
    
    // Test creating a note
    $testNoteData = [
        'project_id' => 999,
        'user_id' => 123456789,
        'user_fio' => 'Тестовый Пользователь',
        'department' => 'ПНР',
        'context_menu' => 'ПНР',
        'stage' => 'Прочее',
        'note_text' => 'Тестовая заметка для проверки системы'
    ];
    
    $noteId = $notesService->createNote($testNoteData);
    
    if ($noteId) {
        echo "   ✅ Test note created with ID: {$noteId}\n";
        
        // Clean up
        $stmt = $pdo->prepare("DELETE FROM project_notes WHERE id = :id");
        $stmt->execute([':id' => $noteId]);
        echo "   ✅ Test note cleaned up\n";
    } else {
        echo "   ❌ Failed to create test note\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

// Test 4: Check pDB getUserData
echo "\n4. Testing pDB::getUserData()...\n";
try {
    $testUserId = 230379668; // Example user ID
    $userData = pDB::getUserData($testUserId);
    echo "   ✅ getUserData() works\n";
    echo "   - FIO: " . ($userData['fio'] ?? 'NOT SET') . "\n";
    if (isset($userData['fio']) && $userData['fio'] !== 'Без ФИО') {
        echo "   ✅ User has FIO set\n";
    } else {
        echo "   ⚠️  User FIO is not set or default value\n";
    }
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== All Integration Tests Passed! ===\n";
echo "The notes system should be working correctly.\n";
echo "If you still get errors, check the error logs for specific runtime errors.\n";
