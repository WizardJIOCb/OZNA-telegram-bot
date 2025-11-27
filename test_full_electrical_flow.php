<?php
// Full flow test for electrical equipment button

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;

echo "=== Full Electrical Equipment Flow Test ===\n\n";

// Test the specific callback data that's used in the commissioning works menu
$callbackData = 'cat_ТабЭлОб';

echo "Testing callback data: {$callbackData}\n";
echo "Length: " . strlen($callbackData) . " bytes\n";
echo "Hex: " . bin2hex($callbackData) . "\n\n";

// Initialize services
$messageService = new MessageService();
$databaseService = new DatabaseService();
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler(
    $databaseService,
    $messageService,
    new ProjectSelector(),
    new TabelEntry($databaseService, $messageService),
    $adminService
);

// Test with CallbackHandlerFactory
echo "=== Testing with CallbackHandlerFactory ===\n";

$factory = new CallbackHandlerFactory();
$factory->registerHandler($tabelHandler);

// Create a mock callback query
class MockCallbackQuery extends CallbackQuery {
    private $data;
    
    public function __construct($data) {
        parent::__construct([]);
        $this->data = $data;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getMessage() {
        return new class {
            public function getChat() {
                return new class {
                    public function getId() {
                        return 123456789;
                    }
                };
            }
        };
    }
    
    public function getFrom() {
        return new class {
            public function getId() {
                return 987654321;
            }
            public function getFirstName() {
                return 'TestUser';
            }
        };
    }
}

$callbackQuery = new MockCallbackQuery($callbackData);
$conversation = new Conversation(123456789, 987654321);

// Mock the Request class to avoid actual Telegram API calls
class MockRequest {
    public static function sendMessage($data) {
        echo "Mock Request::sendMessage called with data:\n";
        print_r($data);
        return new class {
            public function isOk() {
                return true;
            }
        };
    }
    
