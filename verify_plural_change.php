<?php
echo "=== Verifying Plural Form Change ===\n\n";

$files = [
    'MessageService.php' => __DIR__ . '/CustomCommands/Services/MessageService.php',
    'TabelEntry.php' => __DIR__ . '/CustomCommands/Services/TabelEntry.php'
];

$oldText = 'Работа неучтенная в ВОР';
$newText = 'Работы неучтенные в ВОР';

foreach ($files as $name => $path) {
    $content = file_get_contents($path);
    
    $oldCount = substr_count($content, $oldText);
    $newCount = substr_count($content, $newText);
    
    echo "$name:\n";
    echo "  Old text ('$oldText'): $oldCount occurrences\n";
    echo "  New text ('$newText'): $newCount occurrences\n";
    
    if ($oldCount === 0 && $newCount === 6) {
        echo "  ✅ SUCCESS - All changed to plural form!\n\n";
    } else {
        echo "  ❌ ISSUE - Not all occurrences changed\n\n";
    }
}

echo "\n✅ Verification complete!\n";
echo "Changed from: '$oldText' (singular)\n";
echo "Changed to:   '$newText' (plural)\n";
