<?php
// Test the commissioning flow in TabelHandler

// Simulate the category that comes from the callback
$category = 'Табель_ПусконаладочныеРаботы';

echo "=== Testing Commissioning Flow ===\n";
echo "Category: $category\n";
echo "Category length: " . strlen($category) . "\n";

// Test the specific condition we added
echo "\n1. Testing our new condition:\n";
if ($category === 'Табель_ПусконаладочныеРаботы') {
    echo "   MATCH: Would call handleCommissioningWorksMenu()\n";
} else {
    echo "   NO MATCH: Would NOT call handleCommissioningWorksMenu()\n";
}

// Test the older condition that might interfere
echo "\n2. Testing alternative conditions that might interfere:\n";

// Check if it's treated as an activity type
$strippedCategory = str_replace('Табель_', '', $category);
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы'
];

echo "   Stripped category: $strippedCategory\n";
echo "   Is in activityTypes: " . (in_array($strippedCategory, $activityTypes) ? 'YES' : 'NO') . "\n";

// Test the order of conditions
echo "\n3. Testing condition order (as in handleOtherWorkTypes):\n";
echo "   First checking: category === 'Табель_ПусконаладочныеРаботы'\n";
if ($category === 'Табель_ПусконаладочныеРаботы') {
    echo "   RESULT: This condition matches first - would call handleCommissioningWorksMenu()\n";
} else {
    echo "   First condition does not match\n";
    echo "   Then checking: stripped category in activityTypes\n";
    if (in_array($strippedCategory, $activityTypes)) {
        echo "   RESULT: This condition matches - would treat as activity confirmation\n";
    } else {
        echo "   RESULT: No conditions match - would show 'Неизвестная команда табеля'\n";
    }
}

echo "\n=== Test Complete ===\n";

// Test script to simulate the complete commissioning works flow

echo "=== Test Commissioning Works Flow ===\n";

// Test 1: Check if the commissioning keyboard is created correctly
echo "Test 1: Creating commissioning keyboard\n";

class MockMessageService {
    public function createCommissioningKeyboard() {
        return [
            [
                ['text' => '⚙️ Пусконаладочные работы', 'callback_data' => 'category_Табель_ПНЛ']
            ],
            [
                ['text' => '📂 Проектная деятельность', 'callback_data' => 'category_Табель_ПроектнаяДеятельность']
            ],
            [
                ['text' => '🔧 Устранение замечаний РК', 'callback_data' => 'category_Табель_УЗ_РК']
            ],
            [
                ['text' => '🛡️ Работы по гарантии', 'callback_data' => 'category_Табель_РабГарантии']
            ],
            [
                ['text' => '⏸️ Простой', 'callback_data' => 'category_Табель_Простой']
            ],
            [
                ['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']
            ]
        ];
    }
    
    public function createCommissioningWorksKeyboard() {
        return [
            [
                ['text' => '⚡ Электрооборудование', 'callback_data' => 'category_Табель_Электрооборудование']
            ],
            [
                ['text' => '🔥 Пожарной сигнализации', 'callback_data' => 'category_Табель_ПожарнойСигнализации']
            ],
            [
                ['text' => '❄️ Отопление и вентиляция', 'callback_data' => 'category_Табель_ОтоплениеИВентиляции']
            ],
            [
                ['text' => '⚙️ Технологическое оборудование', 'callback_data' => 'category_Табель_ТехнологическоеОборудование']
            ],
            [
                ['text' => '📏 Метрология', 'callback_data' => 'category_Табель_Метрология']
            ],
            [
                ['text' => '🔄 Комплексное опробование', 'callback_data' => 'category_Табель_КомплексноеОпробование']
            ],
            [
                ['text' => '📊 Режимная наладка', 'callback_data' => 'category_Табель_РежимнаяНаладка']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
            ]
        ];
    }
    
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "  -> Would send message to chat $chatId\n";
        echo "  -> Text: $text\n";
        if ($keyboard) {
            echo "  -> With keyboard containing " . count($keyboard) . " rows\n";
        }
        return "Mock server response";
    }
}

$messageService = new MockMessageService();
$commissioningKeyboard = $messageService->createCommissioningKeyboard();

echo "  -> Commissioning keyboard created with " . count($commissioningKeyboard) . " rows\n";
echo "  -> First button: " . $commissioningKeyboard[0][0]['text'] . " with callback " . $commissioningKeyboard[0][0]['callback_data'] . "\n";

// Test 2: Simulate button press
echo "\nTest 2: Simulating button press\n";
$callbackData = $commissioningKeyboard[0][0]['callback_data'];
echo "  -> Callback data: $callbackData\n";

// Extract category
$category = substr($callbackData, strlen('category_'));
echo "  -> Extracted category: $category\n";

// Test 3: Simulate routing
echo "\nTest 3: Simulating routing\n";

function routeTabelCallback($category, $messageService) {
    echo "  -> Routing category: $category\n";
    
    // Special handling for commissioning works menu (Пусконаладочные работы)
    // Using a unique callback to avoid conflicts with activity types
    if ($category === 'Табель_ПНЛ') {
        echo "  -> Matched Табель_ПНЛ\n";
        echo "  -> Calling handleCommissioningWorksMenu\n";
        return handleCommissioningWorksMenu('test_chat_id', $messageService);
    }
    
    echo "  -> No match found\n";
    return false;
}

function handleCommissioningWorksMenu($chatId, $messageService) {
    echo "  -> In handleCommissioningWorksMenu\n";
    $keyboard = $messageService->createCommissioningWorksKeyboard();
    return $messageService->sendMessage(
        $chatId, 
        '<b>⚙️ Пусконаладочные работы</b>\n\nВыберите тип оборудования:',
        $keyboard
    );
}

$result = routeTabelCallback($category, $messageService);
echo "  -> Routing result: " . ($result ? 'success' : 'failure') . "\n";

echo "\nTest completed!\n";
?>