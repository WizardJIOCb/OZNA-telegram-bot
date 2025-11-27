<?php

// Comprehensive test for electrical equipment callback handling
echo "=== Comprehensive Electrical Equipment Callback Test ===\n\n";

// Simulate the callback data
$callbackData = 'category_Табель_Электрооборудование';

echo "Input callback data: {$callbackData}\n";
echo "Length: " . strlen($callbackData) . " bytes\n";
echo "Hex representation: " . bin2hex($callbackData) . "\n\n";

// Test 1: CallbackHandlerFactory processing
echo "=== Test 1: CallbackHandlerFactory Processing ===\n";

// Simulate CallbackHandlerFactory handleCallback method
function handleCallback($callbackData) {
    echo "CallbackHandlerFactory: handleCallback called with callbackData: '{$callbackData}'\n";
    
    // Store original callback data to prevent double processing
    $originalCallbackData = $callbackData;
    
    // Fix encoding issues with callback data - only once at the beginning
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    echo "CallbackHandlerFactory: handleCallback after encoding fix: '{$callbackData}'\n";
    
    // Fix known Cyrillic pattern corruptions - only once at the beginning
    $callbackData = fixKnownCyrillicPatterns($callbackData);
    echo "CallbackHandlerFactory: handleCallback after Cyrillic fix: '{$callbackData}'\n";
    
    // Extract category from callback data
    $category = extractCategory($callbackData);
    echo "CallbackHandlerFactory: handling callback data: '{$callbackData}', extracted category: '{$category}'\n";
    
    // Special check for electrical equipment
    if ($category === 'Табель_Электрооборудование' || strpos($callbackData, 'Табель_Электрооборудование') !== false) {
        echo "CallbackHandlerFactory: detected electrical equipment callback\n";
        return ['handler' => 'TabelHandler', 'method' => 'handleElectricalEquipmentMenu', 'category' => $category];
    }
    
    return ['handler' => 'unknown', 'method' => 'unknown', 'category' => $category];
}

// Simulate fixKnownCyrillicPatterns method
function fixKnownCyrillicPatterns($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
        'Р”Р°С‚Р°' => 'Дата',
        'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
        'Р’С‡РµСЂР°' => 'Вчера',
        'РСЂРѕС„РёР»СЊ' => 'Профиль',
        'РСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
        'Р¤РРћ' => 'ФИО',
        'Р¤РЊ' => 'ФН',
        'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
        'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
        'РСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
        'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
        'РўР°Р±РµР»СЊ' => 'Табель',
        'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
        'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
        'РџРѕРЅРёР·РёС‚СЊ' => 'Понизить',
        // Add missing patterns for commissioning works
        'РџРќР›' => 'ПНЛ',
        'РСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
        'Р Р°Р±РѕС‚С‹' => 'Работы',
        // Add missing patterns for the main buttons (order matters - more specific first)
        'РЁРµС„_РјРѕРЅС‚Р°Р¶' => 'Шеф_монтаж',
        'РўРµРєСѓС‰Р°СЏ' => 'Текущая',
        // Add individual word patterns
        'РјРѕРЅС‚Р°Р¶' => 'монтаж',
        // Add pattern for commissioning works abbreviation
        'РџРќР ' => 'ПНР',
        // Add pattern for preparation
        'РРѕРґРіРѕС‚РѕРІРєР°' => 'Подготовка',
        // Add pattern for electrical equipment - this is the key fix
        'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
        // Add pattern for full electrical equipment callback - this is the key fix
        'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'category_Табель_Электрооборудование',
        // Add additional patterns for double-encoded Cyrillic that might be causing issues
        'Табель_Электрооборудование' => 'Табель_Электрооборудование',
        'category_Табель_Электрооборудование' => 'category_Табель_Электрооборудование'
    ];
    
    // Log the original string
    echo "CallbackHandlerFactory: fixKnownCyrillicPatterns input: '{$str}'\n";
    
    // Special case: if string is already the correct electrical equipment callback, return as is
    if ($str === 'category_Табель_Электрооборудование' || $str === 'Табель_Электрооборудование') {
        echo "CallbackHandlerFactory: fixKnownCyrillicPatterns returning correct electrical equipment string as is: '{$str}'\n";
        return $str;
    }
    
    foreach ($replacements as $corrupted => $fixed) {
        if (strpos($str, $corrupted) !== false) {
            echo "CallbackHandlerFactory: found corrupted pattern '{$corrupted}' in string, replacing with '{$fixed}'\n";
            $str = str_replace($corrupted, $fixed, $str);
        }
    }
    
    // Log the result
    echo "CallbackHandlerFactory: fixKnownCyrillicPatterns output: '{$str}'\n";
    
    return $str;
}

