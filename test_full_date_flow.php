<?php

// Comprehensive test to simulate the full date selection flow

// Simulate the core functionality without database dependencies
class MockRequest {
    public static function sendMessage($data) {
        echo "MockRequest: sendMessage called with data:\n";
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        return new MockServerResponse();
    }
    
    public static function emptyResponse() {
        return new MockServerResponse();
    }
}

class MockServerResponse {
    public function isOk() {
        return true;
    }
    
    public function getDescription() {
        return "Success";
    }
    
    public function getErrorCode() {
        return 0;
    }
}

// Override the Request class for testing
class_alias('MockRequest', 'Longman\TelegramBot\Request');

// Simulate the ProjectSelector class
class ProjectSelector {
    private function fixKnownCyrillicPatterns($str) {
        // Replace the known corrupted patterns
        $replacements = [
            'РЁРёС„СЂРџСЂРѕРµРєС‚Р°' => 'ШифрПроекта',
            'РћР834' => 'ОИ834',
            'РћР651' => 'ОИ651',
            'РћР696' => 'ОИ696',
            'РћР795' => 'ОИ795',
            'РћР810' => 'ОИ810',
            'РћР768' => 'ОИ768',
            'РћР837' => 'ОИ837',
            'РћР562' => 'ОИ562',
            'РћР788' => 'ОИ788',
            'РћР860' => 'ОИ860',
            'РћР862' => 'ОИ862',
            'Р‘РљРќРЎ115' => 'БКНС115',
            'Р‘РљРќРЎ120' => 'БКНС120',
            'Р”Р°С‚Р°' => 'Дата',
            'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
            'Р’С‡РµСЂР°' => 'Вчера'
        ];
        
        foreach ($replacements as $corrupted => $fixed) {
            $str = str_replace($corrupted, $fixed, $str);
        }
        
        return $str;
    }
    
    public function extractDate(string $category): ?string {
        // Fix encoding issues with category
        $category = $this->fixKnownCyrillicPatterns($category);
        
        if (str_contains($category, '_Сегодня')) {
            return date("d.m.Y");
        }
        
        if (str_contains($category, '_Вчера')) {
            return date("d.m.Y", time() - 86400);
        }
        
        if (str_contains($category, '_Позавчера')) {
            return date("d.m.Y", time() - 172800);
        }
        
        return null;
    }

    public function isDateSelection(string $category): bool {
        // Fix encoding issues with category
        $category = $this->fixKnownCyrillicPatterns($category);
        
        $result = str_contains($category, '_Сегодня') || 
                  str_contains($category, '_Вчера') || 
                  str_contains($category, '_Позавчера') || 
                  str_contains($category, '_ДругаяДата');
        echo "ProjectSelector isDateSelection called with category: {$category}, result: " . ($result ? 'true' : 'false') . "\n";
        return $result;
    }
    
    public function extractProjectCode(string $category): ?string {
        if (!str_contains($category, 'ШифрПроекта_')) {
            return null;
        }

        $parts = explode('_', $category);
        return end($parts);
    }
    
    public function isProjectSelection(string $category): bool {
        return str_contains($category, 'ШифрПроекта_');
    }
}

// Simulate the MessageService
class MessageService {
    public function createProjectSelectionKeyboard(): array {
        return [
            [
                ['text' => 'ОИ834', 'callback_data' => 'category_Табель_ШифрПроекта_ОИ834']
            ],
            [
                ['text' => 'ОИ651', 'callback_data' => 'category_Табель_ШифрПроекта_ОИ651']
            ]
        ];
    }
    
    public function sendMessage(string $chatId, string $text, array $keyboard = null): MockServerResponse {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard], JSON_UNESCAPED_UNICODE);
            echo "MessageService sendMessage keyboard JSON: " . $data['reply_markup'] . "\n";
        }
        
        echo "MessageService sendMessage data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
        return MockRequest::sendMessage($data);
    }
}

// Simulate the TabelHandler
class TabelHandler {
    private $projectSelector;
    private $messageService;
    
    public function __construct($projectSelector, $messageService) {
        $this->projectSelector = $projectSelector;
        $this->messageService = $messageService;
    }
    
