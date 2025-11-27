<!DOCTYPE html>
<html>
<head>
    <title>Тест системы уровней доступа</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Тест системы уровней доступа бота OznaWork</h1>
    <?php
    // Include necessary files with error handling
    $filesToInclude = [
        __DIR__ . '/Custome/DB.php',
        __DIR__ . '/CustomCommands/Services/DatabaseService.php',
        __DIR__ . '/CustomCommands/Services/AdminService.php',
        __DIR__ . '/CustomCommands/Services/MessageService.php'
    ];

    echo "<h2>Подключение файлов:</h2>";
    echo "<ul>";
    foreach ($filesToInclude as $file) {
        if (file_exists($file)) {
            require_once $file;
            echo "<li>Подключен файл: " . basename($file) . " - <span style='color: green;'>УСПЕХ</span></li>";
        } else {
            echo "<li>Файл не найден: " . $file . " - <span style='color: red;'>ОШИБКА</span></li>";
        }
    }
    echo "</ul>";

    echo "<h2>Тестирование функциональности:</h2>";
    
    try {
        // Initialize services
        $messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
        echo "<p>MessageService инициализирован - <span style='color: green;'>УСПЕХ</span></p>";
        
        $adminService = new Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
        echo "<p>AdminService инициализирован - <span style='color: green;'>УСПЕХ</span></p>";
        
        // Test user ID (you can change this to test with different users)
        $testUserId = 230379668; // This should be a super admin
        
        echo "<h3>Тестирование для пользователя ID: {$testUserId}</h3>";
        
        // Test 1: Check current access level
        echo "<h4>1. Проверка текущего уровня доступа:</h4>";
        $currentLevel = $adminService->getUserAccessLevel($testUserId);
        $levelName = $adminService->getAccessLevelName($currentLevel);
        echo "<p>Текущий уровень: {$levelName} ({$currentLevel})</p>";
        
        // Test 2: Check if user is super admin
        echo "<h4>2. Проверка статуса суперадмина:</h4>";
        $isSuperAdmin = $adminService->isSuperAdmin($testUserId);
        echo "<p>Суперадмин: " . ($isSuperAdmin ? "Да" : "Нет") . "</p>";
        
        // Test 3: Test access level checking
        echo "<h4>3. Проверка уровней доступа:</h4>";
        echo "<ul>";
        for ($i = 0; $i <= 3; $i++) {
            $hasAccess = $adminService->hasAccessLevel($testUserId, $i);
            $levelName = $adminService->getAccessLevelName($i);
            echo "<li>Доступ к уровню {$i} ({$levelName}): " . ($hasAccess ? "Есть" : "Нет") . "</li>";
        }
        echo "</ul>";
        
        // Test 4: Display all access level names
        echo "<h4>4. Все уровни доступа:</h4>";
        echo "<ul>";
        echo "<li>" . Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_GUEST . " - " . $adminService->getAccessLevelName(Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_GUEST) . "</li>";
        echo "<li>" . Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_USER . " - " . $adminService->getAccessLevelName(Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_USER) . "</li>";
        echo "<li>" . Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_ADMIN . " - " . $adminService->getAccessLevelName(Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_ADMIN) . "</li>";
        echo "<li>" . Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_SUPER_ADMIN . " - " . $adminService->getAccessLevelName(Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) . "</li>";
        echo "</ul>";
        
        echo "<h2 style='color: green;'>Тестирование завершено успешно!</h2>";
        
    } catch (Exception $e) {
        echo "<h2 style='color: red;'>Ошибка при тестировании:</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    ?>
</body>
</html>