<?php

/**
 * Скрипт миграции существующих суперадминов из хардкода в базу данных (с использованием PDO)
 */

echo "Миграция существующих суперадминов из хардкода в базу данных (PDO версия)\n";
echo "=====================================================================\n\n";

try {
    // Подключение к базе данных через PDO
    $host = '82.146.42.213';
    $username = 'WizardJIOCb';
    $password = '7qQ#28051961';
    $database = 'tg_bot';
    
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "Подключение к базе данных успешно установлено.\n\n";
    
    // Список суперадминов из старого хардкода
    $oldSuperAdmins = [
        230379668,  // Ваш ID (из StartCommand.php)
        5261465966  // Боря
        // 1090118386  // Близи (удален из суперадминов)
        // 444234398   // Олег olegpysh (удален из суперадминов)
    ];
    
    echo "Список суперадминов для миграции:\n";
    foreach ($oldSuperAdmins as $adminId) {
        echo "  - {$adminId}\n";
    }
    echo "\n";
    
    // Мигрируем каждого суперадмина в базу данных
    foreach ($oldSuperAdmins as $adminId) {
        echo "Назначение пользователя {$adminId} суперадмином... ";
        
        // Проверяем, существует ли запись пользователя
        $stmt = $pdo->prepare("SELECT user_id, data FROM ozna_data WHERE user_id = ?");
        $stmt->execute([$adminId]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Если пользователь существует, обновляем его данные
            $userData = json_decode($user['data'], true) ?: [];
            $userData['is_super_admin'] = true;
            
            $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
            $updateStmt = $pdo->prepare("UPDATE ozna_data SET data = ? WHERE user_id = ?");
            $result = $updateStmt->execute([$jsonData, $adminId]);
            
            echo ($result ? "Успешно" : "Ошибка") . "\n";
        } else {
            // Если пользователя не существует, создаем новую запись
            $userData = [
                'is_super_admin' => true,
                'access_level' => 4, // Суперадмин
                'fio' => '',
                'functional_direction' => 'Не указано'
            ];
            
            $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
            $insertStmt = $pdo->prepare("INSERT INTO ozna_data (user_id, data) VALUES (?, ?)");
            $result = $insertStmt->execute([$adminId, $jsonData]);
            
            echo ($result ? "Успешно (новая запись)" : "Ошибка") . "\n";
        }
    }
    
    echo "\n";
    echo "Проверка миграции:\n";
    
    // Получаем всех суперадминов из базы данных
    $stmt = $pdo->prepare("SELECT user_id, data FROM ozna_data WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.is_super_admin')) = 'true'");
    $stmt->execute();
    $superAdmins = $stmt->fetchAll();
    
    echo "Суперадмины в базе данных:\n";
    foreach ($superAdmins as $admin) {
        echo "  - " . $admin['user_id'] . "\n";
    }
    
    echo "\nМиграция завершена успешно!\n";
    echo "Теперь можно удалить хардкод списка суперадминов из AdminService.php\n";
    
} catch (PDOException $e) {
    echo "Ошибка при подключении к базе данных: " . $e->getMessage() . "\n";
    echo "Код ошибки: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "Ошибка при миграции: " . $e->getMessage() . "\n";
    echo "Код ошибки: " . $e->getCode() . "\n";
}