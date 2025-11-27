<?php
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/MessageService.php';

// Mock the database service
class MockDatabaseService {
    public function addUserTabel($userId, $message) {
        return true;
    }
    
    public function hasRecentTabelEntry($userId, $isSuperAdmin = false) {
        return null;
    }
    
    public function getAllUsers() {
        return [];
    }
}

// Mock the message service
class MockMessageService {
    public function sendToHistoryChatByFunctionalDirection($message, $functionalDirection) {
        // Do nothing
    }
    
    public function sendMessage($chatId, $text, $keyboard = null) {
        // Do nothing
    }
}

// Test the fix
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);

// Test the getConfirmationMessage method with the Прочее_ПроектнаяДеятельность activity type
$confirmationMessage = $tabelEntry->getConfirmationMessage('Прочее_ПроектнаяДеятельность', 'ОИ833');
echo "Confirmation message: " . $confirmationMessage . "\n";

// Also test with a custom description like in the user's example
// For this we need to look at how the createTabelMessage method works with Прочее_ПроектнаяДеятельность
$tabelMessage = $tabelEntry->createTabelMessage('Тестовый Пользователь', '19.09.2025', 'ОИ833', 'Прочее_ПроектнаяДеятельность', 'Тест');
echo "Tabel message: " . $tabelMessage . "\n";
?>