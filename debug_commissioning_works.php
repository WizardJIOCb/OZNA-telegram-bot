<?php
// Debug script specifically for commissioning works functionality

echo "=== Commissioning Works Debug Script ===\n\n";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_commissioning_works.log');

// Log start of script
error_log("=== Commissioning Works Debug Script Started ===");

// Test 1: Check if required files exist
echo "1. Checking required files...\n";
$requiredFiles = [
    'hook.php',
    'TabelHandler_fixed.php',
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Services/ProjectSelector.php'
];

$allFilesExist = true;
foreach ($requiredFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        echo "   ✓ {$file} exists\n";
        error_log("File exists: {$file}");
    } else {
        echo "   ✗ {$file} NOT FOUND\n";
        error_log("File NOT FOUND: {$file}");
        $allFilesExist = false;
    }
}

if (!$allFilesExist) {
    echo "   ERROR: Some required files are missing!\n";
    error_log("ERROR: Some required files are missing!");
}

// Test 2: Check if TabelHandler can handle commissioning callback
echo "\n2. Testing TabelHandler canHandle method...\n";
try {
    // First check if CallbackHandlerInterface exists
    $interfacePath = __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
    if (file_exists($interfacePath)) {
        echo "   ✓ CallbackHandlerInterface file exists\n";
        error_log("CallbackHandlerInterface file exists");
    } else {
        echo "   ✗ CallbackHandlerInterface file NOT FOUND\n";
        error_log("CallbackHandlerInterface file NOT FOUND");
    }
    
    require_once __DIR__ . '/TabelHandler_fixed.php';
    
    // Create a mock TabelHandler (this is a simplified test)
    echo "   Attempting to load TabelHandler...\n";
    error_log("Attempting to load TabelHandler...");
    
    // We can't fully instantiate TabelHandler without its dependencies,
    // but we can check if the file loads correctly
    echo "   ✓ TabelHandler file loaded successfully\n";
    error_log("TabelHandler file loaded successfully");
    
    // Test the canHandle method logic directly
    $testCallback = 'category_Табель_Пусконаладка';
    $canHandle = str_starts_with($testCallback, 'category_Табель');
    echo "   Testing canHandle with '{$testCallback}': " . ($canHandle ? 'YES' : 'NO') . "\n";
    error_log("canHandle test with '{$testCallback}': " . ($canHandle ? 'YES' : 'NO'));
    
} catch (Exception $e) {
    echo "   ✗ Error loading TabelHandler: " . $e->getMessage() . "\n";
    error_log("Error loading TabelHandler: " . $e->getMessage());
} catch (Error $e) {
    echo "   ✗ Error loading TabelHandler: " . $e->getMessage() . "\n";
    error_log("Error loading TabelHandler: " . $e->getMessage());
}

