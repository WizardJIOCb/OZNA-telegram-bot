<?php

echo "=== INVESTIGATING BUTTON FUNCTIONALITY ===\n\n";

// Let's check what's happening with different types of buttons
echo "🔍 ANALYZING BUTTON BEHAVIOR\n";
echo "============================\n";

// Check recent logs to see which buttons work and which don't
echo "📋 CHECKING RECENT BOT ACTIVITY:\n";
echo "---------------------------------\n";

$log_file = 'c:\ozna_bot\telegram_bot_error.log';
if (file_exists($log_file)) {
    // Get last 50 lines to see recent activity
    $lines = file($log_file);
    $recent_lines = array_slice($lines, -50);
    
    echo "Recent callback data received:\n";
    foreach ($recent_lines as $line) {
        if (strpos($line, 'callback_data:') !== false) {
            echo "  " . trim($line) . "\n";
        }
    }
} else {
    echo "Log file not found\n";
}

echo "\n🔄 TESTING BUTTON FLOW ANALYSIS\n";
echo "===============================\n";

// Let's analyze the flow for different button types

// 1. Main Menu Buttons (these work)
echo "1. MAIN MENU BUTTONS (работают):\n";
echo "   - Табель → category_Табель\n";
echo "   - Профиль → category_Профиль\n";
echo "   - These work because they use simple category_ prefixes\n\n";

// 2. Submenu Buttons (these might fail)
echo "2. SUBMENU BUTTONS (могут не работать):\n";
echo "   - Шеф-монтаж → cat_ШМ or cat_ШМР_...\n";
echo "   - Подготовительные работы → cat_ШМР_ПодгРаб\n";
echo "   - These use shorter cat_ prefixes with Cyrillic text\n\n";

// 3. Check the callback routing system
echo "🔧 CHECKING CALLBACK ROUTING SYSTEM\n";
echo "===================================\n";

$callbackquery_file = 'c:\ozna_bot\CustomCommands\CallbackqueryCommand.php';
if (file_exists($callbackquery_file)) {
    $content = file_get_contents($callbackquery_file);
    
    // Check how callbacks are routed
    if (strpos($content, 'category_') !== false) {
        echo "✅ Found category_ routing in CallbackqueryCommand\n";
    }
    
    if (strpos($content, 'cat_') !== false) {
        echo "✅ Found cat_ routing in CallbackqueryCommand\n";
    }
    
    // Check if there's a handler factory
    if (strpos($content, 'CallbackHandlerFactory') !== false) {
        echo "✅ Found CallbackHandlerFactory usage\n";
    }
    
    // Check for encoding issues
    if (strpos($content, 'fixKnownCyrillicPatterns') !== false) {
        echo "✅ Found encoding fix in CallbackqueryCommand\n";
    } else {
        echo "⚠️ No encoding fix found in CallbackqueryCommand\n";
    }
} else {
    echo "❌ CallbackqueryCommand.php not found\n";
}

echo "\n🔍 ANALYZING THE PROBLEM\n";
echo "========================\n";

echo "ТЕОРИЯ: Кнопки главного меню работают, потому что:\n";
echo "1. Они используют простые английские префиксы (category_)\n";
echo "2. Они не содержат кириллицу в callback_data\n";
echo "3. Они обрабатываются до проверки кодировки\n\n";

echo "Кнопки подменю не работают, потому что:\n";
echo "1. Они содержат кириллические символы\n";
echo "2. Telegram искажает кодировку при передаче\n";
echo "3. Система не находит обработчик для искаженного текста\n\n";

echo "📊 РЕКОМЕНДУЕМАЯ ДИАГНОСТИКА\n";
echo "============================\n";
echo "1. Проверить CallbackqueryCommand на наличие исправления кодировки\n";
echo "2. Добавить логирование всех входящих callback_data\n";
echo "3. Проверить, применяется ли fixKnownCyrillicPatterns ко всем callback'ам\n";

?>