    public function handleDateSelection($callbackQuery, string $category, $conversation): MockServerResponse {
        echo "TabelHandler: handleDateSelection called with category: {$category}\n";
        
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $date = $this->projectSelector->extractDate($category);
        
        // Handle "Other date" selection
        if (str_contains($category, '_ДругаяДата')) {
            echo "TabelHandler: handling 'Other date' selection\n";
            // For "Other date", we need to ask the user to input a date
            $conversation->notes['awaiting_date_input'] = true;
            $conversation->update();
            
            $text = "Пожалуйста, введите дату в формате ДД.ММ.ГГГГ (например, 25.12.2023):";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        if ($date) {
            echo "TabelHandler: extracted date: {$date}\n";
            $conversation->notes['tabel_data'] = $date;
            
            // Save if this is current activity flow
            if (str_contains($category, 'Текущая')) {
                $conversation->notes['is_current_activity'] = true;
                $conversation->notes['project_code'] = 'Текущая деятельность';
                $conversation->update();
                
                echo "TabelHandler: handling current activity flow\n";
                // For this test, we'll just return a success response
                return $this->messageService->sendMessage($chatId, "<b>💼 Табель - Текущая деятельность за {$date}</b>");
            } elseif (str_contains($category, 'Пусконаладка') || str_contains($category, 'ПНЛ')) {
                // Для Пусконаладки сразу переходим к выбору проекта
                $conversation->notes['is_commissioning'] = true;
                $conversation->notes['is_supervision'] = false;
                $conversation->notes['is_current_activity'] = false;
                $conversation->update();
                
                echo "TabelHandler: handling commissioning flow\n";
                $text = "<b>⚙️ Табель Пусконаладка за {$date}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } elseif (str_contains($category, 'Шеф-монтаж') || str_contains($category, 'ШМ')) {
                // Для Шеф-монтажа сразу переходим к выбору проекта
                $conversation->notes['is_supervision'] = true;
                $conversation->notes['is_commissioning'] = false;
                $conversation->notes['is_current_activity'] = false;
                $conversation->update();
                
                echo "TabelHandler: handling supervision flow\n";
                $text = "<b>🔧 Табель Шеф-монтаж за {$date}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } elseif (str_contains($category, 'Мобильная_Бригада') || str_contains($category, 'МобБриг')) {
                // Для мобильной бригады сбрасываем флаги
                $conversation->notes['is_current_activity'] = false;
                $conversation->notes['is_commissioning'] = false;
                $conversation->notes['is_supervision'] = false;
                $conversation->update();
                
                echo "TabelHandler: handling mobile brigade flow\n";
                $text = "<b>Табель за {$date}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } else {
                // Для мобильной бригады сбрасываем флаги
                $conversation->notes['is_current_activity'] = false;
                $conversation->notes['is_commissioning'] = false;
                $conversation->notes['is_supervision'] = false;
                $conversation->update();
                
                echo "TabelHandler: handling default flow\n";
                $text = "<b>Табель за {$date}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
        }
        
        echo "TabelHandler: date extraction failed\n";
        return $this->messageService->sendMessage($chatId, 'Ошибка выбора даты');
    }
}

// Mock classes for testing
class MockChat {
    private $id;
    
    public function __construct($id = 123456789) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
}

class MockMessage {
    private $chat;
    
    public function __construct() {
        $this->chat = new MockChat();
    }
    
    public function getChat() {
        return $this->chat;
    }
}

class MockUser {
    private $id;
    
    public function __construct($id = 987654321) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
}

class MockCallbackQuery {
    private $data;
    private $message;
    private $user;
    
    public function __construct($data = 'category_Табель_Сегодня') {
        $this->data = $data;
        $this->message = new MockMessage();
        $this->user = new MockUser();
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getFrom() {
        return $this->user;
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
        echo "MockConversation: updated with notes: " . json_encode($this->notes, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

echo "Testing full date selection flow...\n\n";

// Test the full flow
echo "=== Test 1: Today date selection ===\n";
$callbackData = 'category_Табель_Сегодня';
$callbackQuery = new MockCallbackQuery($callbackData);
$conversation = new MockConversation();

echo "Input callback data: {$callbackData}\n";

// Initialize services
$projectSelector = new ProjectSelector();
$messageService = new MessageService();
$tabelHandler = new TabelHandler($projectSelector, $messageService);

// Process the date selection
echo "\n--- Processing date selection ---\n";
$result = $tabelHandler->handleDateSelection($callbackQuery, $callbackData, $conversation);

echo "\n=== Test 2: Other date selection ===\n";
$callbackData = 'category_Табель_ДругаяДата';
$callbackQuery = new MockCallbackQuery($callbackData);
$conversation = new MockConversation();

echo "Input callback data: {$callbackData}\n";

echo "\n--- Processing other date selection ---\n";
$result = $tabelHandler->handleDateSelection($callbackQuery, $callbackData, $conversation);

echo "\n=== Test completed ===\n";