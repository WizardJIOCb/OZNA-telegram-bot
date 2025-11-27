<?php
// Verification script to check Mobile Brigade keyboard button order and emoji styling

echo "Verifying Mobile Brigade Keyboard Button Order and Emoji Styling\n";
echo "===============================================================\n\n";

// Read the MessageService.php file
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Define the expected button order and styling
$expectedButtons = [
    '🚗 Подготовка к служебной поездке',
    '🚗 Дорога',
    '📋 Инструктаж',
    '⚡ Электромонтажные работы',
    '🔧 Слесарные работы',
    '🔥 Сварочные работы',
    '🧱 Теплоизоляция',
    '📂 Сдача отчетных документов',
    '⏸️ Простой',
    '🛡️ Гарантия'
];

echo "Checking button order and styling...\n\n";

// Extract the createMobileBrigadeKeyboard method content
if (preg_match('/createMobileBrigadeKeyboard\(\): array\s*\{([^}]+)\}/s', $messageServiceContent, $matches)) {
    $methodContent = $matches[1];
    
    $foundButtons = [];
    // Extract all button texts
    preg_match_all("/'text' => '([^']+)'/", $methodContent, $buttonMatches);
    
    if (isset($buttonMatches[1])) {
        foreach ($buttonMatches[1] as $buttonText) {
            // Skip the "Вернуться в табель" button as it's the last one
            if (strpos($buttonText, 'Вернуться в табель') === false) {
                $foundButtons[] = $buttonText;
            }
        }
    }
    
    // Check if the order matches
    $orderCorrect = true;
    $stylingCorrect = true;
    
    for ($i = 0; $i < count($expectedButtons); $i++) {
        if ($i < count($foundButtons)) {
            if ($expectedButtons[$i] !== $foundButtons[$i]) {
                $orderCorrect = false;
                echo "❌ Order mismatch at position " . ($i + 1) . ":\n";
                echo "   Expected: " . $expectedButtons[$i] . "\n";
                echo "   Found:    " . $foundButtons[$i] . "\n";
            }
        } else {
            $orderCorrect = false;
            echo "❌ Missing button at position " . ($i + 1) . ": " . $expectedButtons[$i] . "\n";
        }
    }
    
    // Check if we have the correct number of buttons
    if (count($foundButtons) !== count($expectedButtons)) {
        echo "❌ Button count mismatch:\n";
        echo "   Expected: " . count($expectedButtons) . " buttons\n";
        echo "   Found:    " . count($foundButtons) . " buttons\n";
        $orderCorrect = false;
    }
    
    // Check that "Ожидание" button is excluded
    if (strpos($methodContent, 'Ожидание') !== false) {
        echo "❌ 'Ожидание' button should be excluded but was found\n";
        $orderCorrect = false;
    } else {
        echo "✅ 'Ожидание' button correctly excluded\n";
    }
    
    if ($orderCorrect) {
        echo "\n✅ SUCCESS: All buttons are in the correct order and have proper styling!\n";
    } else {
        echo "\n❌ FAILURE: Button order or styling issues found.\n";
    }
} else {
    echo "❌ FAILURE: Could not find createMobileBrigadeKeyboard method\n";
}

echo "\nVerification complete.\n";