    public static function emptyResponse() {
        return new class {
            public function isOk() {
                return true;
            }
        };
    }
}

// Override the Request class methods
Request::class;

// Test the factory handling
echo "Calling CallbackHandlerFactory->handleCallback()...\n";
try {
    $result = $factory->handleCallback($callbackQuery, $callbackData, $conversation);
    echo "Factory handling completed.\n";
    echo "Result isOK: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'YES' : 'NO') : 'UNKNOWN') . "\n";
} catch (Exception $e) {
    echo "Error during factory handling: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n";

// Comprehensive test to simulate the full electrical equipment callback flow

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

// Simulate the CallbackHandlerFactory class with only the methods we need for testing
class CallbackHandlerFactory {
    private $handlers = [];
    
    public function registerHandler($handler) {
        $this->handlers[] = $handler;
        // Sort handlers by priority (highest first)
        usort($this->handlers, function($a, $b) {
            return $b->getPriority() - $a->getPriority();
        });
    }
    
    private function fixKnownCyrillicPatterns($str) {
        // Replace the known corrupted patterns
        $replacements = [
            'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
            'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
            'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
            'Р”Р°С‚Р°' => 'Дата',
            'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
            'Р’С‡РµСЂР°' => 'Вчера',
            'РџСЂРѕС„РёР»СЊ' => 'Профиль',
            'РџСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
            'Р¤РРћ' => 'ФИО',
            'Р¤Рќ' => 'ФН',
            'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
            'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
            'РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
            'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
            'РўР°Р±РµР»СЊ' => 'Табель',
            'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
            'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
            'РџРѕРЅРёР·РёС�СЊ' => 'Понизить',
            // Add missing patterns for commissioning works
            'РџРќР›' => 'ПНЛ',
            'РџСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
            'Р Р°Р±РѕС‚С‹' => 'Работы',
            // Add missing patterns for the main buttons (order matters - more specific first)
            'РЁРµС„_РјРѕРЅС‚Р°Р¶' => 'Шеф_монтаж',
            'РўРµРєСѓС‰Р°СЏ' => 'Текущая',
            // Add individual word patterns
            'РјРѕРЅС‚Р°Р¶' => 'монтаж',
            // Add pattern for commissioning works abbreviation
            'РџРќР ' => 'ПНР',
            // Add pattern for preparation
            'РџРѕРґРіРѕС‚РѕРІРєР°' => 'Подготовка',
            // Add pattern for electrical equipment - this is the key fix
            'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
            // Add pattern for full electrical equipment callback - this is the key fix
            'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'category_Табель_Электрооборудование',
            // Add additional patterns for double-encoded Cyrillic that might be causing issues
            'Табель_Электрооборудование' => 'Табель_Электрооборудование',
            'category_Табель_Электрооборудование' => 'category_Табель_Электрооборудование'
        ];

        // Special case: if string is already the correct electrical equipment callback, return as is
        if ($str === 'category_Табель_Электрооборудование' || $str === 'Табель_Электрооборудование') {
            return $str;
        }

        foreach ($replacements as $corrupted => $fixed) {
            if (strpos($str, $corrupted) !== false) {
                $str = str_replace($corrupted, $fixed, $str);
            }
        }

        return $str;
    }

    private function extractCategory(string $callbackData): string {
        // Handle new short callback_data formats
        if (strpos($callbackData, 'adm_req_') === 0) {
            // For admin request callbacks, return the full callbackData as category
            return $callbackData;
        }

        // Special handling for electrical equipment - check this first and return immediately
        if (strpos($callbackData, 'category_Табель_Электрооборудование') === 0) {
            return 'Табель_Электрооборудование';
        }

        if (strpos($callbackData, 'category_') === 0) {
            $category = substr($callbackData, strlen('category_'));
            
            // Fix encoding for category
            $category = mb_convert_encoding($category, 'UTF-8', 'auto');
            
            // Fix known Cyrillic pattern corruptions for category as well
            $category = $this->fixKnownCyrillicPatterns($category);
            
            // Special case: if category is exactly 'Табель_Электрооборудование', return it immediately
            if ($category === 'Табель_Электрооборудование') {
                return $category;
            }
            
            return $category;
        }
        
        return $callbackData;
    }
    
    public function handleCallback($callbackQuery, $callbackData, $conversation) {
        echo "CallbackHandlerFactory: handleCallback called with callbackData: '{$callbackData}'\n";
        
        // Fix encoding issues with callback data - only once at the beginning
        $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
        
        // Fix known Cyrillic pattern corruptions - only once at the beginning
        $callbackData = $this->fixKnownCyrillicPatterns($callbackData);
        
        // Extract category from callback data
        $category = $this->extractCategory($callbackData);
        
        echo "CallbackHandlerFactory: extracted category: '{$category}'\n";
        
        // Special check for electrical equipment
        if ($category === 'Табель_Электрооборудование' || strpos($callbackData, 'Табель_Электрооборудование') !== false) {
            echo "CallbackHandlerFactory: detected electrical equipment callback\n";
        }
        
        // Log all registered handlers and their priorities
        echo "CallbackHandlerFactory: registered handlers:\n";
        foreach ($this->handlers as $index => $handler) {
            echo "  Handler #{$index}: " . get_class($handler) . " with priority: " . $handler->getPriority() . "\n";
        }

        // Find the appropriate handler
        foreach ($this->handlers as $index => $handler) {
            echo "CallbackHandlerFactory: checking handler #{$index}: " . get_class($handler) . " with priority: " . $handler->getPriority() . "\n";
            // Pass the already processed callback data to the handler
            if ($handler->canHandle($callbackData)) {
                echo "CallbackHandlerFactory: found handler: " . get_class($handler) . "\n";
                // Also pass the already processed category
                $result = $handler->handle($callbackQuery, $category, $conversation);
                echo "CallbackHandlerFactory: handler result: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'success' : 'failed') : 'unknown') . "\n";
                return $result;
            } else {
                echo "CallbackHandlerFactory: handler " . get_class($handler) . " cannot handle callback: '{$callbackData}'\n";
            }
        }

        echo "CallbackHandlerFactory: no handler found for callback data: '{$callbackData}'\n";
        return $this->getDefaultResponse($callbackQuery);
    }
    
    private function getDefaultResponse($callbackQuery) {
        return MockRequest::sendMessage([
            'chat_id' => $callbackQuery->getMessage()->getChat()->getId(),
            'text' => 'Не выбран пункт меню',
        ]);
    }
}

// Simulate the TabelHandler
class TabelHandler {
    private $messageService;
    
    public function __construct($messageService) {
        $this->messageService = $messageService;
    }
    
    public function canHandle(string $callbackData): bool {
        echo "TabelHandler: canHandle called with callbackData: '{$callbackData}'\n";
        
        // Special handling for electrical equipment - check this first
        if (str_starts_with($callbackData, 'category_Табель_Электрооборудование')) {
            echo "TabelHandler: canHandle - special case for electrical equipment: true\n";
            return true;
        }
        
        // Additional check for corrupted electrical equipment callback data
        if (strpos($callbackData, 'Электрооборудование') !== false) {
            echo "TabelHandler: canHandle - found электрооборудование in callback data\n";
            // If it contains электрооборудование, we should handle it
            return true;
        }
        
        $canHandle = str_starts_with($callbackData, 'category_Табель');
        
        echo "TabelHandler: canHandle result for '{$callbackData}': " . ($canHandle ? 'true' : 'false') . "\n";
        return $canHandle;
    }
    
    public function handle($callbackQuery, string $category, $conversation): MockServerResponse {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();

        echo "TabelHandler: handle called with category: {$category}\n";
        echo "TabelHandler: handle called with chatId: {$chatId}\n";
        echo "TabelHandler: handle called with userId: {$userId}\n";
        
        // Log the raw callback data
        $callbackData = $callbackQuery->getData();
        echo "TabelHandler: handle called with raw callbackData: '{$callbackData}'\n";
        
        // Special handling for electrical equipment category
        if ($category === 'Табель_Электрооборудование') {
            echo "TabelHandler: handle - special case for electrical equipment category\n";
            return $this->handleElectricalEquipmentMenu($chatId);
        }
        
        // Additional check for electrical equipment in category
        if (strpos($category, 'Электрооборудование') !== false) {
            echo "TabelHandler: handle - category contains электрооборудование: {$category}\n";
            // If it contains электрооборудование, handle it as electrical equipment
            if (strpos($category, 'Табель_Электрооборудование') !== false || $category === 'Электрооборудование') {
                echo "TabelHandler: handle - treating as electrical equipment category\n";
                return $this->handleElectricalEquipmentMenu($chatId);
            }
        }
        
        echo "TabelHandler: handle - no match for electrical equipment, returning default response\n";
        return $this->messageService->sendMessage($chatId, 'Неизвестная команда табеля');
    }
    
    public function getPriority(): int {
        return 20;
    }
    
    private function handleElectricalEquipmentMenu(string $chatId): MockServerResponse {
        echo "TabelHandler: handleElectricalEquipmentMenu called for chatId: {$chatId}\n";
        $keyboard = $this->messageService->createElectricalEquipmentKeyboard();
        
        // Add detailed logging for the keyboard
        echo "TabelHandler: handleElectricalEquipmentMenu keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
        
        $message = '<b>⚡ Электрооборудование</b>' . "\n\n" . 'Выберите тип работ:';
        
        echo "TabelHandler: handleElectricalEquipmentMenu sending message: " . $message . "\n";
        $result = $this->messageService->sendMessage($chatId, $message, $keyboard);
        
        echo "TabelHandler: handleElectricalEquipmentMenu sendMessage result: success\n";
        return $result;
    }
}

// Simulate the MessageService
class MessageService {
    public function createElectricalEquipmentKeyboard(): array {
        $keyboard = [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'category_Табель_РаботыПоГрафику_Электрооборудование']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'category_Табель_ФормированиеИД_Электрооборудование']
            ],
            [
                ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'category_Табель_ИзмСхемПодкл_ЭлектрОб']
            ],
            [
                ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'category_Табель_МонтажКабЛиний_ЭлектрОб']
            ],
            [
                ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'category_Табель_РасклКабЛиний_ЭлектрОб']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'category_Табель_ДопРаб_ЭлектрОб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'category_Табель_ПодтвДостВехи_ЭлектрОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'category_Табель_Прочее_Электрооборудование']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_ПусконаладочныеРаботы']
            ]
        ];
        
        echo "MessageService: createElectricalEquipmentKeyboard returning keyboard with " . count($keyboard) . " rows\n";
        return $keyboard;
    }
    
    public function sendMessage(string $chatId, string $text, array $keyboard = null): MockServerResponse {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            // For inline-клавиатур передаем массив напрямую
            $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard], JSON_UNESCAPED_UNICODE);
            echo "MessageService sendMessage keyboard JSON: " . $data['reply_markup'] . "\n";
        }
        
        echo "MessageService sendMessage data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
        return MockRequest::sendMessage($data);
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
    
    public function __construct($data = 'category_Табель_Электрооборудование') {
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
    }
}

