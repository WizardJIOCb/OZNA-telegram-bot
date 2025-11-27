<?php

/**
 * Упрощённый тест для проверки работы кнопки "Проекты"
 */

echo "=== УПРОЩЁННЫЙ ТЕСТ КНОПКИ ПРОЕКТЫ ===\n\n";

echo "1. Проверяем файлы системы...\n";

// Проверяем наличие ProjectHandler
$projectHandlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
if (file_exists($projectHandlerFile)) {
    echo "✓ ProjectHandler.php существует\n";
    
    // Проверяем содержимое
    $content = file_get_contents($projectHandlerFile);
    
    if (strpos($content, 'canHandle') !== false) {
        echo "✓ Метод canHandle найден в ProjectHandler\n";
    } else {
        echo "✗ Метод canHandle НЕ найден в ProjectHandler\n";
    }
    
    if (strpos($content, 'category_Проекты') !== false) {
        echo "✓ Обработка 'category_Проекты' найдена в ProjectHandler\n";
    } else {
        echo "✗ Обработка 'category_Проекты' НЕ найдена в ProjectHandler\n";
    }
    
    if (strpos($content, 'showProjectsMainMenu') !== false) {
        echo "✓ Метод showProjectsMainMenu найден в ProjectHandler\n";
    } else {
        echo "✗ Метод showProjectsMainMenu НЕ найден в ProjectHandler\n";
    }
} else {
    echo "✗ ProjectHandler.php НЕ существует!\n";
}

echo "\n2. Проверяем регистрацию в CallbackqueryCommand...\n";

$callbackFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
if (file_exists($callbackFile)) {
    $content = file_get_contents($callbackFile);
    
    if (strpos($content, 'use Longman\\TelegramBot\\Commands\\SystemCommands\\Handlers\\ProjectHandler;') !== false) {
        echo "✓ ProjectHandler импортирован в CallbackqueryCommand\n";
    } else {
        echo "✗ ProjectHandler НЕ импортирован в CallbackqueryCommand\n";
    }
    
    if (strpos($content, 'new ProjectHandler(') !== false) {
        echo "✓ ProjectHandler зарегистрирован в CallbackqueryCommand\n";
    } else {
        echo "✗ ProjectHandler НЕ зарегистрирован в CallbackqueryCommand\n";
    }
} else {
    echo "✗ CallbackqueryCommand.php НЕ существует!\n";
}

echo "\n3. Проверяем MessageService...\n";

$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $content = file_get_contents($messageServiceFile);
    
    if (strpos($content, 'Проекты') !== false) {
        echo "✓ Кнопка 'Проекты' найдена в MessageService\n";
    } else {
        echo "✗ Кнопка 'Проекты' НЕ найдена в MessageService\n";
    }
    
    // Проверяем callback_data для проектов
    if (strpos($content, 'category_Проекты') !== false) {
        echo "✓ Callback 'category_Проекты' найден в MessageService\n";
    } else {
        echo "✗ Callback 'category_Проекты' НЕ найден в MessageService\n";
    }
} else {
    echo "✗ MessageService.php НЕ существует!\n";
}

echo "\n4. Проверяем TextmenuCommand...\n";

$textmenuFile = __DIR__ . '/CustomCommands/TextmenuCommand.php';
if (file_exists($textmenuFile)) {
    $content = file_get_contents($textmenuFile);
    
    if (strpos($content, 'handleProjectsMenu') !== false) {
        echo "✓ Метод handleProjectsMenu найден в TextmenuCommand\n";
    } else {
        echo "✗ Метод handleProjectsMenu НЕ найден в TextmenuCommand\n";
    }
    
    if (strpos($content, 'Проекты') !== false) {
        echo "✓ Обработка кнопки 'Проекты' найдена в TextmenuCommand\n";
    } else {
        echo "✗ Обработка кнопки 'Проекты' НЕ найдена в TextmenuCommand\n";
    }
} else {
    echo "✗ TextmenuCommand.php НЕ существует!\n";
}

echo "\n5. Проверяем конфликты с другими обработчиками...\n";

$handlersDir = __DIR__ . '/CustomCommands/Handlers/';
if (is_dir($handlersDir)) {
    $handlers = scandir($handlersDir);
    $conflictFound = false;
    
    foreach ($handlers as $handler) {
        if (strpos($handler, '.php') !== false && $handler !== 'ProjectHandler.php') {
            $handlerFile = $handlersDir . $handler;
            $content = file_get_contents($handlerFile);
            
            if (strpos($content, 'category_Проекты') !== false) {
                echo "⚠️ КОНФЛИКТ: Обработчик {$handler} также содержит 'category_Проекты'\n";
                $conflictFound = true;
            }
        }
    }
    
    if (!$conflictFound) {
        echo "✓ Конфликтов с другими обработчиками не найдено\n";
    }
} else {
    echo "✗ Папка Handlers не найдена!\n";
}

echo "\n6. Анализ возможных проблем...\n";

// Проверяем порядок регистрации обработчиков
if (file_exists($callbackFile)) {
    $content = file_get_contents($callbackFile);
    
    // Ищем порядок регистрации
    $adminPos = strpos($content, 'new AdminHandler(');
    $projectPos = strpos($content, 'new ProjectHandler(');
    
    if ($adminPos !== false && $projectPos !== false) {
        if ($projectPos > $adminPos) {
            echo "✓ ProjectHandler регистрируется после AdminHandler (правильный порядок)\n";
        } else {
            echo "⚠️ ProjectHandler регистрируется перед AdminHandler (может быть проблема приоритета)\n";
        }
    }
}

echo "\n=== РЕКОМЕНДАЦИИ ===\n";
echo "Если кнопка 'Проекты' появляется, но не работает, возможные причины:\n";
echo "1. ProjectHandler не зарегистрирован в CallbackqueryCommand\n";
echo "2. Конфликт с другими обработчиками\n";
echo "3. Неправильный callback_data в кнопке\n";
echo "4. Ошибка в методе canHandle() в ProjectHandler\n";
echo "5. Проблемы с базой данных или зависимостями\n\n";

echo "=== АНАЛИЗ ЗАВЕРШЁН ===\n";

?>