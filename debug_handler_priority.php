<?php
// Debug script to check handler priority and callback processing

echo "=== Handler Priority Debug ===\n\n";

// Simulate the handler registration order and priorities
$handlers = [
    'AdminHandler' => 25,
    'AccessLevelHandler' => 25,
    'SupervisorHandler' => 25,
    'TabelHandler' => 20,
    'ProfileHandler' => 15,
    'FunctionalDirectionHandler' => 10,
    'HelpHandler' => 5,
    'MainMenuHandler' => 50
];

echo "Handler Registration Order and Priorities:\n";
echo "========================================\n";
$registrationOrder = [
    'AdminHandler',
    'AccessLevelHandler', 
    'SupervisorHandler',
    'TabelHandler',
    'ProfileHandler',
    'FunctionalDirectionHandler',
    'HelpHandler',
    'MainMenuHandler'
];

foreach ($registrationOrder as $index => $handler) {
    echo ($index + 1) . ". {$handler} (priority: {$handlers[$handler]})\n";
}

echo "\nSorted by Priority (highest first):\n";
echo "==================================\n";
arsort($handlers);
foreach ($handlers as $handler => $priority) {
    echo "{$handler} (priority: {$priority})\n";
}

echo "\n=== Testing Callback Processing ===\n";
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "Testing callback data: '{$callbackData}'\n\n";

// Simulate canHandle checks in priority order
echo "Handler canHandle checks (in priority order):\n";
echo "============================================\n";

// MainMenuHandler (priority 50)
$mainMenuCanHandle = ($callbackData === 'category_Главное');
echo "MainMenuHandler (priority 50) canHandle: " . ($mainMenuCanHandle ? 'YES' : 'NO') . "\n";

// AdminHandler (priority 25)
$adminCanHandle = (strpos($callbackData, 'category_Админка') === 0 || 
                  strpos($callbackData, 'category_Адм_') === 0 ||
                  strpos($callbackData, 'adm_req_') === 0) && 
                  strpos($callbackData, 'Электрооборудование') === false;
echo "AdminHandler (priority 25) canHandle: " . ($adminCanHandle ? 'YES' : 'NO') . "\n";

// AccessLevelHandler (priority 25)
$accessLevelCanHandle = (strpos($callbackData, 'category_УровеньДоступа') === 0 || 
                        strpos($callbackData, 'acc_lvl_') === 0);
echo "AccessLevelHandler (priority 25) canHandle: " . ($accessLevelCanHandle ? 'YES' : 'NO') . "\n";

// SupervisorHandler (priority 25)
$supervisorCanHandle = (strpos($callbackData, 'category_ШМ') === 0);
echo "SupervisorHandler (priority 25) canHandle: " . ($supervisorCanHandle ? 'YES' : 'NO') . "\n";

// TabelHandler (priority 20)
$tabelCanHandle = (strpos($callbackData, 'category_Табель') === 0 || 
                  strpos($callbackData, 'cat_Таб') === 0);
echo "TabelHandler (priority 20) canHandle: " . ($tabelCanHandle ? 'YES' : 'NO') . "\n";

echo "\nExpected Processing Flow:\n";
echo "========================\n";
echo "1. MainMenuHandler checks first (priority 50) - returns NO\n";
echo "2. AdminHandler checks (priority 25) - returns NO (doesn't match pattern)\n";
echo "3. AccessLevelHandler checks (priority 25) - returns NO\n";
echo "4. SupervisorHandler checks (priority 25) - returns NO\n";
echo "5. TabelHandler checks (priority 20) - returns YES (matches 'cat_Таб' pattern)\n";
echo "6. TabelHandler handles the callback\n";

echo "\n=== Actual Issue Analysis ===\n";
echo "The issue might be that the callback is being processed by a handler with higher priority\n";
echo "but that handler is not properly recognizing the electrical equipment callback.\n\n";

echo "Let's check if any handler is incorrectly claiming the callback:\n";

// Check for potential issues
if (strpos($callbackData, 'Электрооборудование') !== false) {
    echo "Callback contains 'Электрооборудование' - this should be handled by TabelHandler\n";
}

if (strpos($callbackData, 'cat_Таб') === 0) {
    echo "Callback starts with 'cat_Таб' - this should be handled by TabelHandler\n";
}

echo "\nIf the callback is not being handled correctly, it might be because:\n";
echo "1. The TabelHandler's canHandle method is not recognizing the callback properly\n";
echo "2. There's an encoding issue with the callback data\n";
echo "3. The handler registration order is causing a different handler to process it\n";
echo "4. The callback data is being modified before reaching the handler factory\n";