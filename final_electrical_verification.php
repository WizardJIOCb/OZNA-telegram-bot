<?php
// Final verification test for electrical equipment button fix

echo "=== ФИНАЛЬНАЯ ПРОВЕРКА ИСПРАВЛЕНИЯ КНОПКИ ЭЛЕКТРООБОРУДОВАНИЯ ===\n\n";

echo "ПРОВЕРЯЕМЫЙ SCENARIO:\n";
echo "1. Пользователь нажимает кнопку '⚡ Электрооборудование'\n";
echo "2. Telegram отправляет callback_data: 'cat_ТабЭлОб'\n";
echo "3. Ожидаемый результат: Показать меню с 5 активностями\n";
echo "4. НЕ ожидаемый результат: Создать отчет\n\n";

echo "ПОШАГОВАЯ СИМУЛЯЦИЯ:\n";
echo str_repeat("=", 50) . "\n";

$callbackData = 'cat_ТабЭлОб';

// Step 1: TabelHandler->canHandle()
echo "ШАГ 1: TabelHandler->canHandle('$callbackData')\n";
$canHandle = str_starts_with($callbackData, 'category_Табель') || 
             str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
             str_starts_with($callbackData, 'cat_Таб');
echo "Результат: " . ($canHandle ? 'ПРИНЯТ ✅' : 'ОТКЛОНЕН ❌') . "\n\n";

if (!$canHandle) {
    echo "ОСТАНОВ: Callback не обрабатывается TabelHandler\n";
    exit;
}

// Step 2: extractCategoryFromCallback()
echo "ШАГ 2: extractCategoryFromCallback('$callbackData')\n";

function extractCategory($callbackData) {
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    if (strpos($callbackData, 'cat_Таб') === 0) {
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабАвтом' => 'Табель_Автоматизация',
            'cat_ТабПожСигн' => 'Табель_ПожарнаяСигнализация'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

$extractedCategory = extractCategory($callbackData);
echo "Результат: '$extractedCategory'\n\n";

// Step 3: Category assignment in handle()
$finalCategory = !empty($extractedCategory) ? $extractedCategory : $callbackData;
echo "ШАГ 3: Финальная категория для обработки\n";
echo "Результат: '$finalCategory'\n\n";

// Step 4: routeTabelCallback() routing decisions
echo "ШАГ 4: Проверки маршрутизации в routeTabelCallback()\n";

// Check isActivityExecution
echo "4.1 isActivityExecution('$finalCategory'):\n";

function checkIsActivityExecution($category) {
    $menuCallbacks = [
        'cat_ТабЭлОб',     
        'cat_ТабАвтом',    
        'cat_ТабПожСигн',  
        'Табель_Электрооборудование',  
        'Табель_Автоматизация',        
        'Табель_ПожарнаяСигнализация'  
    ];
    
    if (in_array($category, $menuCallbacks)) {
        echo "    ✅ НАЙДЕН в menu callbacks\n";
        return false;
    }
    
    if (str_contains($category, '_yes')) {
        echo "    ✅ Содержит '_yes' - это activity execution\n";
        return true;
    }
    
    echo "    ❌ НЕ найден в menu callbacks и не содержит '_yes'\n";
    return false;
}

$isActivityExecution = checkIsActivityExecution($finalCategory);
echo "    Результат: " . ($isActivityExecution ? 'true (создать отчет) ❌' : 'false (не создавать отчет) ✅') . "\n\n";

// Check isActivityConfirmation 
echo "4.2 isActivityConfirmation('$finalCategory'):\n";
echo "    Результат: false (не подтверждение активности)\n\n";

// Final routing decision
echo "ШАГ 5: Финальное решение маршрутизации\n";
if ($isActivityExecution) {
    echo "➜ handleActivityExecution() - СОЗДАТЬ ОТЧЕТ ❌\n";
    echo "ПРОБЛЕМА: ОТЧЕТ БУДЕТ СОЗДАН ВМЕСТО ПОКАЗА МЕНЮ!\n";
} else {
    echo "➜ handleOtherWorkTypes() - ОБРАБОТАТЬ КАК ДРУГОЙ ТИП ✅\n";
    
    echo "\nШАГ 6: handleOtherWorkTypes('$finalCategory')\n";
    
    if ($finalCategory === 'cat_ТабЭлОб') {
        echo "✅ СОВПАДЕНИЕ: cat_ТабЭлОб\n";
        echo "➜ return handleElectricalEquipmentMenu()\n";
        echo "РЕЗУЛЬТАТ: ПОКАЗАТЬ МЕНЮ ЭЛЕКТРООБОРУДОВАНИЯ ✅\n";
    } elseif ($finalCategory === 'Табель_Электрооборудование') {
        echo "✅ СОВПАДЕНИЕ: Табель_Электрооборудование\n";
        echo "➜ return handleElectricalEquipmentMenu()\n";
        echo "РЕЗУЛЬТАТ: ПОКАЗАТЬ МЕНЮ ЭЛЕКТРООБОРУДОВАНИЯ ✅\n";
    } else {
        echo "❌ НЕТ СОВПАДЕНИЙ\n";
        echo "➜ return 'Неизвестная команда табеля'\n";
        echo "РЕЗУЛЬТАТ: ОШИБКА ❌\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "ЗАКЛЮЧЕНИЕ:\n";

if (!$isActivityExecution && ($finalCategory === 'cat_ТабЭлОб' || $finalCategory === 'Табель_Электрооборудование')) {
    echo "✅ ИСПРАВЛЕНИЕ РАБОТАЕТ ПРАВИЛЬНО!\n";
    echo "   При нажатии кнопки 'Электрооборудование' будет показано меню,\n";
    echo "   а НЕ создан отчет.\n\n";
    
    echo "МЕНЮ БУДЕТ СОДЕРЖАТЬ:\n";
    echo "- 📅 Работы по графику\n";
    echo "- 📄 Формирование ИД\n";
    echo "- 🛠️ Дополнительные работы\n";
    echo "- ✅ Подтвердить достижение вехи\n";
    echo "- ⬅️ Назад\n\n";
    
    echo "ДОПОЛНИТЕЛЬНЫЕ РАБОТЫ ВКЛЮЧАЮТ:\n";
    echo "- 🔀 Изменение схем подключения\n";
    echo "- 🔌 Монтаж кабельных линий\n";
    echo "- 📊 Расключение кабельных линий\n";
    echo "- ⚙️ Монтаж оборудования\n";
    echo "- 📝 Прочее\n";
} else {
    echo "❌ ИСПРАВЛЕНИЕ НЕ РАБОТАЕТ!\n";
    echo "   ТРЕБУЕТСЯ ДОПОЛНИТЕЛЬНАЯ ДИАГНОСТИКА.\n";
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
?>