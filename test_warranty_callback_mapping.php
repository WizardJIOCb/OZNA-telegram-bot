<?php

// Test the callback data mapping for warranty equipment
$testCallbacks = [
    'cat_Гарантии_ЭлектрОб' => 'Табель_РабГарантии_ЭлектрОб',
    'cat_Гарантии_СрИзм' => 'Табель_РабГарантии_СрИзм',
    'cat_Гарантии_НасОб' => 'Табель_РабГарантии_НасОб',
    'cat_Гарантии_ТрубАрм' => 'Табель_РабГарантии_ТрубАрм',
    'cat_Гарантии_ИспМех' => 'Табель_РабГарантии_ИспМех',
    'cat_Гарантии_КонтрОб' => 'Табель_РабГарантии_КонтрОб',
    'cat_Гарантии_Прочее' => 'Табель_РабГарантии_Прочее',
    'cat_Гарантии_ЭлектрОб_yes' => 'РабГарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes' => 'РабГарантии_СрИзм_yes',
    'cat_Гарантии_НасОб_yes' => 'РабГарантии_НасОб_yes',
    'cat_Гарантии_ТрубАрм_yes' => 'РабГарантии_ТрубАрм_yes',
    'cat_Гарантии_ИспМех_yes' => 'РабГарантии_ИспМех_yes',
    'cat_Гарантии_КонтрОб_yes' => 'РабГарантии_КонтрОб_yes',
    'cat_Гарантии_Прочее_yes' => 'РабГарантии_Прочее_yes'
];

echo "Testing warranty equipment callback data mapping:\n";
echo "=============================================\n";

foreach ($testCallbacks as $input => $expectedOutput) {
    $utf8Length = strlen($input);
    
    echo "Input: $input\n";
    echo "  Length: $utf8Length bytes\n";
    echo "  Expected output: $expectedOutput\n";
    echo "  Within Telegram 64-byte limit: " . ($utf8Length <= 64 ? "YES" : "NO") . "\n\n";
}

?>