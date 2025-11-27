<?php

echo "=== APPLYING ENCODING FIX ===\n";

$file = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
$content = file_get_contents($file);

// Find the end of the replacements array and add our fix
$search = "            'Р Р°Р±Р±РѕС‚С‹' => 'Работы'\n        ];";

$replace = "            'Р Р°Р±Р±РѕС‚С‹' => 'Работы',
            // CRITICAL FIX: Add the specific corrupted pattern we see in logs
            'РЁРњР _РџРѕРґРіР Р°Р±' => 'ШМР_ПодгРаб',
            'cat_РЁРњР _РџРѕРґРіР Р°Р±' => 'cat_ШМР_ПодгРаб'
        ];";

if (strpos($content, $search) !== false) {
    echo "Found replacement target\n";
    $new_content = str_replace($search, $replace, $content);
    
    if (file_put_contents($file, $new_content)) {
        echo "✅ Encoding fix applied successfully!\n";
    } else {
        echo "❌ Failed to write file\n";
    }
} else {
    echo "❌ Could not find replacement target\n";
    echo "Looking for: " . bin2hex($search) . "\n";
}

?>