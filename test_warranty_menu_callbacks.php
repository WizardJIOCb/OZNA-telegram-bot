<?php

// Test the callback data for warranty equipment menu items
$warrantyMenuCallbacks = [
    'cat_Гарантии_ЭлектрОб',
    'cat_Гарантии_СрИзм',
    'cat_Гарантии_НасОб',
    'cat_Гарантии_ТрубАрм',
    'cat_Гарантии_ИспМех',
    'cat_Гарантии_КонтрОб',
    'cat_Гарантии_Прочее'
];

echo "Testing warranty equipment menu callback data:\n";
echo "==========================================\n";

foreach ($warrantyMenuCallbacks as $callback) {
    $utf8Length = strlen($callback);
    
    echo "Callback: $callback\n";
    echo "  Length: $utf8Length bytes\n";
    echo "  Within Telegram 64-byte limit: " . ($utf8Length <= 64 ? "YES" : "NO") . "\n\n";
}

// Test confirmation callback data
echo "Testing warranty equipment confirmation callback data:\n";
echo "==================================================\n";

$warrantyConfirmationCallbacks = [
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes',
    'cat_Гарантии_НасОб_yes',
    'cat_Гарантии_ТрубАрм_yes',
    'cat_Гарантии_ИспМех_yes',
    'cat_Гарантии_КонтрОб_yes',
    'cat_Гарантии_Прочее_yes'
];

foreach ($warrantyConfirmationCallbacks as $callback) {
    $utf8Length = strlen($callback);
    
    echo "Callback: $callback\n";
    echo "  Length: $utf8Length bytes\n";
    echo "  Within Telegram 64-byte limit: " . ($utf8Length <= 64 ? "YES" : "NO") . "\n\n";
}

?>