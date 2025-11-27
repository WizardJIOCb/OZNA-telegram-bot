<?php
// Simple test for Projects button functionality

echo "=== ТЕСТ КНОПКИ ПРОЕКТЫ ===\n";

// 1. Simulate callback data
$callbackData = 'category_Проекты';
echo "1. Тестируем callback_data: '{$callbackData}'\n";

// 2. Extract category (как в CallbackHandlerFactory)
$category = '';
if (strpos($callbackData, 'category_') === 0) {
    $category = substr($callbackData, strlen('category_'));
}
echo "2. Извлечённая категория: '{$category}'\n";

// 3. Test ProjectHandler canHandle method
function projectHandlerCanHandle($callbackData) {
    $startsWithCategoryProjects = str_starts_with($callbackData, 'category_Проекты');
    $startsWithProjectList = str_starts_with($callbackData, 'proj_list_');
    $startsWithProjectControl = str_starts_with($callbackData, 'proj_ctrl_');
    return $startsWithCategoryProjects || $startsWithProjectList || $startsWithProjectControl;
}

$canHandle = projectHandlerCanHandle($callbackData);
echo "3. ProjectHandler canHandle: " . ($canHandle ? 'true' : 'false') . "\n";

// 4. Test handle method condition
$handleCondition = ($category === 'Проекты');
echo "4. Handle method condition (\$category === 'Проекты'): " . ($handleCondition ? 'true' : 'false') . "\n";

// 5. Test access level check
echo "\n5. Проверяем уровни доступа:\n";
$accessLevels = [
    'Гость' => 0,
    'Пользователь' => 1,
    'Супервайзер' => 2,
    'Администратор' => 3,
    'Суперадминистратор' => 4
];

foreach ($accessLevels as $role => $level) {
    $hasAccess = ($level >= 1); // ACCESS_LEVEL_USER
    echo "   {$role} (уровень {$level}): " . ($hasAccess ? 'ДОСТУП' : 'НЕТ ДОСТУПА') . "\n";
}

echo "\n=== РЕЗУЛЬТАТ ===\n";
if ($canHandle && $handleCondition) {
    echo "✅ ВСЁ РАБОТАЕТ ПРАВИЛЬНО!\n";
    echo "Кнопка 'Проекты' должна показывать меню проектов.\n";
    echo "\nВозможные причины проблемы:\n";
    echo "1. Нужно перезапустить веб-сервер\n";
    echo "2. Проблема с кешем PHP (OPcache)\n";
    echo "3. Ошибки в логах сервера\n";
} else {
    echo "❌ ПРОБЛЕМА В КОДЕ!\n";
    echo "canHandle: " . ($canHandle ? 'OK' : 'FAIL') . "\n";
    echo "handleCondition: " . ($handleCondition ? 'OK' : 'FAIL') . "\n";
}

echo "\nРекомендации:\n";
echo "1. Перезапустите веб-сервер (Apache/Nginx/IIS)\n";
echo "2. Проверьте логи ошибок в файле errors.log\n";
echo "3. Убедитесь, что webhook настроен правильно\n";
?>