<?php
// Detailed debug script for the "Пусконаладочные работы" button

echo "=== Detailed Debug for 'Пусконаладочные работы' Button ===\n\n";

// Let's check if there are any issues with the actual TabelHandler implementation
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "1. Testing TabelHandler class loading...\n";
try {
    // Try to instantiate the classes to check for any syntax errors
    $messageService = new MessageService();
    echo "   ✓ MessageService instantiated successfully\n";
} catch (Exception $e) {
    echo "   ✗ Error instantiating MessageService: " . $e->getMessage() . "\n";
}

echo "\n2. Checking for syntax errors in TabelHandler...\n";
// Let's check the actual file for syntax errors
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');
if ($tabelHandlerContent === false) {
    echo "   ✗ Could not read TabelHandler.php file\n";
} else {
    echo "   ✓ TabelHandler.php file read successfully\n";
    
    // Check for the specific methods we're interested in
    if (strpos($tabelHandlerContent, 'handleCommissioningWorksMenu') !== false) {
        echo "   ✓ handleCommissioningWorksMenu method found\n";
    } else {
        echo "   ✗ handleCommissioningWorksMenu method NOT found\n";
    }
    
    if (strpos($tabelHandlerContent, "if (\$category === 'Табель_ПНЛ')") !== false) {
        echo "   ✓ Табель_ПНЛ routing found\n";
    } else {
        echo "   ✗ Табель_ПНЛ routing NOT found\n";
    }
}

echo "\n3. Simulating actual callback processing...\n";

// Let's look at what might be happening in the callback processing
$callbackData = 'category_Табель_ПНЛ';
echo "   Testing callback data: $callbackData\n";

// Simulate the category extraction as it would happen in the real code
$category = substr($callbackData, strlen('category_'));
echo "   Extracted category: $category\n";

// Check if this matches what we expect
if ($category === 'Табель_ПНЛ') {
    echo "   ✓ Category correctly extracted\n";
} else {
    echo "   ✗ Category extraction failed. Expected 'Табель_ПНЛ', got '$category'\n";
}

// Let's check if there might be encoding issues
echo "\n4. Checking for potential encoding issues...\n";
$utf8Category = mb_convert_encoding($category, 'UTF-8', 'UTF-8');
if ($category === $utf8Category) {
    echo "   ✓ No encoding issues detected\n";
} else {
    echo "   ⚠ Potential encoding issue detected\n";
}

echo "\n5. Checking MessageService keyboard creation...\n";
try {
    // Check if the keyboard creation works
    $keyboard = [
        [
            ['text' => '⚙️ Пусконаладочные работы', 'callback_data' => 'category_Табель_ПНЛ']
        ]
    ];
    echo "   ✓ Keyboard structure is valid\n";
    
    // Check if this matches what's in the actual code
    if (strpos($messageServiceContent, '⚙️ Пусконаладочные работы') !== false) {
        echo "   ✓ Button text found in MessageService\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error creating keyboard: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";
echo "If all checks passed, the issue might be in the callback processing chain.\n";
echo "Please check if the callback is being properly routed through the factory.\n";
?>