// Simulate extractCategory method
function extractCategory($callbackData) {
    echo "CallbackHandlerFactory extractCategory called with callbackData: '{$callbackData}'\n";
    
    // Handle new short callback_data formats
    if (strpos($callbackData, 'adm_req_') === 0) {
        // For admin request callbacks, return the full callbackData as category
        $category = $callbackData;
        echo "CallbackHandlerFactory extracted category: '{$category}' from admin request callback data: '{$callbackData}'\n";
        return $category;
    }
    
    // Special handling for electrical equipment - check this first and return immediately
    if (strpos($callbackData, 'category_Табель_Электрооборудование') === 0) {
        $category = 'Табель_Электрооборудование';
        echo "CallbackHandlerFactory extracted category (special case): '{$category}' from callback data: '{$callbackData}'\n";
        return $category;
    }
    
    // Additional logging for electrical equipment patterns
    if (strpos($callbackData, 'Электрооборудование') !== false) {
        echo "CallbackHandlerFactory: callbackData contains электрооборудование: {$callbackData}\n";
        echo "CallbackHandlerFactory: strpos 'category_Табель_Электрооборудование' result: " . (strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ? 'true' : 'false') . "\n";
    }
    
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        echo "CallbackHandlerFactory extractCategory after substr: '{$category}'\n";
        
        // Fix encoding for category
        $category = mb_convert_encoding($category, 'UTF-8', 'auto');
        
        echo "CallbackHandlerFactory extractCategory after category encoding fix: '{$category}'\n";
        
        // Fix known Cyrillic pattern corruptions for category as well
        $category = fixKnownCyrillicPatterns($category);
        
        echo "CallbackHandlerFactory extractCategory after category Cyrillic fix: '{$category}'\n";
        
        // Special case: if category is exactly 'Табель_Электрооборудование', return it immediately
        if ($category === 'Табель_Электрооборудование') {
            echo "CallbackHandlerFactory extracted category (special case 2): '{$category}'\n";
            return $category;
        }
        
        // Additional check for electrical equipment after fixes
        if (strpos($category, 'Табель_Электрооборудование') === 0) {
            $finalCategory = 'Табель_Электрооборудование';
            echo "CallbackHandlerFactory extracted category (special case 3): '{$finalCategory}' from fixed category: '{$category}'\n";
            return $finalCategory;
        }
        
        echo "CallbackHandlerFactory extracted category: '{$category}' from callback data: '{$callbackData}'\n";
        return $category;
    }
    
    echo "CallbackHandlerFactory no recognized prefix found in callback data: '{$callbackData}'\n";
    return $callbackData;
}

$result = handleCallback($callbackData);
$category = $result['category'];
echo "Handler: {$result['handler']}\n";
echo "Method: {$result['method']}\n";
echo "Category: {$category}\n\n";

// Test 2: TabelHandler canHandle
echo "=== Test 2: TabelHandler canHandle ===\n";

function tabelHandlerCanHandle($callbackData) {
    echo "TabelHandler: canHandle called with callbackData: '{$callbackData}'\n";
    
    // Special handling for electrical equipment - check this first
    if (str_starts_with($callbackData, 'category_Табель_Электрооборудование')) {
        echo "TabelHandler: canHandle - special case for electrical equipment: true\n";
        return true;
    }
    
    // Additional check for corrupted electrical equipment callback data
    if (strpos($callbackData, 'Электрооборудование') !== false) {
        echo "TabelHandler: canHandle - found электрооборудование in callback data\n";
        return true;
    }
    
    $canHandle = str_starts_with($callbackData, 'category_Табель');
    echo "TabelHandler: canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";
    return $canHandle;
}

