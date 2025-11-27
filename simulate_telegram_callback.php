<?php
/**
 * Simulate the exact Telegram callback structure
 */

echo "=== Simulating Telegram Callback Structure ===\n";

// Simulate the exact structure that Telegram would send
$telegramCallbackData = [
    'update_id' => 123456789,
    'callback_query' => [
        'id' => '1234567890123456789',
        'from' => [
            'id' => 7897006540,
            'is_bot' => false,
            'first_name' => 'Test',
            'username' => 'testuser',
            'language_code' => 'ru'
        ],
        'message' => [
            'message_id' => 12345,
            'from' => [
                'id' => 7902640773,
                'is_bot' => true,
                'first_name' => 'OznaWork',
                'username' => 'OznaWork_bot'
            ],
            'chat' => [
                'id' => 7897006540,
                'first_name' => 'Test',
                'username' => 'testuser',
                'type' => 'private'
            ],
            'date' => time(),
            'text' => '⚡ Электрооборудование\n\nВыберите тип работ:',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабЭлОб_РабПоГр']
                    ]
                ]
            ]
        ],
        'chat_instance' => '1234567890123456789',
        'data' => 'cat_ТабЭлОб_РабПоГр'
    ]
];

$callbackData = $telegramCallbackData['callback_query']['data'];
echo "Callback data from Telegram: {$callbackData}\n";

// Test our processing
echo "\n--- Processing Callback ---\n";

// Simulate CallbackHandlerFactory processing
echo "1. CallbackHandlerFactory processing:\n";
$processedCallbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
echo "   After encoding fix: '{$processedCallbackData}'\n";

$processedCallbackData = fixKnownCyrillicPatterns($processedCallbackData);
echo "   After Cyrillic fix: '{$processedCallbackData}'\n";

$category = extractCategory($processedCallbackData);
echo "   Extracted category: '{$category}'\n";

// Check if this matches our expected category
$expectedCategory = 'Табель_РаботыПоГрафику_Электрооборудование';
if ($category === $expectedCategory) {
    echo "✅ SUCCESS: Category matches expected value\n";
    echo "Expected behavior: Direct execution without 'Неизвестная команда табеля'\n";
} else {
    echo "❌ FAILURE: Category does not match expected value\n";
    echo "Expected: '{$expectedCategory}'\n";
    echo "Actual: '{$category}'\n";
}

echo "\n=== Simulation Complete ===\n";

// Helper functions
function fixKnownCyrillicPatterns($str) {
    $replacements = [
        'Р Р°Р±РѕС‚С‹РїРѕРіСЂР°С„РёРєСѓ' => 'РаботыПоГрафику',
        'Р Р°Р±РџРѕР“СЂ' => 'РаботыПоГрафику',
        'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        if (strpos($str, $corrupted) !== false) {
            $str = str_replace($corrupted, $fixed, $str);
        }
    }
    
    return $str;
}

function extractCategory($callbackData) {
    // Handle short category format for electrical equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_РабПоГр' => 'Табель_РаботыПоГрафику_Электрооборудование',
            'cat_ТабЭлОб_ФормИД' => 'Табель_ФормированиеИД_Электрооборудование',
            'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзменениеСхемПодключения_Электрооборудование',
            'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтажКабельныхЛиний_Электрооборудование',
            'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РасключениеКабельныхЛиний_Электрооборудование',
            'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
            'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
            'cat_ТабЭлОб_Проч' => 'Табель_Прочее_Электрооборудование'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        return substr($callbackData, strlen('cat_'));
    }
    
    // Handle other categories
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    if (strpos($callbackData, 'date_') === 0) {
        return substr($callbackData, strlen('date_'));
    }
    
    if (strpos($callbackData, 'fio_') === 0) {
        return substr($callbackData, strlen('fio_'));
    }
    
    return '';
}
?>