echo "Testing full electrical equipment callback flow...\n\n";

// Test the full flow
echo "=== Test 1: Full callback flow ===\n";
$callbackData = 'category_Табель_Электрооборудование';
$callbackQuery = new MockCallbackQuery($callbackData);
$conversation = new MockConversation();

echo "Input callback data: {$callbackData}\n";

// Initialize services
$messageService = new MessageService();
$tabelHandler = new TabelHandler($messageService);

// Initialize handler factory
$handlerFactory = new CallbackHandlerFactory();

// Register handlers (order matters for priority)
$handlerFactory->registerHandler($tabelHandler);

// Handle the callback
echo "\n--- Processing callback ---\n";
$result = $handlerFactory->handleCallback($callbackQuery, $callbackData, $conversation);

echo "\n=== Test completed ===\n";

/**
 * Comprehensive test of the electrical equipment flow
 * 
 * This script simulates the complete flow from pressing the electrical equipment button
 * through to displaying the electrical equipment menu.
 */

class MockMessageService {
    public function createElectricalEquipmentKeyboard() {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабЭлОб_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабЭлОб_ФормИД']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабЭлОб_ДопРаб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабЭлОб_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_ПусконаладочныеРаботы']
            ]
        ];
    }
    
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "sendMessage called:\n";
        echo "  Chat ID: $chatId\n";
        echo "  Text: $text\n";
        if ($keyboard) {
            echo "  Keyboard has " . count($keyboard) . " rows\n";
            foreach ($keyboard as $index => $row) {
                echo "    Row $index: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
        echo "\n";
        return "Mock ServerResponse";
    }
}

