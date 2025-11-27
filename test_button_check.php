<?php
echo "Testing button existence...\n";

$content = file_get_contents('CustomCommands/Services/MessageService.php');

// Check for the button
if (strpos($content, "'callback_data' => 'category_Табель_ИсполнениеОбязМастера'") !== false) {
    echo "✅ Button found in MessageService.php\n";
} else {
    echo "❌ Button NOT found in MessageService.php\n";
}

// Check the line specifically
$lines = file('CustomCommands/Services/MessageService.php');
foreach ($lines as $lineNum => $line) {
    if (strpos($line, 'Исполнение обязанностей мастера') !== false) {
        echo "Found button text on line " . ($lineNum + 1) . ": " . trim($line) . "\n";
    }
}