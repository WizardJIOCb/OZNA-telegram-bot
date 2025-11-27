<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Проверка AccessLevelHandler</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1e1e1e; color: #fff; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #4fc1ff; }
        .box { background: #2d2d2d; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #4fc1ff; }
        .success { border-left-color: #28a745; }
        .error { border-left-color: #dc3545; }
        .warning { border-left-color: #ffc107; }
        pre { background: #000; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .button { 
            background: #007bff; color: white; padding: 10px 20px; border: none; 
            border-radius: 5px; cursor: pointer; margin: 5px; text-decoration: none; display: inline-block;
        }
        .button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Проверка AccessLevelHandler</h1>
        
        <div style="text-align: center; margin: 20px 0;">
            <a href="debug_user_management_button.php" class="button">⬅️ Назад к диагностике</a>
            <button class="button" onclick="location.reload();">🔄 Обновить</button>
        </div>

        <?php
        echo "<div class='box'>";
        echo "<h3>1. Проверка существования файлов</h3>";
        
        $files = [
            'AccessLevelHandler' => __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php',
            'CallbackHandlerFactory' => __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php',
            'CallbackqueryCommand' => __DIR__ . '/CustomCommands/CallbackqueryCommand.php',
        ];
        
        foreach ($files as $name => $path) {
            if (file_exists($path)) {
                echo "<p>✅ {$name}: найден</p>";
            } else {
                echo "<p class='error'>❌ {$name}: НЕ НАЙДЕН ({$path})</p>";
            }
        }
        echo "</div>";

        echo "<div class='box'>";
        echo "<h3>2. Тест canHandle метода AccessLevelHandler</h3>";
        echo "<pre>";
        
        // Test callback data patterns
        $testCallbacks = [
            'acc_lvl_manage_users',
            'acc_lvl_users', 
            'acc_lvl_user_details_123',
            'acc_lvl_set_level_123_2',
            'category_УровеньДоступа_Управление',
            'category_other',
            'random_callback'
        ];
        
        foreach ($testCallbacks as $callback) {
            echo "\nТест callback: '{$callback}'\n";
            
            // Simulate AccessLevelHandler canHandle logic
            $isAccessLevelManagement = (
                $callback === 'acc_lvl_users' ||
                $callback === 'acc_lvl_manage_users' ||
                strpos($callback, 'acc_lvl_user_details_') === 0 ||
                strpos($callback, 'acc_lvl_set_level_') === 0
            );
            
            $isCategoryAccessLevel = strpos($callback, 'category_УровеньДоступа') === 0;
            
            $canHandle = $isCategoryAccessLevel || $isAccessLevelManagement;
            
            echo "  isAccessLevelManagement: " . ($isAccessLevelManagement ? 'true' : 'false') . "\n";
            echo "  isCategoryAccessLevel: " . ($isCategoryAccessLevel ? 'true' : 'false') . "\n";
            echo "  canHandle: " . ($canHandle ? 'TRUE' : 'FALSE') . "\n";
            
            if ($callback === 'acc_lvl_manage_users') {
                echo "  >>> ЭТО НАШ CALLBACK! Должен быть TRUE <<<\n";
            }
        }
        
        echo "</pre>";
        echo "</div>";

        echo "<div class='box'>";
        echo "<h3>3. Проверка CallbackqueryCommand фильтра</h3>";
        echo "<pre>";
        
        $testCallback = 'acc_lvl_manage_users';
        echo "Тест callback: '{$testCallback}'\n\n";
        
        // Simulate early exit filter
        $passesFilter = (
            strpos($testCallback, 'category_') === 0 || 
            strpos($testCallback, 'adm_req_') === 0 || 
            strpos($testCallback, 'acc_lvl_') === 0 || 
            strpos($testCallback, 'cat_Таб') === 0 ||
            strpos($testCallback, 'cat_ШМР_') === 0 ||
            strpos($testCallback, 'proj_ctrl_') === 0 ||
            strpos($testCallback, 'proj_list_') === 0 ||
            strpos($testCallback, 'cat_Гарантии') === 0
        );
        
        echo "Проверка условий early exit фильтра:\n";
        echo "  category_: " . (strpos($testCallback, 'category_') === 0 ? 'true' : 'false') . "\n";
        echo "  adm_req_: " . (strpos($testCallback, 'adm_req_') === 0 ? 'true' : 'false') . "\n";
        echo "  acc_lvl_: " . (strpos($testCallback, 'acc_lvl_') === 0 ? 'true' : 'false') . " <<<< ДОЛЖНО БЫТЬ TRUE\n";
        echo "  cat_Таб: " . (strpos($testCallback, 'cat_Таб') === 0 ? 'true' : 'false') . "\n";
        echo "  proj_ctrl_: " . (strpos($testCallback, 'proj_ctrl_') === 0 ? 'true' : 'false') . "\n";
        echo "\nПроходит фильтр: " . ($passesFilter ? 'TRUE' : 'FALSE') . "\n";
        
        if ($passesFilter) {
            echo "\n✅ Callback должен пройти через фильтр и обработаться!";
        } else {
            echo "\n❌ Callback будет заблокирован фильтром!";
        }
        
        echo "</pre>";
        echo "</div>";

        echo "<div class='box'>";
        echo "<h3>4. Тест extractCategory в CallbackHandlerFactory</h3>";
        echo "<pre>";
        
        $testCallback = 'acc_lvl_manage_users';
        echo "Тест callback: '{$testCallback}'\n\n";
        
        // Simulate extractCategory logic
        if (strpos($testCallback, 'acc_lvl_') === 0) {
            $category = $testCallback; // Pass the full callback data to AccessLevelHandler
            echo "Обнаружен acc_lvl_ префикс\n";
            echo "Извлеченная категория: '{$category}'\n";
            echo "✅ Категория будет передана AccessLevelHandler как есть";
        } elseif (strpos($testCallback, 'category_') === 0) {
            $category = substr($testCallback, strlen('category_'));
            echo "Обнаружен category_ префикс\n";
            echo "Извлеченная категория: '{$category}'\n";
        } else {
            echo "❌ Не найден подходящий префикс";
        }
        
        echo "</pre>";
        echo "</div>";

        echo "<div class='box'>";
        echo "<h3>5. Проверка регистрации обработчиков</h3>";
        echo "<pre>";
        
        // Check if handlers are registered in CallbackqueryCommand
        $callbackCommandFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
        if (file_exists($callbackCommandFile)) {
            $content = file_get_contents($callbackCommandFile);
            
            if (strpos($content, 'AccessLevelHandler') !== false) {
                echo "✅ AccessLevelHandler импортирован в CallbackqueryCommand\n";
            } else {
                echo "❌ AccessLevelHandler НЕ импортирован в CallbackqueryCommand\n";
            }
            
            if (strpos($content, 'new AccessLevelHandler') !== false) {
                echo "✅ AccessLevelHandler регистрируется в фабрике\n";
            } else {
                echo "❌ AccessLevelHandler НЕ регистрируется в фабрике\n";
            }
            
            if (strpos($content, 'registerHandler') !== false) {
                echo "✅ Обработчики регистрируются через registerHandler\n";
            } else {
                echo "❌ Нет вызовов registerHandler\n";
            }
            
        } else {
            echo "❌ Не найден файл CallbackqueryCommand.php\n";
        }
        
        echo "</pre>";
        echo "</div>";

        echo "<div class='box'>";
        echo "<h3>6. Рекомендации</h3>";
        echo "<p>Основываясь на тестах выше:</p>";
        echo "<ul>";
        echo "<li>Если все тесты проходят успешно, то проблема скорее всего в правах доступа пользователя</li>";
        echo "<li>Если какие-то тесты не проходят, это указывает на конкретную проблему в коде</li>";
        echo "<li>Используйте страницу диагностики для проверки поступления callback'ов на сервер</li>";
        echo "</ul>";
        echo "</div>";
        ?>
    </div>
</body>
</html>