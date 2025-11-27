<?php
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

echo "=== ПРОВЕРКА ТЕКУЩЕГО СОСТОЯНИЯ МЕНЮ ЭЛЕКТРООБОРУДОВАНИЯ ===\n\n";

try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    echo "1. Проверка основного меню электрооборудования:\n";
    echo "============================================\n";
    
    $electricalKeyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "Создано меню с " . count($electricalKeyboard) . " кнопками:\n\n";
    
    foreach ($electricalKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  • " . $button['text'] . " → " . $button['callback_data'] . "\n";
        }
    }
    
    echo "\n2. Проверка меню дополнительных работ:\n";
    echo "=====================================\n";
    
    $additionalWorksKeyboard = $messageService->createAdditionalWorksElectricalKeyboard();
    echo "Создано подменю с " . count($additionalWorksKeyboard) . " кнопками:\n\n";
    
    foreach ($additionalWorksKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  • " . $button['text'] . " → " . $button['callback_data'] . "\n";
        }
    }
    
    echo "\n3. Ожидаемая структура меню:\n";
    echo "===========================\n";
    
    echo "Основное меню 'Электрооборудование':\n";
    echo "  1. 📅 Работы по графику\n";
    echo "  2. 📄 Формирование ИД\n";
    echo "  3. 🛠️ Дополнительные работы\n";
    echo "  4. ✅ Подтвердить достижение вехи\n";
    echo "  5. ⬅️ Назад\n\n";
    
    echo "Подменю 'Дополнительные работы':\n";
    echo "  1. 🔀 Изменение схем подключения\n";
    echo "  2. 🔌 Монтаж кабельных линий\n";
    echo "  3. 📊 Расключение кабельных линий\n";
    echo "  4. ⚙️ Монтаж оборудования\n";
    echo "  5. 📝 Прочее\n";
    echo "  6. ⬅️ Назад\n\n";
    
    echo "4. Проверка на соответствие:\n";
    echo "==========================\n";
    
    // Check main menu
    $expectedMainButtons = [
        '📅 Работы по графику',
        '📄 Формирование ИД', 
        '🛠️ Дополнительные работы',
        '✅ Подтвердить достижение вехи',
        '⬅️ Назад'
    ];
    
    $actualMainButtons = [];
    foreach ($electricalKeyboard as $row) {
        foreach ($row as $button) {
            $actualMainButtons[] = $button['text'];
        }
    }
    
    foreach ($expectedMainButtons as $expected) {
        if (in_array($expected, $actualMainButtons)) {
            echo "  ✅ Найдена кнопка: $expected\n";
        } else {
            echo "  ❌ Отсутствует кнопка: $expected\n";
        }
    }
    
    echo "\n";
    
    // Check additional works submenu
    $expectedAdditionalButtons = [
        '🔀 Изменение схем подключения',
        '🔌 Монтаж кабельных линий',
        '📊 Расключение кабельных линий',
        '⚙️ Монтаж оборудования',
        '📝 Прочее',
        '⬅️ Назад'
    ];
    
    $actualAdditionalButtons = [];
    foreach ($additionalWorksKeyboard as $row) {
        foreach ($row as $button) {
            $actualAdditionalButtons[] = $button['text'];
        }
    }
    
    foreach ($expectedAdditionalButtons as $expected) {
        if (in_array($expected, $actualAdditionalButtons)) {
            echo "  ✅ Найдена доп. кнопка: $expected\n";
        } else {
            echo "  ❌ Отсутствует доп. кнопка: $expected\n";
        }
    }
    
    echo "\n5. ЗАКЛЮЧЕНИЕ:\n";
    echo "=============\n";
    
    $mainMenuOK = count(array_diff($expectedMainButtons, $actualMainButtons)) === 0;
    $additionalMenuOK = count(array_diff($expectedAdditionalButtons, $actualAdditionalButtons)) === 0;
    
    if ($mainMenuOK && $additionalMenuOK) {
        echo "✅ СТРУКТУРА МЕНЮ КОРРЕКТНА!\n";
        echo "Все необходимые кнопки присутствуют в обоих меню.\n";
    } else {
        echo "❌ ТРЕБУЕТСЯ ИСПРАВЛЕНИЕ СТРУКТУРЫ МЕНЮ!\n";
        if (!$mainMenuOK) {
            echo "- Проблемы в основном меню электрооборудования\n";
        }
        if (!$additionalMenuOK) {
            echo "- Проблемы в подменю дополнительных работ\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
}
?>