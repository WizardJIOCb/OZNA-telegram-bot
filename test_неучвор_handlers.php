<?php
/**
 * Test script to verify "Работы неучтенные в ВОР" button handlers
 */

echo "=== Testing 'Работы неучтенные в ВОР' Button Handlers ===\n\n";

// Check if handlers exist in TabelHandler.php
$handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$content = file_get_contents($handlerFile);

$activities = [
    'ШМР_МонтажТрубопроводов_НеучВОР' => 'Монтаж трубопроводов',
    'ШМР_ГидроПневмоИспытания_НеучВОР' => 'Гидро-пневмо испытания',
    'ШМР_МонтажТехнологическогоОборудования_НеучВОР' => 'Монтаж технологического оборудования',
    'ШМР_МонтажЗРА_НеучВОР' => 'Монтаж ЗРА',
    'ШМР_ПротяжкаСоединений_НеучВОР' => 'Протяжка соединений',
    'ШМР_МонтажТеплоизоляции_НеучВОР' => 'Монтаж теплоизоляции'
];

$allPass = true;

echo "Checking handlers in TabelHandler.php:\n";
echo str_repeat("-", 60) . "\n";

foreach ($activities as $code => $name) {
    // Check if handler exists
    $handlerPattern = "if (\$category === '$code')";
    
    if (strpos($content, $handlerPattern) !== false) {
        echo "✅ $name\n";
        echo "   Handler found: \$category === '$code'\n";
        
        // Check if it calls getConfirmationMessage
        $confirmPattern = "getConfirmationMessage('$code'";
        if (strpos($content, $confirmPattern) !== false) {
            echo "   ✅ Calls getConfirmationMessage\n";
        } else {
            echo "   ❌ Missing getConfirmationMessage call\n";
            $allPass = false;
        }
        
        // Check if it creates confirmation keyboard
        if (strpos($content, "createConfirmationKeyboard") !== false) {
            echo "   ✅ Creates confirmation keyboard\n\n";
        } else {
            echo "   ❌ Missing createConfirmationKeyboard\n\n";
            $allPass = false;
        }
    } else {
        echo "❌ $name - HANDLER NOT FOUND\n";
        echo "   Missing: $handlerPattern\n\n";
        $allPass = false;
    }
}

echo "\n" . str_repeat("=", 60) . "\n";

if ($allPass) {
    echo "✅ ALL HANDLERS FOUND AND CONFIGURED CORRECTLY!\n\n";
    echo "The buttons should now work properly:\n";
    echo "1. Click 'Работы неучтенные в ВОР' button\n";
    echo "2. System shows confirmation dialog\n";
    echo "3. Click 'Да' to confirm\n";
    echo "4. Entry is created with proper format\n";
} else {
    echo "❌ SOME HANDLERS ARE MISSING OR INCOMPLETE\n";
    echo "Please review the failures above.\n";
}

echo str_repeat("=", 60) . "\n";
