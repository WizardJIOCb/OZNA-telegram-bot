<?php
/**
 * Test script to verify preparatory works menu content
 * This will show exactly what buttons are being created
 */

// Simple test without autoloader - let's check the file directly
echo "=== TESTING PREPARATORY WORKS MENU ===\n";
echo "Current time: " . date('Y-m-d H:i:s') . "\n\n";

// Read the MessageService.php file directly
$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';

if (!file_exists($messageServiceFile)) {
    echo "❌ ERROR: MessageService.php not found at: $messageServiceFile\n";
    exit(1);
}

$content = file_get_contents($messageServiceFile);

// Find the createPreparatoryWorksKeyboard function
if (preg_match('/function createPreparatoryWorksKeyboard\(\).*?\{(.*?)\}/s', $content, $matches)) {
    $functionContent = $matches[1];
    echo "📋 FOUND createPreparatoryWorksKeyboard FUNCTION:\n";
    echo "Content:\n";
    echo $functionContent . "\n\n";
    
    // Check for site inspection references
    if (stripos($functionContent, 'Проверка монтажной площадки') !== false) {
        echo "❌ FOUND SITE INSPECTION TEXT IN FUNCTION!\n";
    } elseif (stripos($functionContent, 'АналФунд') !== false) {
        echo "❌ FOUND АналФунд CALLBACK IN FUNCTION!\n";
    } else {
        echo "✅ NO SITE INSPECTION REFERENCES FOUND IN FUNCTION\n";
    }
} else {
    echo "❌ ERROR: createPreparatoryWorksKeyboard function not found\n";
}

echo "\n=== CHECKING ENTIRE FILE FOR SITE INSPECTION REFERENCES ===\n";

// Check entire file for any site inspection references
$foundReferences = [];
if (stripos($content, 'Проверка монтажной площадки') !== false) {
    $foundReferences[] = 'Russian text: Проверка монтажной площадки';
}
if (stripos($content, 'АналФунд') !== false) {
    $foundReferences[] = 'Callback code: АналФунд';
}
if (stripos($content, 'site inspection') !== false) {
    $foundReferences[] = 'English text: site inspection';
}

if (empty($foundReferences)) {
    echo "✅ NO SITE INSPECTION REFERENCES FOUND IN ENTIRE FILE\n";
} else {
    echo "❌ FOUND SITE INSPECTION REFERENCES:\n";
    foreach ($foundReferences as $ref) {
        echo "  - $ref\n";
    }
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "Expected result: Only 3 buttons should exist\n";
echo "1. 🔍 Входной контроль\n";
echo "2. 🚚 Завоз оборудования на площадку\n";
echo "3. ⬅️ Назад\n";
?>
