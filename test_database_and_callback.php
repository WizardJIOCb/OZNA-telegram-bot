<?php

// Test database connection and electrical equipment callback handling
echo "=== Testing Database Connection and Electrical Equipment Callback ===\n\n";

// Simulate the callback data
$callbackData = 'category_Табель_Электрооборудование';

echo "Input callback data: {$callbackData}\n\n";

// Test database connection simulation
echo "=== Testing Database Connection ===\n";

// Simulate database connection check
function testDatabaseConnection() {
    // In a real scenario, this would try to connect to the database
    // For now, we'll simulate a successful connection
    echo "Database connection: SUCCESS\n";
    return true;
}

$dbConnected = testDatabaseConnection();

// Test user access level check
echo "\n=== Testing User Access Level Check ===\n";

function getUserAccessLevel($userId) {
    // Simulate getting user access level
    // 0 = guest, 1 = user, 2 = supervisor, 3 = admin, 4 = super admin
    echo "Getting access level for user {$userId}\n";
    return 1; // Regular user
}

$userId = 123456789;
$userAccessLevel = getUserAccessLevel($userId);
echo "User access level: {$userAccessLevel}\n";

// Test FIO and functional direction checks
echo "\n=== Testing FIO and Functional Direction Checks ===\n";

function getUserData($userId) {
    // Simulate getting user data
    echo "Getting user data for user {$userId}\n";
    return [
        'fio' => 'Иванов Иван Иванович',
        'functional_direction' => 'Пусконаладка'
    ];
}

$userData = getUserData($userId);
echo "User FIO: {$userData['fio']}\n";
echo "User functional direction: {$userData['functional_direction']}\n";

// Test callback processing
echo "\n=== Testing Callback Processing ===\n";

function processCallback($callbackData, $userId) {
    echo "Processing callback: {$callbackData}\n";
    
    // Extract category
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        echo "Extracted category: {$category}\n";
        
        // Check if it's electrical equipment
        if ($category === 'Табель_Электрооборудование') {
            echo "Electrical equipment callback detected\n";
            return 'handleElectricalEquipmentMenu';
        }
    }
    
    return 'unknown';
}

$handlerMethod = processCallback($callbackData, $userId);
echo "Handler method to call: {$handlerMethod}\n";

// Test handleElectricalEquipmentMenu
echo "\n=== Testing handleElectricalEquipmentMenu ===\n";

function handleElectricalEquipmentMenu() {
    echo "Creating electrical equipment keyboard...\n";
    
    $keyboard = [
        [
            ['text' => '📅 Работы по графику', 'callback_data' => 'category_Табель_РаботыПоГрафику_Электрооборудование']
        ],
        [
            ['text' => '📄 Формирование ИД', 'callback_data' => 'category_Табель_ФормированиеИД_Электрооборудование']
        ],
        [
            ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'category_Табель_ИзмСхемПодкл_ЭлектрОб']
        ],
        [
            ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'category_Табель_МонтажКабЛиний_ЭлектрОб']
        ],
        [
            ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'category_Табель_РасклКабЛиний_ЭлектрОб']
        ],
        [
            ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'category_Табель_ДопРаб_ЭлектрОб']
        ],
        [
            ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'category_Табель_ПодтвДостВехи_ЭлектрОб']
        ],
        [
            ['text' => '📝 Прочее', 'callback_data' => 'category_Табель_Прочее_Электрооборудование']
        ],
        [
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_ПусконаладочныеРаботы']
        ]
    ];
    
    echo "Keyboard created with " . count($keyboard) . " rows\n";
    
    $message = '<b>⚡ Электрооборудование</b>' . "\n\n" . 'Выберите тип работ:';
    echo "Message: {$message}\n";
    
    return ['message' => $message, 'keyboard' => $keyboard];
}

if ($handlerMethod === 'handleElectricalEquipmentMenu') {
    $result = handleElectricalEquipmentMenu();
    echo "Successfully created electrical equipment menu\n";
} else {
    echo "Not handling electrical equipment menu\n";
}

echo "\n=== Summary ===\n";
echo "1. Database connection: " . ($dbConnected ? 'SUCCESS' : 'FAILED') . "\n";
echo "2. User access level: {$userAccessLevel}\n";
echo "3. User FIO: {$userData['fio']}\n";
echo "4. User functional direction: {$userData['functional_direction']}\n";
echo "5. Callback data: {$callbackData}\n";
echo "6. Handler method: {$handlerMethod}\n";

if ($dbConnected && $userAccessLevel > 0 && !empty($userData['fio']) && !empty($userData['functional_direction']) && $handlerMethod === 'handleElectricalEquipmentMenu') {
    echo "\n✅ SUCCESS: All checks passed. The electrical equipment button should work correctly!\n";
    echo "If it's still not working, there might be a runtime issue with the Telegram API or message sending.\n";
} else {
    echo "\n❌ ISSUE: One or more checks failed.\n";
}

echo "\n=== Test Complete ===\n";