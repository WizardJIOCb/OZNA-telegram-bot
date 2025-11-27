<?php
$message = "<b>Назаров Андрей Юрьевич</b>
✅19.10.2025, Проект <b>ОИ886</b>: Добавлена запись в табель: <b>🛣️ Дорога</b>";

echo "Message contains:\n";
echo $message . "\n\n";

// Extract emoji
preg_match('/табель: <b>(.*?) Дорога/', $message, $matches);
if ($matches) {
    $emoji = $matches[1];
    echo "Emoji found: '{$emoji}'\n";
    echo "Hex: ";
    for ($i = 0; $i < mb_strlen($emoji, 'UTF-8'); $i++) {
        $char = mb_substr($emoji, $i, 1, 'UTF-8');
        echo bin2hex($char) . " ";
    }
    echo "\n";
}

echo "\nChecking different emojis:\n";
echo "🚗 - car (U+1F697)\n";
echo "🛣️ - motorway (U+1F6E3 + U+FE0F)\n";

echo "\nTesting strpos:\n";
echo "strpos with 🚗: " . var_export(strpos($message, '🚗'), true) . "\n";
echo "strpos with 🛣️: " . var_export(strpos($message, '🛣️'), true) . "\n";
echo "strpos with 🛣: " . var_export(strpos($message, '🛣'), true) . "\n";
