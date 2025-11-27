<?php

/**
 * Проверка логики ProjectHandler без создания объектов
 */

echo "=== ПРОВЕРКА ЛОГИКИ PROJECTHANDLER ===\n\n";

$projectHandlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
$content = file_get_contents($projectHandlerFile);

echo "1. Анализ метода canHandle...\n";

// Ищем метод canHandle
if (preg_match('/public function canHandle\(string \$callbackData\): bool\s*\{(.*?)\}/s', $content, $matches)) {
    $canHandleMethod = $matches[1];
    echo "✓ Метод canHandle найден\n";
    
    // Проверяем логику обработки
    if (strpos($canHandleMethod, 'category_Проекты') !== false) {
        echo "✓ Обработка 'category_Проекты' найдена\n";
    } else {
        echo "✗ Обработка 'category_Проекты' НЕ найдена\n";
    }
    
    if (strpos($canHandleMethod, 'str_starts_with') !== false) {
        echo "✓ Использует str_starts_with для проверки\n";
    } else {
        echo "✗ НЕ использует str_starts_with\n";
    }
    
    if (strpos($canHandleMethod, 'return') !== false) {
        echo "✓ Возвращает результат\n";
    } else {
        echo "✗ НЕ возвращает результат\n";
    }
} else {
    echo "✗ Метод canHandle НЕ найден\n";
}

echo "\n2. Анализ метода handle...\n";

if (preg_match('/public function handle\(.*?\): ServerResponse\s*\{/s', $content, $matches)) {
    echo "✓ Метод handle найден\n";
    
    // Проверяем обработку категории 'Проекты'
    if (strpos($content, 'category === \'Проекты\'') !== false || 
        strpos($content, '$category === "Проекты"') !== false) {
        echo "✓ Обработка категории 'Проекты' найдена\n";
    } else {
        echo "✗ Обработка категории 'Проекты' НЕ найдена\n";
    }
    
    // Проверяем вызов showProjectsMainMenu
    if (strpos($content, 'showProjectsMainMenu') !== false) {
        echo "✓ Вызов showProjectsMainMenu найден\n";
    } else {
        echo "✗ Вызов showProjectsMainMenu НЕ найден\n";
    }
} else {
    echo "✗ Метод handle НЕ найден\n";
}

echo "\n3. Анализ метода showProjectsMainMenu...\n";

if (strpos($content, 'private function showProjectsMainMenu') !== false) {
    echo "✓ Метод showProjectsMainMenu найден\n";
    
    // Проверяем создание клавиатуры
    if (strpos($content, 'Мои проекты') !== false) {
        echo "✓ Кнопка 'Мои проекты' найдена\n";
    } else {
        echo "✗ Кнопка 'Мои проекты' НЕ найдена\n";
    }
    
    if (strpos($content, 'Моя активность') !== false) {
        echo "✓ Кнопка 'Моя активность' найдена\n";
    } else {
        echo "✗ Кнопка 'Моя активность' НЕ найдена\n";
    }
} else {
    echo "✗ Метод showProjectsMainMenu НЕ найден\n";
}

echo "\n4. Проверка регистрации в CallbackqueryCommand...\n";

$callbackFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
if (file_exists($callbackFile)) {
    $callbackContent = file_get_contents($callbackFile);
    
    // Проверяем импорт
    if (strpos($callbackContent, 'use Longman\\TelegramBot\\Commands\\SystemCommands\\Handlers\\ProjectHandler;') !== false) {
        echo "✓ ProjectHandler импортирован\n";
    } else {
        echo "✗ ProjectHandler НЕ импортирован\n";
    }
    
    // Проверяем регистрацию
    if (preg_match('/registerHandler\(\s*new ProjectHandler\(/s', $callbackContent)) {
        echo "✓ ProjectHandler зарегистрирован\n";
    } else {
        echo "✗ ProjectHandler НЕ зарегистрирован\n";
    }
    
    // Проверяем порядок регистрации
    $lines = explode("\n", $callbackContent);
    $adminPos = -1;
    $projectPos = -1;
    
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'new AdminHandler(') !== false) {
            $adminPos = $lineNum;
        }
        if (strpos($line, 'new ProjectHandler(') !== false) {
            $projectPos = $lineNum;
        }
    }
    
    if ($adminPos > 0 && $projectPos > 0) {
        if ($projectPos > $adminPos) {
            echo "✓ ProjectHandler регистрируется после AdminHandler\n";
        } else {
            echo "⚠️ ProjectHandler регистрируется перед AdminHandler\n";
        }
    }
}

echo "\n5. Проверка возможных проблем...\n";

// Проверяем PHP версию для str_starts_with
if (function_exists('str_starts_with')) {
    echo "✓ Функция str_starts_with доступна (PHP 8.0+)\n";
} else {
    echo "⚠️ Функция str_starts_with НЕ доступна (PHP < 8.0) - возможная проблема!\n";
    echo "Рекомендация: заменить str_starts_with на strpos\n";
}

echo "\n6. Возможные решения проблемы...\n";
echo "Если кнопка 'Проекты' не работает, попробуйте:\n";
echo "1. Перезапустить веб-сервер\n";
echo "2. Очистить кэш браузера\n";
echo "3. Проверить логи бота на ошибки\n";
echo "4. Убедиться, что webhook настроен правильно\n\n";

echo "=== АНАЛИЗ ЗАВЕРШЁН ===\n";

?>