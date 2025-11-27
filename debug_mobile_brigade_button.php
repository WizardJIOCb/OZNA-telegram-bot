<?php
// Debug script to understand what's happening with the mobile brigade button

echo "Debugging Mobile Brigade Button Issue\n";
echo "====================================\n\n";

// Simulate the exact callback data that should be sent when pressing the mobile brigade button
$callbackData = 'category_Табель_Мобильная_Бригада';

echo "1. Callback data sent by mobile brigade button:\n";
echo "   $callbackData\n\n";

// Test each handler's canHandle method
echo "2. Testing each handler's canHandle method:\n";

$handlers = [
    'AdminHandler' => function($data) { 
        return strpos($data, 'category_Админка') === 0 || 
               strpos($data, 'category_Адм_') === 0 || 
               $data === 'category_Главное' || 
               strpos($data, 'adm_req_') === 0; 
    },
    'AccessLevelHandler' => function($data) { 
        return strpos($data, 'category_УровеньДоступа') === 0 || 
               $data === 'acc_lvl_users' || 
               $data === 'acc_lvl_manage_users' || 
               strpos($data, 'acc_lvl_user_details_') === 0 || 
               strpos($data, 'acc_lvl_set_level_') === 0;
    },
    'SupervisorHandler' => function($data) { 
        return strpos($data, 'category_Супервайзер') === 0; 
    },
    'TabelHandler' => function($data) { 
        return strpos($data, 'category_Табель') === 0; 
    },
    'ProfileHandler' => function($data) { 
        return strpos($data, 'category_Профиль') === 0; 
    },
    'FunctionalDirectionHandler' => function($data) { 
        return strpos($data, 'category_Профиль_ФН') === 0; 
    },
    'HelpHandler' => function($data) { 
        return strpos($data, 'category_Помощь') === 0; 
    },
    'MainMenuHandler' => function($data) { 
        return $data === 'category_Главное'; 
    }
];

foreach ($handlers as $handlerName => $canHandleFunction) {
    $result = $canHandleFunction($callbackData) ? 'YES' : 'NO';
    echo "   $handlerName canHandle: $result\n";
}

echo "\n3. Handler priorities:\n";
$priorities = [
    'AdminHandler' => 25,
    'AccessLevelHandler' => 25,
    'SupervisorHandler' => 25,
    'TabelHandler' => 70,  // Our fix
    'ProfileHandler' => 15,
    'FunctionalDirectionHandler' => 10,
    'HelpHandler' => 1,
    'MainMenuHandler' => 50
];

// Sort by priority (highest first)
uasort($priorities, function($a, $b) {
    return $b - $a;
});

echo "   Processing order:\n";
foreach ($priorities as $handler => $priority) {
    echo "   $priority. $handler\n";
}

echo "\n4. Expected flow:\n";
echo "   - TabelHandler should handle the callback (priority 70)\n";
echo "   - TabelHandler->handle() should be called\n";
echo "   - Category extracted: 'Табель_Мобильная_Бригада'\n";
echo "   - routeTabelCallback() should route to handleOtherWorkTypes()\n";
echo "   - handleOtherWorkTypes() should detect mobile brigade and call handleMobileBrigadeDateSelection()\n";
echo "   - User should see date selection menu\n";

echo "\n5. Let's check the exact routing logic in handleOtherWorkTypes:\n";

$category = 'Табель_Мобильная_Бригада';
echo "   Category: $category\n";

$conditions = [
    "category === 'Табель_Пусконаладка'" => $category === 'Табель_Пусконаладка',
    "category === 'Табель_Шеф_монтаж'" => $category === 'Табель_Шеф_монтаж',
    "category === 'Табель_Мобильная_Бригада' || category === 'Табель_МобБриг'" => 
        ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг'),
    "category === 'Табель_Текущая'" => $category === 'Табель_Текущая'
];

foreach ($conditions as $condition => $result) {
    echo "   $condition: " . ($result ? 'TRUE' : 'FALSE') . "\n";
}

echo "\n6. If all the above is correct, the issue might be:\n";
echo "   - The button is not actually sending the expected callback data\n";
echo "   - There's an issue in the Telegram bot's callback handling mechanism\n";
echo "   - There's a database or user access issue preventing the flow\n";
echo "   - The message is being sent but not displayed to the user\n";

echo "\nDebug completed.\n";