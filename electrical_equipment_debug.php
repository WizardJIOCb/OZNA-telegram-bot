<?php
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С КНОПКОЙ ЭЛЕКТРООБОРУДОВАНИЕ ===\n\n";

try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    echo "1. Проверка создания клавиатуры пусконаладочных работ:\n";
    echo "====================================================\n";
    
    $commissioningWorksKeyboard = $messageService->createCommissioningWorksKeyboard();
    echo "Создана клавиатура с " . count($commissioningWorksKeyboard) . " кнопками\n\n";
    
    $electricalFound = false;
    foreach ($commissioningWorksKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "Кнопка: '{$button['text']}' → Callback: '{$button['callback_data']}'\n";
            
            if (strpos($button['text'], 'Электрооборудование') !== false) {
                $electricalFound = true;
                echo "  ⚡ НАЙДЕНА кнопка электрооборудования!\n";
                echo "  Callback данные: {$button['callback_data']}\n";
                echo "  Длина callback: " . strlen($button['callback_data']) . " байт\n";
            }
        }
    }
    
    if (!$electricalFound) {
        echo "❌ ОШИБКА: Кнопка электрооборудования НЕ НАЙДЕНА!\n";
    } else {
        echo "\n2. Проверка клавиатуры электрооборудования:\n";
        echo "==========================================\n";
        
        $electricalKeyboard = $messageService->createElectricalEquipmentKeyboard();
        echo "Создана клавиатура электрооборудования с " . count($electricalKeyboard) . " кнопками\n\n";
        
        foreach ($electricalKeyboard as $index => $row) {
            foreach ($row as $button) {
                echo "  - {$button['text']} (callback: {$button['callback_data']})\n";
            }
        }
    }
    
    echo "\n3. Анализ проблемы:\n";
    echo "==================\n";
    
    echo "Ожидаемый поток:\n";
    echo "1. Пользователь нажимает 'Электрооборудование' → callback: 'cat_ТабЭлОб'\n";
    echo "2. TabelHandler должен распознать этот callback\n";
    echo "3. TabelHandler вызывает handleElectricalEquipmentMenu()\n";
    echo "4. Показывается меню с активностями электрооборудования\n\n";
    
    echo "Возможные причины проблемы:\n";
    echo "- TabelHandler не распознает callback 'cat_ТабЭлОб'\n";
    echo "- Маршрутизация в TabelHandler работает неправильно\n";
    echo "- handleElectricalEquipmentMenu() не вызывается\n";
    echo "- createElectricalEquipmentKeyboard() возвращает неправильные данные\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
}

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>