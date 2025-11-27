<?php
// Comprehensive test to simulate the entire mobile brigade button flow

echo "Comprehensive Mobile Brigade Flow Test\n";
echo "====================================\n\n";

// Simulate the exact flow when mobile brigade button is pressed
echo "1. User presses 'Мобильная бригада' button\n";
echo "   - Button callback_data: 'category_Табель_Мобильная_Бригада'\n\n";

echo "2. CallbackHandlerFactory receives callback\n";
$callbackData = 'category_Табель_Мобильная_Бригада';
echo "   - Raw callback_data: $callbackData\n";

// Simulate encoding fix
$callbackData = fixEncoding($callbackData);
echo "   - After encoding fix: $callbackData\n";

// Simulate category extraction
$category = extractCategory($callbackData);
echo "   - Extracted category: $category\n\n";

echo "3. Handler processing order:\n";
$handlers = [
    'TabelHandler' => 70,
    'MainMenuHandler' => 50,
    'AdminHandler' => 25,
    'AccessLevelHandler' => 25,
    'SupervisorHandler' => 25,
    'ProfileHandler' => 15,
    'FunctionalDirectionHandler' => 10,
    'HelpHandler' => 1
];

// Sort by priority
uasort($handlers, function($a, $b) {
    return $b - $a;
});

$processingOrder = 1;
foreach ($handlers as $handlerName => $priority) {
    echo "   $processingOrder. $handlerName (priority: $priority)\n";
    $processingOrder++;
}
echo "\n";

echo "4. TabelHandler processes callback:\n";
echo "   - canHandle('$callbackData'): " . (strpos($callbackData, 'category_Табель') === 0 ? 'TRUE' : 'FALSE') . "\n";
echo "   - handle() method called\n";
echo "   - Category passed to handle(): '$category'\n\n";

echo "5. TabelHandler routing:\n";
echo "   - routeTabelCallback() called with category: '$category'\n";
echo "   - Routing logic check:\n";

$conditions = [
    "category === 'Табель'" => $category === 'Табель',
    "projectSelector->isDateSelection(category)" => false, // Not a date selection
    "projectSelector->isProjectSelection(category)" => false, // Not a project selection
    "category === 'Табель_Мобильная_Бригада_Дата'" => $category === 'Табель_Мобильная_Бригада_Дата',
    "category === 'Табель_ПодготовкаТКП_Меню'" => $category === 'Табель_ПодготовкаТКП_Меню',
    "category === 'Табель_Текущая_Назад'" => $category === 'Табель_Текущая_Назад',
    "category === 'Табель_Пусконаладка_Back'" => $category === 'Табель_Пусконаладка_Back',
    "category === 'Табель_ШМ_Back'" => $category === 'Табель_ШМ_Back',
    "isIdleMenuCallback(category)" => ($category === 'Табель_Простой' || $category === 'Табель_ШМ_Простой'),
    "isActivityExecution(category)" => false, // Not an execution
    "isActivityConfirmation(category)" => false, // Not a confirmation
    "default: handleOtherWorkTypes()" => true // This should be the path
];

foreach ($conditions as $condition => $result) {
    echo "     $condition: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    if ($result && $condition !== "default: handleOtherWorkTypes()") {
        echo "   - Routing to specific handler\n";
        break;
    } elseif ($condition === "default: handleOtherWorkTypes()") {
        echo "   - Routing to handleOtherWorkTypes()\n";
    }
}
echo "\n";

echo "6. handleOtherWorkTypes() processing:\n";
echo "   - Category: '$category'\n";
echo "   - Condition check:\n";

$otherConditions = [
    "category === 'Табель_Пусконаладка'" => $category === 'Табель_Пусконаладка',
    "category === 'Табель_Шеф_монтаж'" => $category === 'Табель_Шеф_монтаж',
    "category === 'Табель_Мобильная_Бригада' || category === 'Табель_МобБриг'" => 
        ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг'),
    "category === 'Табель_Текущая'" => $category === 'Табель_Текущая'
];

foreach ($otherConditions as $condition => $result) {
    echo "     $condition: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    if ($result) {
        echo "   - Routing to specific handler for mobile brigade\n";
        break;
    }
}
echo "\n";

echo "7. Mobile brigade handling:\n";
if ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг') {
    echo "   - Calling handleMobileBrigadeDateSelection()\n";
    echo "   - Creating date selection keyboard with prefix: 'category_Табель_Мобильная_Бригада'\n";
    echo "   - Sending message: '💼 <b>Выберите дату для Мобильной бригады</b>'\n";
    echo "   - Keyboard buttons:\n";
    echo "     - 📅 Сегодня (callback: category_Табель_Мобильная_Бригада_Сегодня)\n";
    echo "     - 📆 Вчера (callback: category_Табель_Мобильная_Бригада_Вчера)\n";
    echo "     - 📆 Позавчера (callback: category_Табель_Мобильная_Бригада_Позавчера)\n";
    echo "     - 📆 Другая дата (callback: category_Табель_Мобильная_Бригада_ДругаяДата)\n";
    echo "\n";
    echo "8. Expected user experience:\n";
    echo "   - User should see message: '💼 Выберите дату для Мобильной бригады'\n";
    echo "   - User should see date selection buttons\n";
    echo "   - User should be able to select a date\n";
} else {
    echo "   - ERROR: Mobile brigade condition not matched\n";
}

echo "\nTest completed.\n";

// Helper functions
function fixEncoding($str) {
    // Simulate encoding fix
    return $str;
}

function extractCategory($callbackData) {
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    return '';
}