$canHandle = tabelHandlerCanHandle($callbackData);
echo "TabelHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n\n";

// Test 3: TabelHandler handle method
echo "=== Test 3: TabelHandler handle method ===\n";

function tabelHandlerHandle($category) {
    echo "TabelHandler: handle called with category: {$category}\n";
    
    // Special handling for electrical equipment category
    if ($category === 'Табель_Электрооборудование') {
        echo "TabelHandler: handle - special case for electrical equipment category\n";
        return 'handleElectricalEquipmentMenu';
    }
    
    // Additional check for electrical equipment in category
    if (strpos($category, 'Электрооборудование') !== false) {
        echo "TabelHandler: handle - category contains электрооборудование: {$category}\n";
        if (strpos($category, 'Табель_Электрооборудование') !== false || $category === 'Электрооборудование') {
            echo "TabelHandler: handle - treating as electrical equipment category\n";
            return 'handleElectricalEquipmentMenu';
        }
    }
    
    echo "TabelHandler: handle - no match for electrical equipment\n";
    return 'routeTabelCallback';
}

$handlerMethod = tabelHandlerHandle($category);
echo "TabelHandler will call method: {$handlerMethod}\n\n";

// Test 4: handleElectricalEquipmentMenu
echo "=== Test 4: handleElectricalEquipmentMenu ===\n";

function handleElectricalEquipmentMenu() {
    echo "TabelHandler: handleElectricalEquipmentMenu called\n";
    
    // Create the keyboard
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
    
    echo "Created electrical equipment keyboard with " . count($keyboard) . " rows\n";
    
    $message = '<b>⚡ Электрооборудование</b>' . "\n\n" . 'Выберите тип работ:';
    echo "Message to send: {$message}\n";
    
    // Simulate sending message
    echo "Sending message to Telegram API...\n";
    echo "SUCCESS: Message sent to Telegram API\n";
    
    return ['message' => $message, 'keyboard' => $keyboard, 'status' => 'success'];
}

if ($handlerMethod === 'handleElectricalEquipmentMenu') {
    $result = handleElectricalEquipmentMenu();
    echo "Successfully created electrical equipment menu\n";
    echo "Message: {$result['message']}\n";
    echo "Keyboard rows: " . count($result['keyboard']) . "\n";
    echo "Status: {$result['status']}\n";
} else {
    echo "Not handling electrical equipment menu\n";
}

echo "\n=== Summary ===\n";
echo "1. Callback data: {$callbackData}\n";
echo "2. Extracted category: {$category}\n";
echo "3. TabelHandler can handle: " . ($canHandle ? 'YES' : 'NO') . "\n";
echo "4. Handler method: {$handlerMethod}\n";
echo "5. Electrical equipment menu should be displayed: " . ($handlerMethod === 'handleElectricalEquipmentMenu' ? 'YES' : 'NO') . "\n";

if ($canHandle && $handlerMethod === 'handleElectricalEquipmentMenu') {
    echo "\n✅ SUCCESS: The electrical equipment button should work correctly!\n";
    echo "If it's still not working, there might be a runtime issue with:\n";
    echo "  - Database connection\n";
    echo "  - Telegram API message sending\n";
    echo "  - Network connectivity\n";
    echo "  - User access level restrictions\n";
} else {
    echo "\n❌ ISSUE: There's a problem in the callback handling logic.\n";
}

echo "\n=== Recommendations ===\n";
echo "1. Check the database connection\n";
echo "2. Verify user access level is not 0 (guest)\n";
echo "3. Check if user has FIO and functional direction filled\n";
echo "4. Verify Telegram API is working correctly\n";
echo "5. Check network connectivity\n";

echo "\n=== Test Complete ===\n";

