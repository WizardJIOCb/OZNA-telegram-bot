<?php
/**
 * Debug the complete supervision milestone flow
 */

echo "=== ПОЛНАЯ ДИАГНОСТИКА SUPERVISION MILESTONE FLOW ===\n\n";

// Step 1: Test button callback
echo "1. ТЕСТ КНОПКИ MILESTONE:\n";
echo "========================\n";
$buttonCallback = 'cat_ШМР_ВхКонтр_ПодтВех';
echo "Button callback: $buttonCallback\n";

// This should map to ШМР_ВходнойКонтроль_ПодтВех via extractCategoryFromCallback
echo "After mapping should become: ШМР_ВходнойКонтроль_ПодтВех\n";

// Step 2: Test confirmation generation
echo "\n2. ТЕСТ ГЕНЕРАЦИИ ПОДТВЕРЖДЕНИЯ:\n";
echo "================================\n";
$mappedCategory = 'ШМР_ВходнойКонтроль_ПодтВех';
echo "Mapped category: $mappedCategory\n";

// The handler creates confirmation with:
$confirmationCallback = "{$mappedCategory}_yes";
echo "Confirmation callback: $confirmationCallback\n";
echo "Expected: ШМР_ВходнойКонтроль_ПодтВех_yes\n";

// Step 3: Test isActivityExecution
echo "\n3. ТЕСТ isActivityExecution:\n";
echo "============================\n";

function testIsActivityExecution($category) {
    // Test our new pattern: ШМР_(.+)_ПодтВех_yes
    if (preg_match('/^ШМР_(.+)_ПодтВех_yes$/u', $category, $matches)) {
        $activityName = $matches[1];
        echo "✅ Matches milestone pattern: $activityName\n";
        return true;
    }
    
    echo "❌ Does NOT match milestone pattern\n";
    return false;
}

$result = testIsActivityExecution($confirmationCallback);
echo "isActivityExecution result: " . ($result ? 'TRUE' : 'FALSE') . "\n";

// Step 4: Test handleActivityExecution
echo "\n4. ТЕСТ handleActivityExecution:\n";
echo "================================\n";

function testHandleActivityExecution($category) {
    if (preg_match('/^ШМР_(.+)_ПодтВех_yes$/u', $category, $matches)) {
        $activityName = $matches[1];
        $activityType = "ШМР_{$activityName}_ПодтВех";
        echo "✅ Activity type: $activityType\n";
        return $activityType;
    }
    
    echo "❌ No mapping found\n";
    return null;
}

$activityType = testHandleActivityExecution($confirmationCallback);

// Step 5: Check if TabelEntry has the activity
echo "\n5. ПРОВЕРКА TABELENTRY:\n";
echo "=======================\n";

$expectedActivityTypes = [
    'ШМР_ВходнойКонтроль_ПодтВех',
    'ШМР_ЗавозОборудования_ПодтВех',
    'ШМР_АнализИнформацииПоФундаменту_ПодтВех',
];

foreach ($expectedActivityTypes as $type) {
    echo "Activity type: $type - Should be in TabelEntry.php\n";
}

echo "\n=== ДИАГНОЗ ===\n";
echo "Если все шаги проходят успешно, но кнопка 'Да' не работает,\n";
echo "возможные причины:\n";
echo "1. Неправильная маршрутизация в handle() method\n";
echo "2. canHandle() не распознает callback\n";
echo "3. Проблема с routeTabelCallback()\n";
echo "4. Отсутствие activity type в TabelEntry\n";
echo "5. Проблема с созданием записи в БД\n\n";

echo "Для окончательной диагностики нужно проверить логи при нажатии 'Да'!\n";
?>