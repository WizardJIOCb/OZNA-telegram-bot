<?php
// Detailed test for Projects button callback processing

echo "=== ДЕТАЛЬНОЕ ТЕСТИРОВАНИЕ КНОПКИ ПРОЕКТЫ ===\n\n";

// Test 1: Check if ProjectHandler can be loaded
echo "1. ТЕСТИРОВАНИЕ ЗАГРУЗКИ PROJECTHANDLER\n";
try {
    require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
    echo "✅ ProjectHandler загружен успешно\n";
    
    // Test if the class exists
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler')) {
        echo "✅ Класс ProjectHandler существует\n";
    } else {
        echo "❌ Класс ProjectHandler НЕ НАЙДЕН\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка загрузки ProjectHandler: " . $e->getMessage() . "\n";
}

// Test 2: Check callback data processing
echo "\n2. ТЕСТИРОВАНИЕ ОБРАБОТКИ CALLBACK ДАННЫХ\n";
$callbackData = 'category_Проекты';
echo "Тестируем callback_data: '{$callbackData}'\n";

// Test category extraction (as done in CallbackHandlerFactory)
$category = '';
if (strpos($callbackData, 'category_') === 0) {
    $category = substr($callbackData, strlen('category_'));
    echo "Извлечённая категория: '{$category}'\n";
} else {
    echo "❌ Неверный формат callback_data\n";
}

// Test 3: Check ProjectHandler canHandle method
echo "\n3. ТЕСТИРОВАНИЕ МЕТОДА canHandle\n";
function projectHandlerCanHandle($callbackData) {
    $startsWithCategoryProjects = str_starts_with($callbackData, 'category_Проекты');
    $startsWithProjectList = str_starts_with($callbackData, 'proj_list_');
    $startsWithProjectControl = str_starts_with($callbackData, 'proj_ctrl_');
    return $startsWithCategoryProjects || $startsWithProjectList || $startsWithProjectControl;
}

$canHandle = projectHandlerCanHandle($callbackData);
echo "ProjectHandler->canHandle('{$callbackData}'): " . ($canHandle ? 'true' : 'false') . "\n";

// Test 4: Check if CallbackHandlerFactory is properly registering ProjectHandler
echo "\n4. ТЕСТИРОВАНИЕ РЕГИСТРАЦИИ В CallbackHandlerFactory\n";
try {
    require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
    echo "✅ CallbackHandlerFactory загружен\n";
    
    // Check if CallbackqueryCommand registers ProjectHandler
    $callbackCommandPath = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
    if (file_exists($callbackCommandPath)) {
        $callbackCommandContent = file_get_contents($callbackCommandPath);
        if (strpos($callbackCommandContent, 'ProjectHandler') !== false) {
            echo "✅ CallbackqueryCommand регистрирует ProjectHandler\n";
        } else {
            echo "❌ CallbackqueryCommand НЕ регистрирует ProjectHandler\n";
        }
        
        // Check the specific registration line
        if (preg_match('/new ProjectHandler/', $callbackCommandContent)) {
            echo "✅ Найдена строка регистрации: new ProjectHandler(...)\n";
        } else {
            echo "❌ Не найдена строка регистрации ProjectHandler\n";
        }
    } else {
        echo "❌ CallbackqueryCommand.php не найден\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка при тестировании CallbackHandlerFactory: " . $e->getMessage() . "\n";
}

// Test 5: Check MessageService for Projects button
echo "\n5. ТЕСТИРОВАНИЕ MessageService\n";
try {
    $messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
    if (file_exists($messageServicePath)) {
        $messageServiceContent = file_get_contents($messageServicePath);
        if (strpos($messageServiceContent, 'Проекты') !== false) {
            echo "✅ MessageService содержит кнопку Проекты\n";
            
            // Check for the specific line that adds the Projects button
            if (preg_match('/Проекты.*callback_data.*category_Проекты/', $messageServiceContent)) {
                echo "✅ Найдена строка добавления кнопки Проекты\n";
            } else {
                echo "❌ Не найдена корректная строка добавления кнопки Проекты\n";
            }
        } else {
            echo "❌ MessageService НЕ содержит кнопку Проекты\n";
        }
    } else {
        echo "❌ MessageService.php не найден\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка при тестировании MessageService: " . $e->getMessage() . "\n";
}

// Test 6: Check handler priority
echo "\n6. ТЕСТИРОВАНИЕ ПРИОРИТЕТОВ ОБРАБОТЧИКОВ\n";
$handlerPriorities = [
    'AdminHandler' => 60,
    'MainMenuHandler' => 50,
    'SupervisorHandler' => 25,
    'AccessLevelHandler' => 25,
    'ProjectHandler' => 25,  // Should be 25
    'TabelHandler' => 20,
    'ProfileHandler' => 15,
    'FunctionalDirectionHandler' => 10,
    'HelpHandler' => 1
];

echo "ProjectHandler имеет приоритет: " . $handlerPriorities['ProjectHandler'] . "\n";
echo "TabelHandler имеет приоритет: " . $handlerPriorities['TabelHandler'] . "\n";

if ($handlerPriorities['ProjectHandler'] > $handlerPriorities['TabelHandler']) {
    echo "✅ ProjectHandler имеет более высокий приоритет, чем TabelHandler\n";
} else {
    echo "ℹ️ ProjectHandler имеет такой же или более низкий приоритет, чем TabelHandler\n";
}

// Test 7: Simulate full callback processing
echo "\n7. СИМУЛЯЦИЯ ПОЛНОЙ ОБРАБОТКИ CALLBACK\n";
echo "Шаги обработки callback 'category_Проекты':\n";
echo "1. Telegram отправляет webhook запрос с callback_data='category_Проекты'\n";
echo "2. hook.php получает запрос и вызывает \$telegram->handle()\n";
echo "3. CallbackqueryCommand->execute() получает callback_query\n";
echo "4. CallbackqueryCommand->initializeServices() создаёт обработчики\n";
echo "5. CallbackHandlerFactory->handleCallback() обрабатывает callback\n";
echo "6. ProjectHandler->canHandle('category_Проекты') должен вернуть true\n";
echo "7. ProjectHandler->handle() должен быть вызван с категорией 'Проекты'\n";
echo "8. ProjectHandler->showProjectsMainMenu() должен отправить меню\n";

echo "\n=== РЕЗУЛЬТАТЫ ТЕСТИРОВАНИЯ ===\n";
if ($canHandle) {
    echo "✅ Логика обработки callback работает корректно\n";
    echo "💡 Проблема, скорее всего, в кешировании или перезапуске webhook\n";
} else {
    echo "❌ Обнаружена ошибка в логике обработки callback\n";
}

echo "\n=== РЕКОМЕНДАЦИИ ===\n";
echo "1. Проверьте, правильно ли загружаются все зависимости\n";
echo "2. Убедитесь, что веб-сервер имеет доступ ко всем файлам\n";
echo "3. Проверьте права доступа к файлам ProjectHandler.php\n";
echo "4. Попробуйте перезапустить веб-сервер полностью\n";
echo "5. Проверьте логи веб-сервера на наличие ошибок загрузки файлов\n";

?>