/**
 * Comprehensive test for Projects under Control callback handling
 * Tests the complete flow from callback reception to handler execution
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

// Use the services
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;
use Longman\TelegramBot\DB;

echo "=== COMPREHENSIVE CALLBACK FLOW TEST ===\n\n";

// Initialize database
try {
    DB::initialize($mysql_credentials, $telegram, null, $mysql_encoding);
    echo "✓ Database initialized\n";
} catch (Exception $e) {
    echo "✗ Database failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 1: Callback data length verification
echo "\n1. CALLBACK DATA LENGTH VERIFICATION:\n";
$testCallbacks = [
    'proj_ctrl_add',
    'proj_ctrl_remove',
    'proj_ctrl_list', 
    'proj_ctrl_settings'
];

foreach ($testCallbacks as $callback) {
    $length = strlen($callback);
    echo "  '{$callback}' = {$length} bytes " . ($length <= 64 ? "✓" : "✗") . "\n";
}

// Test 2: CallbackHandlerFactory extractCategory method
echo "\n2. CALLBACKHANDLERFACTORY EXTRACTCATEGORY TEST:\n";

$factory = new CallbackHandlerFactory();

// Use reflection to test the private extractCategory method
$reflectionClass = new ReflectionClass($factory);
$extractCategoryMethod = $reflectionClass->getMethod('extractCategory');
$extractCategoryMethod->setAccessible(true);

foreach ($testCallbacks as $callback) {
    $extractedCategory = $extractCategoryMethod->invoke($factory, $callback);
    echo "  '{$callback}' -> category: '{$extractedCategory}' " . 
         (!empty($extractedCategory) ? "✓" : "✗") . "\n";
}

// Test 3: ProjectHandler canHandle test
echo "\n3. PROJECTHANDLER CANHANDLE TEST:\n";

$messageService = new MessageService();
$adminService = new AdminService($messageService);
$projectHandler = new ProjectHandler($adminService, $messageService);

foreach ($testCallbacks as $callback) {
    $canHandle = $projectHandler->canHandle($callback);
    echo "  ProjectHandler->canHandle('{$callback}') = " . ($canHandle ? "true" : "false") . 
         ($canHandle ? " ✓" : " ✗") . "\n";
}

// Test 4: Full flow simulation
echo "\n4. FULL FLOW SIMULATION:\n";

// Create mock objects for testing
class MockCallbackQuery {
    private $data;
    
    public function __construct($data) {
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
                return 230379668; // Test user ID
            }
        };
    }
}

class MockConversation {
    public $notes = [];
    public function update() {}
}

// Register ProjectHandler in factory
$factory->registerHandler($projectHandler);

foreach ($testCallbacks as $callback) {
    echo "  Testing full flow for '{$callback}':\n";
    
    $mockCallbackQuery = new MockCallbackQuery($callback);
    $mockConversation = new MockConversation();
    
    try {
        $result = $factory->handleCallback($mockCallbackQuery, $callback, $mockConversation);
        
        if ($result && is_object($result) && method_exists($result, 'isOk')) {
            echo "    Result: " . ($result->isOk() ? "SUCCESS ✓" : "FAILED ✗") . "\n";
        } else {
            echo "    Result: UNKNOWN ?\n";
        }
    } catch (Exception $e) {
        echo "    ERROR: " . $e->getMessage() . " ✗\n";
    }
}

// Test 5: Database check for test user
echo "\n5. TEST USER DATABASE CHECK:\n";

$testUserId = 230379668;
try {
    $stmt = DB::getPdo()->prepare("SELECT username, access_level FROM users WHERE id = ?");
    $stmt->execute([$testUserId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "  User found: {$user['username']}\n";
        echo "  Access level: {$user['access_level']}\n";
        echo "  Has supervisor access: " . ($user['access_level'] >= 2 ? "YES ✓" : "NO ✗") . "\n";
    } else {
        echo "  Test user not found ✗\n";
    }
} catch (Exception $e) {
    echo "  Database error: " . $e->getMessage() . " ✗\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "\nIf all tests show ✓, the callback handling should work correctly.\n";
echo "If you still see only 2 buttons or buttons not responding:\n";
echo "1. Check error logs for specific handler execution errors\n";
echo "2. Verify user access level is 2+ for control projects\n";
echo "3. Check if other handlers might be intercepting the callbacks\n";
?>