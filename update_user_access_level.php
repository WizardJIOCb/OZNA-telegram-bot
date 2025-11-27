<?php

/**
 * Скрипт для обновления уровня доступа пользователя через PDO
 * Поскольку на локальной машине нет расширения MySQLi, используем PDO как альтернативу
 */

echo "Обновление уровня доступа пользователя\n";
echo "====================================\n\n";

// Проверим, доступно ли расширение PDO
if (!extension_loaded('pdo_mysql')) {
    echo "Расширение PDO MySQL не доступно на этой системе.\n";
    echo "Пожалуйста, выполните следующий SQL-запрос вручную через phpMyAdmin или другой MySQL клиент:\n\n";
    
    $userId = 5261465966;
    $userFio = "Богданов Борис Сергеевич";
    
    echo "UPDATE ozna_data \n";
    echo "SET data = JSON_SET(\n";
    echo "    COALESCE(data, '{}'), \n";
    echo "    '$.fio', '{$userFio}',\n";
    echo "    '$.access_level', 4,\n";
    echo "    '$.is_super_admin', true\n";
    echo ") \n";
    echo "WHERE user_id = {$userId};\n\n";
    
    echo "Если запись не существует, выполните:\n\n";
    echo "INSERT INTO ozna_data (user_id, data) \n";
    echo "VALUES ({$userId}, '{\"fio\": \"{$userFio}\", \"access_level\": 4, \"is_super_admin\": true, \"functional_direction\": \"Не указано\"}');\n\n";
    
    exit;
}

try {
    // Данные подключения к базе данных
    $host = '82.146.42.213';
    $dbname = 'tg_bot';
    $username = 'WizardJIOCb';
    $password = '7qQ#28051961';
    
    // Данные пользователя
    $userId = 5261465966;
    $userFio = "Богданов Борис Сергеевич";
    
    echo "Подключение к базе данных...\n";
    
    // Создаем подключение через PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "Подключение успешно установлено.\n\n";
    
    // Проверяем, существует ли запись пользователя
    echo "Проверка существования записи пользователя...\n";
    $stmt = $pdo->prepare("SELECT user_id, data FROM ozna_data WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "Запись пользователя найдена. Обновление данных...\n";
        
        // Обновляем данные пользователя
        $stmt = $pdo->prepare("
            UPDATE ozna_data 
            SET data = JSON_SET(
                COALESCE(data, '{}'), 
                '$.fio', ?,
                '$.access_level', 4,
                '$.is_super_admin', true
            ) 
            WHERE user_id = ?
        ");
        
        $stmt->execute([$userFio, $userId]);
        
        echo "Данные пользователя успешно обновлены.\n\n";
    } else {
        echo "Запись пользователя не найдена. Создание новой записи...\n";
        
        // Создаем новую запись пользователя
        $stmt = $pdo->prepare("
            INSERT INTO ozna_data (user_id, data) 
            VALUES (?, ?)
        ");
        
        $userData = json_encode([
            'fio' => $userFio,
            'access_level' => 4,
            'is_super_admin' => true,
            'functional_direction' => 'Не указано'
        ], JSON_UNESCAPED_UNICODE);
        
        $stmt->execute([$userId, $userData]);
        
        echo "Новая запись пользователя успешно создана.\n\n";
    }
    
    // Проверяем обновленные данные
    echo "Проверка обновленных данных...\n";
    $stmt = $pdo->prepare("SELECT user_id, data FROM ozna_data WHERE user_id = ?");
    $stmt->execute([$userId]);
    $updatedUser = $stmt->fetch();
    
    if ($updatedUser) {
        $userData = json_decode($updatedUser['data'], true);
        echo "Обновленные данные пользователя:\n";
        echo "  User ID: {$updatedUser['user_id']}\n";
        echo "  ФИО: " . ($userData['fio'] ?? 'Не указано') . "\n";
        echo "  Уровень доступа: " . ($userData['access_level'] ?? 'Не указан') . "\n";
        echo "  Суперадмин: " . (isset($userData['is_super_admin']) && $userData['is_super_admin'] ? 'Да' : 'Нет') . "\n\n";
        
        echo "Готово! Ваш уровень доступа должен быть обновлен до суперадмина.\n";
        echo "Пожалуйста, перезапустите бота или отправьте /start, чтобы увидеть изменения.\n";
    } else {
        echo "Не удалось получить обновленные данные пользователя.\n";
    }
    
} catch (PDOException $e) {
    echo "Ошибка подключения к базе данных: " . $e->getMessage() . "\n";
    echo "Пожалуйста, выполните SQL-запрос вручную через phpMyAdmin или другой MySQL клиент:\n\n";
    
    $userId = 5261465966;
    $userFio = "Богданов Борис Сергеевич";
    
    echo "UPDATE ozna_data \n";
    echo "SET data = JSON_SET(\n";
    echo "    COALESCE(data, '{}'), \n";
    echo "    '$.fio', '{$userFio}',\n";
    echo "    '$.access_level', 4,\n";
    echo "    '$.is_super_admin', true\n";
    echo ") \n";
    echo "WHERE user_id = {$userId};\n\n";
    
    echo "Если запись не существует, выполните:\n\n";
    echo "INSERT INTO ozna_data (user_id, data) \n";
    echo "VALUES ({$userId}, '{\"fio\": \"{$userFio}\", \"access_level\": 4, \"is_super_admin\": true, \"functional_direction\": \"Не указано\"}');\n\n";
} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage() . "\n";
}