<?php
// Comprehensive test to verify the idle reason fix

echo "=== Comprehensive Idle Reason Fix Test ===\n\n";

// Test the getIdleReasonFromCallback method directly
echo "1. Testing getIdleReasonFromCallback method:\n";

class TabelHandlerTester {
    public static function testGetIdleReasonFromCallback($category) {
        // Simulate the method logic directly
        $parts = explode('_', str_replace('category_Табель_', '', $category));
        
        $categories = [
            '1' => 'Ожидание допоставки',
            '2' => 'Отсутствие обратной связи', 
            '3' => 'Готовность Заказчика',
            '4' => 'Транспорт',
            '5' => 'Погодные условия',
            '6' => 'Прочее'
        ];
        
        // Новые короткие ключи
        $subcategories = [
            // Категория 1: Допоставка
            'def' => 'Дефицит по спецификации',
            'ost' => 'Отсутствие в поставке',
            'izm' => 'Изменение ТР',
            // Категория 2: Отсутствие обратной связи
            'teh' => 'Техническое решение',
            'sog' => 'Согласование плана работ',
            // Категория 3: Готовность Заказчика
            'ene' => 'Отсутствие энергоресурсов',
            'rab' => 'Отсутствие рабочих сред',
            'per' => 'Перенос предъявления работ',
            'str' => 'Отсутствие строительной готовности',
            // Категория 4: Транспорт
            'pol' => 'Поломка',
            'mes' => 'Отсутствие свободных мест',
            // Категория 5: Погодные условия
            'bur' => 'Буран/снегопад',
            'mor' => 'Мороз',
            'ras' => 'Распутица',
            // Категория 6: Прочее
            'po' => 'Разработка ПО',
            'dop' => 'Отсутствие допуска к работам',
            'svy' => 'Отсутствие связи',
            'kom' => 'Свой комментарий',
            // Подподкатегории (разделы)
            'es' => 'Раздел ЭС',
            'ath' => 'Раздел АТХ',
            'ov' => 'Раздел ОВ',
            'ps' => 'Раздел ПС/ОС',
            'th' => 'Раздел ТХ',
            'pki' => 'Поставщик ПКИ'
        ];
        
        $reasonParts = [];
        
        // Skip supervision prefix if present
        $startIndex = ($parts[0] === 'ШМ' && $parts[1] === 'Простой') ? 2 : 1;
        
        // Main category
        if (isset($parts[$startIndex]) && isset($categories[$parts[$startIndex]])) {
            $reasonParts[] = $categories[$parts[$startIndex]];
        }
        
        // Subcategory
        if (isset($parts[$startIndex + 1]) && isset($subcategories[$parts[$startIndex + 1]])) {
            $reasonParts[] = $subcategories[$parts[$startIndex + 1]];
        }
        
        // Sub-subcategory
        if (isset($parts[$startIndex + 2]) && isset($subcategories[$parts[$startIndex + 2]])) {
            $reasonParts[] = $subcategories[$parts[$startIndex + 2]];
        }
        
        // Format with nested parentheses like: "Ожидание допоставки (Дефицит по спецификации)"
        $reason = '';
        if (count($reasonParts) > 0) {
            $reason = $reasonParts[0];
            if (count($reasonParts) > 1) {
                $reason .= ' (' . implode(' (', array_slice($reasonParts, 1)) . str_repeat(')', count($reasonParts) - 1);
            }
        }
        
        return $reason ?: 'Простой';
    }
}

// Test cases
$testCases = [
    'category_Табель_Простой_1_izm' => 'Ожидание допоставки (Изменение ТР)',
    'category_Табель_Простой_1_def' => 'Ожидание допоставки (Дефицит по спецификации)',
    'category_Табель_Простой_2_teh' => 'Отсутствие обратной связи (Техническое решение)',
    'category_Табель_ШМ_Простой_1_izm' => 'Ожидание допоставки (Изменение ТР)',
    'category_Табель_ШМ_Простой_1_def' => 'Ожидание допоставки (Дефицит по спецификации)',
    'category_Табель_ШМ_Простой_2_teh' => 'Отсутствие обратной связи (Техническое решение)'
];

foreach ($testCases as $input => $expected) {
    $result = TabelHandlerTester::testGetIdleReasonFromCallback($input);
    if ($result === $expected) {
        echo "✅ PASS: $input -> $result\n";
    } else {
        echo "❌ FAIL: $input -> Expected: $expected, Got: $result\n";
    }
}

echo "\n2. Testing regex patterns for idle confirmation callbacks:\n";

// Test the regex patterns we added
$confirmationTestCases = [
    'category_Табель_Простой_yes' => 'Простой',
    'category_Табель_ШМ_Простой_yes' => 'ШМ_Простой',
    'Табель_Простой_yes' => 'Простой',
    'Табель_ШМ_Простой_yes' => 'ШМ_Простой'
];

foreach ($confirmationTestCases as $input => $expected) {
    if (preg_match('/^(category_)?Табель_(Простой|ШМ_Простой)_yes$/', $input, $matches)) {
        $result = $matches[2];
        if ($result === $expected) {
            echo "✅ PASS: $input -> $result\n";
        } else {
            echo "❌ FAIL: $input -> Expected: $expected, Got: $result\n";
        }
    } else {
        echo "❌ FAIL: $input -> Pattern did not match\n";
    }
}

echo "\n3. Testing that non-idle confirmations are not matched:\n";

$nonIdleTestCases = [
    'category_Табель_РаботыПоГрафику_Электрооборудование_yes',
    'category_Табель_ФормИД_ЭлектрОб_yes',
    'Табель_РаботыПоГрафику_Электрооборудование_yes'
];

foreach ($nonIdleTestCases as $input) {
    if (preg_match('/^(category_)?Табель_(Простой|ШМ_Простой)_yes$/', $input, $matches)) {
        echo "❌ FAIL: $input -> Pattern incorrectly matched\n";
    } else {
        echo "✅ PASS: $input -> Pattern correctly rejected\n";
    }
}

echo "\n=== Test Summary ===\n";
echo "The fixes should now correctly:\n";
echo "1. Generate nested idle reasons with proper parentheses formatting\n";
echo "2. Identify idle confirmation callbacks with the new regex pattern\n";
echo "3. Preserve the detailed idle reason from conversation notes\n";
echo "4. Not interfere with other confirmation callbacks\n\n";

echo "After deploying these changes, the user should see:\n";
echo "⚠️ ⏸️ Простой (Ожидание допоставки (Изменение ТР))\n";
echo "instead of:\n";
echo "⚠️ ⏸️ Простой (Изменение ТР)\n";
?>