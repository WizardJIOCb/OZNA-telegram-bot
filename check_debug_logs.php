<?php
echo "=== Debug Logs Check ===\n\n";

// Проверяем debug_textmenu.log
echo "TextmenuCommand Debug Log:\n";
echo str_repeat("-", 40) . "\n";
if (file_exists(__DIR__ . '/debug_textmenu.log')) {
    $content = file_get_contents(__DIR__ . '/debug_textmenu.log');
    if (!empty($content)) {
        echo $content;
    } else {
        echo "Log file is empty\n";
    }
} else {
    echo "debug_textmenu.log not found\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Проверяем debug_generic.log
echo "GenericmessageCommand Debug Log:\n";
echo str_repeat("-", 40) . "\n";
if (file_exists(__DIR__ . '/debug_generic.log')) {
    $content = file_get_contents(__DIR__ . '/debug_generic.log');
    if (!empty($content)) {
        echo $content;
    } else {
        echo "Log file is empty\n";
    }
} else {
    echo "debug_generic.log not found\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "End of debug logs\n";
?>