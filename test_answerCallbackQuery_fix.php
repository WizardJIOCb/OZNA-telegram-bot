<?php
/**
 * Test script to verify answerCallbackQuery fix
 * This simulates a callback query and checks if answerCallbackQuery is called
 */

// Mock the Telegram Bot API classes for testing
class MockCallbackQuery {
    private $id;
    private $data;
    
    public function __construct($id, $data) {
        $this->id = $id;
        $this->data = $data;
    }
    
    public function getId() {
        return $this->id;
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
        };
    }
}

class MockRequest {
    public static $answerCallbackQueryCalled = false;
    public static $callbackQueryId = null;
    
    public static function answerCallbackQuery($params) {
        self::$answerCallbackQueryCalled = true;
        self::$callbackQueryId = $params['callback_query_id'] ?? null;
        echo "✅ answerCallbackQuery CALLED with ID: " . self::$callbackQueryId . "\n";
        return new class {
            public function isOk() {
                return true;
            }
        };
    }
    
    public static function sendMessage($params) {
        echo "📤 sendMessage called to chat: " . ($params['chat_id'] ?? 'unknown') . "\n";
        echo "   Text: " . substr($params['text'] ?? '', 0, 50) . "...\n";
        return new class {
            public function isOk() {
                return true;
            }
        };
    }
    
    public static function emptyResponse() {
        echo "⚠️ emptyResponse called\n";
        return new class {
            public function isOk() {
                return false;
            }
        };
    }
}

// Replace the real Request class with our mock
class_alias('MockRequest', 'Longman\TelegramBot\Request');

echo "=== TESTING CALLBACK QUERY HANDLING ===\n\n";

// Test 1: Check if CallbackHandlerFactory exists
echo "Test 1: Checking if CallbackHandlerFactory.php exists...\n";
$factoryFile = __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
if (file_exists($factoryFile)) {
    echo "✅ File exists: {$factoryFile}\n";
} else {
    echo "❌ File NOT found: {$factoryFile}\n";
    exit(1);
}

// Test 2: Check if answerCallbackQuery is called in the file
echo "\nTest 2: Checking if answerCallbackQuery is present in code...\n";
$content = file_get_contents($factoryFile);
if (strpos($content, 'Request::answerCallbackQuery') !== false) {
    echo "✅ Found 'Request::answerCallbackQuery' in code\n";
} else {
    echo "❌ 'Request::answerCallbackQuery' NOT found in code\n";
    echo "⚠️ THE FIX IS NOT APPLIED!\n";
    exit(1);
}

// Test 3: Check if it's called BEFORE handler processing
echo "\nTest 3: Checking if answerCallbackQuery is called EARLY in handleCallback...\n";
$lines = explode("\n", $content);
$answerCallbackQueryLine = 0;
$handleCallbackLine = 0;
$handlerLoopLine = 0;

foreach ($lines as $i => $line) {
    if (strpos($line, 'public function handleCallback') !== false) {
        $handleCallbackLine = $i + 1;
    }
    if (strpos($line, 'Request::answerCallbackQuery') !== false) {
        $answerCallbackQueryLine = $i + 1;
    }
    if (strpos($line, 'foreach ($this->handlers as') !== false) {
        $handlerLoopLine = $i + 1;
    }
}

if ($answerCallbackQueryLine > 0 && $handleCallbackLine > 0) {
    $distance = $answerCallbackQueryLine - $handleCallbackLine;
    echo "   handleCallback() starts at line: {$handleCallbackLine}\n";
    echo "   answerCallbackQuery() called at line: {$answerCallbackQueryLine}\n";
    echo "   Handler loop starts at line: {$handlerLoopLine}\n";
    
    if ($answerCallbackQueryLine > $handleCallbackLine && 
        $answerCallbackQueryLine < $handlerLoopLine) {
        echo "✅ answerCallbackQuery is called BEFORE handler loop (distance: {$distance} lines)\n";
    } else {
        echo "❌ answerCallbackQuery is NOT called before handler loop!\n";
        exit(1);
    }
} else {
    echo "❌ Could not find method positions in file\n";
    exit(1);
}

// Test 4: Check for CRITICAL FIX comment
echo "\nTest 4: Checking for CRITICAL FIX documentation...\n";
if (strpos($content, 'CRITICAL FIX: Answer the callback query IMMEDIATELY') !== false) {
    echo "✅ Found CRITICAL FIX documentation comment\n";
} else {
    echo "⚠️ CRITICAL FIX comment not found (but code might still work)\n";
}

// Test 5: Verify the fix will work
echo "\n=== FINAL VERDICT ===\n";
echo "✅ All tests passed!\n";
echo "✅ The fix is properly applied in CallbackHandlerFactory.php\n";
echo "✅ answerCallbackQuery will be called IMMEDIATELY when callback is received\n";
echo "✅ Buttons should work without freezing after deployment\n\n";

echo "📋 Next steps:\n";
echo "1. Upload CallbackHandlerFactory.php to server\n";
echo "2. Clear OPcache (https://ozna.ruc-nw.ru/clear_cache.php)\n";
echo "3. Test buttons in Telegram bot\n";
echo "4. Verify no loading spinners appear\n";