class MockTabelHandler {
    private $messageService;
    
    public function __construct() {
        $this->messageService = new MockMessageService();
    }
    
    public function handle($callbackData, $category) {
        $chatId = 'test_chat_id';
        
        echo "TabelHandler::handle called with:\n";
        echo "  callbackData: $callbackData\n";
        echo "  category: $category\n\n";
        
        // Special handling for electrical equipment with encoding issues
        if (strpos($callbackData, 'Электрооборудование') !== false || 
            strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
            strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
            strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
            echo "✅ Special handling condition matched - calling handleElectricalEquipmentMenu\n";
            return $this->handleElectricalEquipmentMenu($chatId);
        }
        
        // Extract the actual category from the callback data
        $actualCategory = $this->extractCategoryFromCallback($callbackData);
        echo "Extracted actual category: '$actualCategory'\n";
        
        // Handle electrical equipment menu with short callback format
        if ($actualCategory === 'Табель_Электрооборудование' || 
            $actualCategory === 'cat_ТабЭлОб' || 
            $actualCategory === 'cat_ТабЭлОб_Наз') {
            echo "✅ Second condition matched - calling handleElectricalEquipmentMenu\n";
            return $this->handleElectricalEquipmentMenu($chatId);
        }
        
        echo "❌ No conditions matched - unexpected flow\n";
        return "No handler matched";
    }
    
    private function extractCategoryFromCallback(string $callbackData): string {
        // Handle original category format
        if (strpos($callbackData, 'category_') === 0) {
            return substr($callbackData, strlen('category_'));
        }
        
        // Handle short category format for electrical equipment
        if (strpos($callbackData, 'cat_Таб') === 0) {
            // Map short formats to full category names
            $shortFormats = [
                'cat_ТабЭлОб' => 'Табель_Электрооборудование',
                'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
                'cat_ТабЭлОб_РабПоГр' => 'Табель_РабПоГр_ЭлектрОб',
                'cat_ТабЭлОб_ФормИД' => 'Табель_ФормИД_ЭлектрОб',
                'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзмСхПд_ЭлектрОб',
                'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтКбЛн_ЭлектрОб',
                'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РаскКбЛн_ЭлектрОб',
                'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
                'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтВех_ЭлектрОб',
                'cat_ТабЭлОб_Проч' => 'Табель_Прочее_ЭлектрОб',
                // Handle confirmation callbacks as well
                'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
                'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормИД_ЭлектрОб_yes',
                'cat_ТабЭлОб_ИзмСхПд_yes' => 'Табель_ИзмСхПд_ЭлектрОб_yes',
                'cat_ТабЭлОб_МонтКбЛн_yes' => 'Табель_МонтКбЛн_ЭлектрОб_yes',
                'cat_ТабЭлОб_РаскКбЛн_yes' => 'Табель_РаскКбЛн_ЭлектрОб_yes',
                'cat_ТабЭлОб_ДопРаб_yes' => 'Табель_ДопРаб_ЭлектрОб_yes',
                'cat_ТабЭлОб_ПодтВех_yes' => 'Табель_ПодтВех_ЭлектрОб_yes',
                'cat_ТабЭлОб_Проч_yes' => 'Табель_Прочее_ЭлектрОб_yes'
            ];
            
            if (isset($shortFormats[$callbackData])) {
                return $shortFormats[$callbackData];
            }
            
            // Handle other short formats as needed
            return substr($callbackData, strlen('cat_'));
        }
        
        return '';
    }
    
    private function handleElectricalEquipmentMenu(string $chatId) {
        echo "handleElectricalEquipmentMenu called\n";
        $keyboard = $this->messageService->createElectricalEquipmentKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>⚡ Электрооборудование</b> Выберите тип работ:',
            $keyboard
        );
    }
}

// Test the flow
echo "=== Testing Electrical Equipment Flow ===\n\n";

$handler = new MockTabelHandler();

// Test 1: Electrical equipment button press
echo "Test 1: Electrical equipment button press\n";
echo "----------------------------------------\n";
$result1 = $handler->handle('category_Табель_Электрооборудование', 'Табель_ПусконаладочныеРаботы');
echo "Result: $result1\n\n";

// Test 2: Electrical equipment activity button press (Работы по графику)
echo "Test 2: Electrical equipment activity button press (Работы по графику)\n";
echo "----------------------------------------------------------------------\n";
$result2 = $handler->handle('cat_ТабЭлОб_РабПоГр', 'Табель_Электрооборудование');
echo "Result: $result2\n\n";

echo "=== Test Complete ===\n";

?>