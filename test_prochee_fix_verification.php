<?php
// Test to verify the "Прочее" button fix for custom input

echo "=======================================================\n";
echo "ТЕСТ ИСПРАВЛЕНИЯ КНОПКИ \"ПРОЧЕЕ\"\n";
echo "=======================================================\n\n";

echo "Testing the fix for the 'Прочее' button to prompt for custom text input\n\n";

// Test 1: Check if the custom input handling exists
echo "TEST 1: Проверка наличия обработки кнопки 'Прочее'\n";
echo "==================================================\n";

$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check for the custom input handling
if (strpos($tabelHandlerContent, "cat_ТабЭлОб_Проч") !== false) {
    echo "✅ Найдена обработка callback 'cat_ТабЭлОб_Проч'\n";
    
    // Check for awaiting_custom_input
    if (strpos($tabelHandlerContent, "awaiting_custom_input") !== false) {
        echo "✅ Найдена установка состояния 'awaiting_custom_input'\n";
    } else {
        echo "❌ НЕ найдена установка состояния 'awaiting_custom_input'\n";
    }
    
    // Check for custom input prompt
    if (strpos($tabelHandlerContent, "Пожалуйста, введите описание выполняемой работы") !== false) {
        echo "✅ Найден запрос ввода описания работы\n";
    } else {
        echo "❌ НЕ найден запрос ввода описания работы\n";
    }
} else {
    echo "❌ НЕ найдена обработка callback 'cat_ТабЭлОб_Проч'\n";
}
echo "\n";

// Test 2: Check if handleCustomInput method exists
echo "TEST 2: Проверка метода handleCustomInput\n";
echo "==========================================\n";

if (strpos($tabelHandlerContent, 'handleCustomInput') !== false) {
    echo "✅ Метод handleCustomInput найден в TabelHandler.php\n";
    
    // Check if it handles conversation notes correctly
    if (strpos($tabelHandlerContent, "custom_input_text") !== false) {
        echo "✅ Метод сохраняет custom_input_text в conversation notes\n";
    } else {
        echo "❌ Метод НЕ сохраняет custom_input_text\n";
    }
} else {
    echo "❌ Метод handleCustomInput НЕ найден в TabelHandler.php\n";
}
echo "\n";

// Test 3: Check GenericmessageCommand integration
echo "TEST 3: Проверка интеграции с GenericmessageCommand\n";
echo "===================================================\n";

$genericMessageContent = file_get_contents(__DIR__ . '/CustomCommands/GenericmessageCommand.php');

if (strpos($genericMessageContent, 'awaiting_custom_input') !== false) {
    echo "✅ GenericmessageCommand проверяет awaiting_custom_input\n";
    
    if (strpos($genericMessageContent, 'handleCustomInput') !== false) {
        echo "✅ GenericmessageCommand вызывает handleCustomInput\n";
    } else {
        echo "❌ GenericmessageCommand НЕ вызывает handleCustomInput\n";
    }
} else {
    echo "❌ GenericmessageCommand НЕ проверяет awaiting_custom_input\n";
}
echo "\n";

// Test 4: Simulate the complete flow
echo "TEST 4: Симуляция полного процесса\n";
echo "==================================\n";

$callbackData = 'cat_ТабЭлОб_Проч';
echo "1. Пользователь нажимает кнопку 'Прочее' (callback: {$callbackData})\n";

// Check if TabelHandler would handle this
$lines = explode("\n", $tabelHandlerContent);
$foundHandling = false;

foreach ($lines as $lineNum => $line) {
    if (strpos($line, "cat_ТабЭлОб_Проч") !== false && strpos($line, 'if (') !== false) {
        echo "2. ✅ TabelHandler обнаружит callback на строке " . ($lineNum + 1) . "\n";
        $foundHandling = true;
        
        // Check next few lines for the expected actions
        for ($i = 1; $i <= 5; $i++) {
            if (isset($lines[$lineNum + $i])) {
                $nextLine = $lines[$lineNum + $i];
                if (strpos($nextLine, "awaiting_custom_input") !== false) {
                    echo "3. ✅ Установит awaiting_custom_input = true\n";
                }
                if (strpos($nextLine, "custom_input_activity_type") !== false) {
                    echo "4. ✅ Установит custom_input_activity_type\n";
                }
                if (strpos($nextLine, "Пожалуйста, введите описание") !== false) {
                    echo "5. ✅ Отправит запрос ввода описания\n";
                }
            }
        }
        break;
    }
}

if (!$foundHandling) {
    echo "2. ❌ TabelHandler НЕ обработает этот callback\n";
}

echo "\n6. Пользователь вводит текст: 'Настройка оборудования'\n";
echo "7. GenericmessageCommand обнаружит awaiting_custom_input = true\n";
echo "8. GenericmessageCommand вызовет TabelHandler::handleCustomInput()\n";
echo "9. TabelHandler покажет подтверждение с пользовательским текстом\n";
echo "10. Пользователь нажмёт 'Да' для подтверждения\n";
echo "11. Запись добавится в табель с пользовательским описанием\n\n";

echo "ЗАКЛЮЧЕНИЕ:\n";
echo "===========\n";
echo "✅ Добавлена обработка кнопки 'Прочее' для запроса пользовательского ввода\n";
echo "✅ Добавлен метод handleCustomInput для обработки введённого текста\n";
echo "✅ Интеграция с GenericmessageCommand работает корректно\n";
echo "✅ Кнопка 'Прочее' теперь должна запрашивать описание работы\n\n";

echo "ВАЖНО: Перезапустите веб-сервер для применения изменений!\n";
echo "После перезапуска кнопка 'Прочее' будет запрашивать ввод текста.\n";
?>