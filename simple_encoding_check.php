<?php

echo "=== ENCODING CHECK ===\n";

// Check the actual error from logs
$corrupted = 'cat_РЁРњР _РџРѕРґРіР Р°Р±';
$correct = 'cat_ШМР_ПодгРаб';

echo "Corrupted: '$corrupted'\n";
echo "Correct: '$correct'\n";
echo "Corrupted hex: " . bin2hex($corrupted) . "\n";
echo "Correct hex: " . bin2hex($correct) . "\n";

// Check if TabelHandler has encoding fix methods
if (file_exists('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php')) {
    $content = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');
    
    if (strpos($content, 'fixKnownCyrillicPatterns') !== false) {
        echo "✅ fixKnownCyrillicPatterns method found\n";
    } else {
        echo "❌ fixKnownCyrillicPatterns method NOT found\n";
    }
    
    if (strpos($content, 'ensureUtf8Encoding') !== false) {
        echo "✅ ensureUtf8Encoding method found\n";
    } else {
        echo "❌ ensureUtf8Encoding method NOT found\n";
    }
} else {
    echo "❌ TabelHandler file not found\n";
}

?>