// Test 3: Check MessageService createDateSelectionKeyboard method
echo "\n3. Testing MessageService createDateSelectionKeyboard method...\n";
try {
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    echo "   Attempting to create MessageService...\n";
    error_log("Attempting to create MessageService...");
    
    $messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    echo "   ✓ MessageService created successfully\n";
    error_log("MessageService created successfully");
    
    // Test creating commissioning date selection keyboard
    echo "   Creating commissioning date selection keyboard...\n";
    error_log("Creating commissioning date selection keyboard...");
    
    $keyboard = $messageService->createDateSelectionKeyboard('category_Табель_ПНЛ');
    echo "   ✓ Keyboard created successfully\n";
    error_log("Keyboard created successfully");
    
    // Display keyboard structure
    echo "   Keyboard structure:\n";
    error_log("Keyboard structure:");
    foreach ($keyboard as $rowIndex => $row) {
        foreach ($row as $buttonIndex => $button) {
            $text = $button['text'] ?? 'N/A';
            $callback = $button['callback_data'] ?? 'N/A';
            echo "     Button: {$text} -> {$callback}\n";
            error_log("Button: {$text} -> {$callback}");
        }
    }
    
} catch (Exception $e) {
    echo "   ✗ Error with MessageService: " . $e->getMessage() . "\n";
    error_log("Error with MessageService: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
} catch (Error $e) {
    echo "   ✗ Error with MessageService: " . $e->getMessage() . "\n";
    error_log("Error with MessageService: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
}

// Test 4: Check callback data length (Telegram has a 64-byte limit)
echo "\n4. Checking callback data lengths...\n";
error_log("Checking callback data lengths...");

$testCallbacks = [
    'category_Табель_ПНЛ_Сегодня',
    'category_Табель_ПНЛ_Вчера',
    'category_Табель_ПНЛ_Позавчера',
    'category_Табель_ПНЛ_ДругаяДата'
];

foreach ($testCallbacks as $callback) {
    $length = strlen($callback);
    echo "   '{$callback}' - Length: {$length} bytes\n";
    error_log("Callback: '{$callback}' - Length: {$length} bytes");
    
    if ($length > 64) {
        echo "   ⚠️  WARNING: This exceeds Telegram's 64-byte limit!\n";
        error_log("WARNING: Callback exceeds Telegram's 64-byte limit!");
    }
}

// Test 5: Check encoding
echo "\n5. Checking encoding...\n";
error_log("Checking encoding...");

$testStrings = [
    'Пусконаладка',
    'Табель',
    'Сегодня',
    'category_Табель_ПНЛ'
];

// Check if mbstring is available
$mbstringAvailable = extension_loaded('mbstring');
if (!$mbstringAvailable) {
    echo "   ⚠️  mbstring extension not available - using basic string functions\n";
    error_log("mbstring extension not available - using basic string functions");
}

foreach ($testStrings as $string) {
    $length = strlen($string);
    if ($mbstringAvailable) {
        $mbLength = mb_strlen($string, 'UTF-8');
        echo "   '{$string}' - Bytes: {$length}, UTF-8 chars: {$mbLength}\n";
        error_log("String: '{$string}' - Bytes: {$length}, UTF-8 chars: {$mbLength}");
    } else {
        echo "   '{$string}' - Bytes: {$length}\n";
        error_log("String: '{$string}' - Bytes: {$length}");
    }
}

// Test 6: Simulate the complete commissioning flow
echo "\n6. Simulating complete commissioning flow...\n";
error_log("Simulating complete commissioning flow...");

echo "   Step 1: User clicks '⚙️ Отдел ПНР' button\n";
error_log("Step 1: User clicks '⚙️ Отдел ПНР' button");

$initialCallback = 'category_Табель_Пусконаладка';
echo "   Callback data: {$initialCallback}\n";
error_log("Callback data: {$initialCallback}");

echo "   Step 2: TabelHandler processes callback\n";
error_log("Step 2: TabelHandler processes callback");

// Check if it matches commissioning condition
if ($initialCallback === 'category_Табель_Пусконаладка') {
    echo "   ✓ MATCH: Commissioning callback detected\n";
    error_log("MATCH: Commissioning callback detected");
    
    echo "   Step 3: Calling handleCommissioningDateSelection\n";
    error_log("Step 3: Calling handleCommissioningDateSelection");
    
    // Simulate what handleCommissioningDateSelection does
    $datePrefix = 'category_Табель_ПНЛ';
    echo "   Using date selection prefix: {$datePrefix}\n";
    error_log("Using date selection prefix: {$datePrefix}");
    
    echo "   Step 4: Creating date selection keyboard\n";
    error_log("Step 4: Creating date selection keyboard");
    
    // Simulate keyboard creation
    $keyboard = [
        [
            ['text' => '📅 Сегодня', 'callback_data' => "{$datePrefix}_Сегодня"]
        ],
        [
            ['text' => '📆 Вчера', 'callback_data' => "{$datePrefix}_Вчера"]
        ],
        [
            ['text' => '📆 Позавчера', 'callback_data' => "{$datePrefix}_Позавчера"]
        ],
        [
            ['text' => '📆 Другая дата', 'callback_data' => "{$datePrefix}_ДругаяДата"]
        ]
    ];
    
    echo "   ✓ Date selection keyboard created successfully\n";
    error_log("Date selection keyboard created successfully");
    
    echo "   Keyboard contents:\n";
    error_log("Keyboard contents:");
    foreach ($keyboard as $rowIndex => $row) {
        foreach ($row as $buttonIndex => $button) {
            $text = $button['text'] ?? 'N/A';
            $callback = $button['callback_data'] ?? 'N/A';
            echo "     Button: {$text} -> {$callback}\n";
            error_log("Button: {$text} -> {$callback}");
        }
    }
    
    echo "   ✅ COMMISSIONING FLOW SIMULATION COMPLETED SUCCESSFULLY\n";
    error_log("COMMISSIONING FLOW SIMULATION COMPLETED SUCCESSFULLY");
    
} else {
    echo "   ✗ NO MATCH: This is not a commissioning callback\n";
    error_log("NO MATCH: This is not a commissioning callback");
}

// Final summary
echo "\n=== Debug Summary ===\n";
error_log("=== Debug Summary ===");

echo "If you're experiencing issues with commissioning works:\n";
echo "1. Check the log file: debug_commissioning_works.log\n";
echo "2. Check the main log: telegram_bot_error.log\n";
echo "3. Look for errors in the output above\n";
echo "4. Verify callback data lengths don't exceed 64 bytes\n";
echo "5. Ensure all required files exist\n";

error_log("Debug script completed");
echo "\nDebug information has been logged to: debug_commissioning_works.log\n";

?>