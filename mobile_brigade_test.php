<?php
// Simple test to verify mobile brigade button functionality

// Include necessary files
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/AdminService.php';
require_once 'CustomCommands/Handlers/TabelHandler.php';

// Mock the necessary classes for testing
class MockDatabaseService {
    public function getUserAccessLevel($userId) {
        return 3; // Regular user with access
    }
    
    public function hasRecentTabelEntry($userId, $isSuperAdmin) {
        return false; // No recent entry
    }
}

class MockAdminService {
    public function getUserAccessLevel($userId) {
        return 3; // Regular user with access
    }
    
    public function isSuperAdmin($userId) {
        return false;
    }
}

class MockProjectSelector {
    public function isDateSelection($category) {
        return false;
    }
    
    public function isProjectSelection($category) {
        return false;
    }
}

class MockTabelEntry {
    public function getConfirmationMessage($activityType, $projectCode) {
        return "Подтвердите запись: $activityType для проекта: $projectCode";
    }
    
    public function addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason = '') {
        return true;
    }
    
    public function createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') {
        return "Запись создана для $fio на $date";
    }
    
    public function formatRecentEntryError($recentEntry) {
        return "Ошибка: повторная запись";
    }
}

class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "Отправка сообщения в чат $chatId:\n$text\n";
        if ($keyboard) {
            echo "Клавиатура:\n";
            foreach ($keyboard as $rowIndex => $row) {
                echo "  Ряд $rowIndex: ";
                foreach ($row as $button) {
                    echo "[{$button['text']}] ";
                }
                echo "\n";
            }
        }
        echo "\n";
        // Return a mock ServerResponse
        return new class {
            public function isOk() { return true; }
        };
    }
    
    public function createWorkTypeKeyboard(): array {
        return [
            [
                ['text' => '💼 Мобильная бригада', 'callback_data' => 'category_Табель_Мобильная_Бригада']
            ],
            [
                ['text' => '🔧 Отдел ШМР', 'callback_data' => 'category_Табель_Шеф_монтаж']
            ],
            [
                ['text' => '⚙️ Отдел ПНР', 'callback_data' => 'category_Табель_Пусконаладка']
            ]
        ];
    }
    
    public function createDateSelectionKeyboard($prefix = 'category_Табель'): array {
        return [
            [
                ['text' => 'Сегодня', 'callback_data' => $prefix . '_Сегодня']
            ],
            [
                ['text' => 'Вчера', 'callback_data' => $prefix . '_Вчера']
            ]
        ];
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

// Test the mobile brigade functionality
echo "Тестирование функциональности мобильной бригады...\n\n";

// Create mock services
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

// Create TabelHandler
$tabelHandler = new Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
    $databaseService,
    $messageService,
    $projectSelector,
    $tabelEntry,
    $adminService
);

// Test if TabelHandler can handle the mobile brigade callback
$callbackData = 'category_Табель_Мобильная_Бригада';
echo "Проверка, может ли TabelHandler обработать callback: $callbackData\n";
$canHandle = $tabelHandler->canHandle($callbackData);
echo "Результат: " . ($canHandle ? 'ДА' : 'НЕТ') . "\n\n";

// Test the routing
if ($canHandle) {
    echo "Тестирование маршрутизации...\n";
    
    // Mock callback query
    $callbackQuery = new stdClass();
    $callbackQuery->message = new stdClass();
    $callbackQuery->message->chat = new stdClass();
    $callbackQuery->message->chat->id = 123456789;
    $callbackQuery->from = new stdClass();
    $callbackQuery->from->id = 987654321;
    $callbackQuery->getData = function() use ($callbackData) {
        return $callbackData;
    };
    
    // Category would be extracted as "Табель_Мобильная_Бригада"
    $category = 'Табель_Мобильная_Бригада';
    $conversation = new MockConversation();
    
    echo "Вызов обработчика для категории: $category\n";
    // This should call handleMobileBrigadeDateSelection
    $result = $tabelHandler->handle($callbackQuery, $category, $conversation);
    echo "Обработка завершена.\n";
}

echo "\nТест завершен.\n";