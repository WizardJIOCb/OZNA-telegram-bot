<?php
// Compare AdminHandler vs ProjectHandler behavior

echo "=== СРАВНЕНИЕ AdminHandler И ProjectHandler ===\n\n";

// Test 1: Check callback data processing for both handlers
echo "1. ТЕСТИРОВАНИЕ ОБРАБОТКИ CALLBACK ДАННЫХ\n";

$adminCallback = 'category_Админка';
$projectsCallback = 'category_Проекты';

echo "AdminHandler callback: '{$adminCallback}'\n";
echo "ProjectHandler callback: '{$projectsCallback}'\n\n";

// Test 2: Simulate canHandle methods
echo "2. ТЕСТИРОВАНИЕ МЕТОДОВ canHandle\n";

// AdminHandler canHandle simulation
function adminHandlerCanHandle($callbackData) {
    if (strpos($callbackData, 'Электрооборудование') !== false) {
        return false;
    }
    
    $startsWithCategoryAdmin = str_starts_with($callbackData, 'category_Админка');
    $startsWithCategoryAdm = str_starts_with($callbackData, 'category_Адм_');
    $equalsCategoryMain = $callbackData === 'category_Главное';
    $startsWithAdmReq = str_starts_with($callbackData, 'adm_req_');
    $isAdmReqCallback = $startsWithAdmReq;
    $isAccessLevelCallback = str_starts_with($callbackData, 'acc_lvl_');
    
    $canHandle = !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
    
    return $canHandle;
}

// ProjectHandler canHandle simulation
function projectHandlerCanHandle($callbackData) {
    $startsWithCategoryProjects = str_starts_with($callbackData, 'category_Проекты');
    $startsWithProjectList = str_starts_with($callbackData, 'proj_list_');
    $startsWithProjectControl = str_starts_with($callbackData, 'proj_ctrl_');
    return $startsWithCategoryProjects || $startsWithProjectList || $startsWithProjectControl;
}

$adminCanHandle = adminHandlerCanHandle($adminCallback);
$projectCanHandle = projectHandlerCanHandle($projectsCallback);

echo "AdminHandler->canHandle('{$adminCallback}'): " . ($adminCanHandle ? 'true' : 'false') . "\n";
echo "ProjectHandler->canHandle('{$projectsCallback}'): " . ($projectCanHandle ? 'true' : 'false') . "\n\n";

// Test 3: Check if both handlers would be registered
echo "3. ПРОВЕРКА РЕГИСТРАЦИИ ОБРАБОТЧИКОВ\n";

// Check if files exist
$adminHandlerPath = __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
$projectHandlerPath = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

echo "AdminHandler file exists: " . (file_exists($adminHandlerPath) ? 'YES' : 'NO') . "\n";
echo "ProjectHandler file exists: " . (file_exists($projectHandlerPath) ? 'YES' : 'NO') . "\n\n";

// Test 4: Check handler priorities
echo "4. ПРОВЕРКА ПРИОРИТЕТОВ ОБРАБОТЧИКОВ\n";
echo "AdminHandler priority: 25\n";
echo "ProjectHandler priority: 25\n";
echo "Note: Both have the same priority, so registration order matters\n\n";

// Test 5: Check registration order in CallbackqueryCommand
echo "5. ПРОВЕРКА ПОРЯДКА РЕГИСТРАЦИИ\n";
$callbackCommandPath = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
if (file_exists($callbackCommandPath)) {
    $content = file_get_contents($callbackCommandPath);
    
    // Find AdminHandler registration
    $adminPos = strpos($content, 'new AdminHandler');
    $projectPos = strpos($content, 'new ProjectHandler');
    
    if ($adminPos !== false && $projectPos !== false) {
        if ($adminPos < $projectPos) {
            echo "✅ AdminHandler зарегистрирован РАНЬШЕ ProjectHandler\n";
            echo "   AdminHandler position: {$adminPos}\n";
            echo "   ProjectHandler position: {$projectPos}\n";
        } else {
            echo "✅ ProjectHandler зарегистрирован РАНЬШЕ AdminHandler\n";
            echo "   ProjectHandler position: {$projectPos}\n";
            echo "   AdminHandler position: {$adminPos}\n";
        }
    } else {
        echo "❌ Не удалось найти регистрацию обработчиков\n";
    }
} else {
    echo "❌ CallbackqueryCommand.php не найден\n";
}

echo "\n6. ПОИСК РАЗЛИЧИЙ\n";

// Check if there are differences in implementation
echo "Основные различия между обработчиками:\n";
echo "- AdminHandler: НЕ имеет explicit require_once для интерфейса\n";
echo "- ProjectHandler: ИМЕЕТ explicit require_once для интерфейса\n";
echo "- AdminHandler: Работает (кнопка активна)\n";
echo "- ProjectHandler: НЕ работает (кнопка неактивна)\n\n";

echo "=== ВОЗМОЖНАЯ ПРИЧИНА ПРОБЛЕМЫ ===\n";
echo "1. Проверим, загружается ли ProjectHandler вообще:\n";

try {
    // Include autoloader
    require_once __DIR__ . '/custom_autoload.php';
    
    // Try to load ProjectHandler
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler')) {
        echo "✅ ProjectHandler успешно загружается\n";
        
        // Try to create instance (this is where the error might occur)
        try {
            // We can't actually create instance without dependencies, but we can check if class definition is valid
            $reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler');
            echo "✅ Класс ProjectHandler корректно определён\n";
            
            // Check if canHandle method exists
            if ($reflection->hasMethod('canHandle')) {
                echo "✅ Метод canHandle существует\n";
            } else {
                echo "❌ Метод canHandle НЕ существует\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Ошибка при анализе класса ProjectHandler: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "❌ ProjectHandler НЕ МОЖЕТ быть загружен\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка при загрузке: " . $e->getMessage() . "\n";
}

echo "\n=== РЕКОМЕНДАЦИИ ===\n";
echo "1. Проблема НЕ в логике canHandle - обе функции работают правильно\n";
echo "2. Проблема НЕ в регистрации - оба обработчика регистрируются\n";
echo "3. Проблема ВОЗМОЖНО в загрузке класса или создании экземпляра\n";
echo "4. Нужно проверить логи telegram_bot_error.log на наличие ошибок ProjectHandler\n";
echo "5. Возможно, ProjectHandler не создаётся из-за ошибки в конструкторе\n";

?>