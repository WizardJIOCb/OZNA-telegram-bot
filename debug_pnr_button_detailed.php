<?php
// Detailed debug script to trace the PNR button execution path

echo "=== Detailed Debug for PNR Button Issue ===\n\n";

// Simulate the callback that would be sent when clicking "⚙️ Отдел ПНР"
$callbackData = 'category_Табель_Пусконаладка';
echo "1. Received callback data: $callbackData\n";

// Simulate the category extraction as done in CallbackHandlerFactory
$category = substr($callbackData, strlen('category_'));
echo "2. Extracted category: $category\n";

// Check if TabelHandler can handle this callback
function tabelHandlerCanHandle($callbackData) {
    // Special handling for electrical equipment callback with encoding issues
    // Check for both correctly encoded and corrupted versions
    if (strpos($callbackData, 'Электрооборудование') !== false || 
        strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
        strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
        strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
        return true;
    }
    
    // Check for department buttons (Отдел ПНР, Отдел ШМР, etc.)
    if (strpos($callbackData, 'category_Табель_Пусконаладка') === 0 ||
        strpos($callbackData, 'category_Табель_Шеф_монтаж') === 0 ||
        strpos($callbackData, 'category_Табель_Мобильная_Бригада') === 0 ||
        strpos($callbackData, 'category_Табель_Текущая') === 0) {
        return true;
    }
    
    // Check for both original and short callback formats
    $result = strpos($callbackData, 'category_Табель') === 0 || strpos($callbackData, 'cat_Таб') === 0;
    return $result;
}

$canHandle = tabelHandlerCanHandle($callbackData);
echo "3. TabelHandler canHandle result: " . ($canHandle ? 'YES' : 'NO') . "\n";

if ($canHandle) {
    echo "4. TabelHandler would process this callback\n";
    
    // Simulate the routing logic in TabelHandler::handle
    echo "5. In TabelHandler::handle method\n";
    echo "6. Category after extraction: $category\n";
    
    // Check if it matches the commissioning department condition
    if ($category === 'Табель_Пусконаладка') {
        echo "7. MATCHED: Category is 'Табель_Пусконаладка'\n";
        echo "8. Would call handleOtherWorkTypes with category: $category\n";
        
        // Simulate handleOtherWorkTypes
        echo "9. In handleOtherWorkTypes method\n";
        
        // Check the specific condition for Пусконаладка
        if ($category === 'Табель_Пусконаладка') {
            echo "10. MATCHED: Category is 'Табель_Пусконаладка' in handleOtherWorkTypes\n";
            echo "11. Would call handleCommissioningDateSelection()\n";
            
            // Simulate handleCommissioningDateSelection
            echo "12. In handleCommissioningDateSelection method\n";
            $datePrefix = 'category_Табель_Пусконаладка';
            echo "13. Using date selection prefix: $datePrefix\n";
            
            // Simulate creating the date selection keyboard
            echo "14. Creating date selection keyboard with prefix: $datePrefix\n";
            $keyboard = [
                [
                    ['text' => '📅 Сегодня', 'callback_data' => "{$datePrefix}_Сегодня"]
                ],
                [
                    ['text' => '📆 Вчера', 'callback_data' => "{$datePrefix}_Вчера"]
                ],
                [
                    ['text' => '📆 Позавчера', 'callback_data' => "{$datePrefix}_Позавчера"]
                ],
                [
                    ['text' => '📆 Другая дата', 'callback_data' => "{$datePrefix}_ДругаяДата"]
                ]
            ];
            
            echo "15. Created date selection keyboard:\n";
            foreach ($keyboard as $rowIndex => $row) {
                foreach ($row as $buttonIndex => $button) {
                    echo "    Button: {$button['text']} -> {$button['callback_data']}\n";
                }
            }
            
            echo "\n✅ SUCCESS: Date selection keyboard should be displayed!\n";
            echo "If it's not appearing, the issue might be:\n";
            echo "  - Telegram API message sending failure\n";
            echo "  - Network connectivity issues\n";
            echo "  - User access level restrictions\n";
            echo "  - Database connection problems\n";
        } else {
            echo "10. NO MATCH: Category is not 'Табель_Пусконаладка' in handleOtherWorkTypes\n";
        }
    } else {
        echo "7. NO MATCH: Category is not 'Табель_Пусконаладка'\n";
    }
} else {
    echo "4. TabelHandler would NOT process this callback\n";
}

echo "\n=== Debug Complete ===\n";
?>