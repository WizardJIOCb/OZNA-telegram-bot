<?php
// Простая проверка уровня доступа пользователя
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Проверка уровня доступа пользователя</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; }
        .box { background: white; padding: 20px; border-radius: 5px; margin: 10px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .button { background: #2196f3; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .success { color: #4caf50; }
        .error { color: #f44336; }
        .warning { color: #ff9800; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Проверка уровня доступа пользователя</h1>
        
        <div class="box">
            <h3>Введите User ID для проверки</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="user_id">User ID:</label>
                    <input type="number" id="user_id" name="user_id" required 
                           value="<?= isset($_POST['user_id']) ? htmlspecialchars($_POST['user_id']) : '' ?>">
                </div>
                <button type="submit" class="button">Проверить уровень доступа</button>
            </form>
        </div>

        <?php
        if (isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];
            
            echo "<div class='box'>";
            echo "<h3>Результат проверки для User ID: {$userId}</h3>";
            
            try {
                // Подключение к базе данных
                $host = '82.146.42.213';
                $dbname = 'tg_bot';
                $username = 'wj';
                $password = 'k41nz7C&dBr#';
                
                $mysqli = new mysqli($host, $username, $password, $dbname);
                $mysqli->set_charset('utf8');
                
                if ($mysqli->connect_error) {
                    throw new Exception("Ошибка подключения к БД: " . $mysqli->connect_error);
                }
                
                // Получаем данные пользователя
                $stmt = $mysqli->prepare("SELECT data FROM ozna_data WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $userData = json_decode($row['data'], true);
                    
                    $accessLevel = $userData['access_level'] ?? 0;
                    $fio = $userData['fio'] ?? 'Не указано';
                    $functionalDirection = $userData['functional_direction'] ?? 'Не указано';
                    
                    // Определяем название уровня доступа
                    $levelNames = [
                        0 => 'Гость',
                        1 => 'Пользователь', 
                        2 => 'Администратор',
                        3 => 'Суперадминистратор'
                    ];
                    $levelName = $levelNames[$accessLevel] ?? 'Неизвестный';
                    
                    echo "<p><strong>ФИО:</strong> {$fio}</p>";
                    echo "<p><strong>Функциональное направление:</strong> {$functionalDirection}</p>";
                    echo "<p><strong>Уровень доступа:</strong> {$levelName} (ID: {$accessLevel})</p>";
                    
                    if ($accessLevel >= 2) {
                        echo "<p class='success'>✅ У пользователя ЕСТЬ права для управления пользователями (уровень {$accessLevel})</p>";
                        echo "<p>Кнопка 'Управление пользователями' должна работать для этого пользователя.</p>";
                    } else {
                        echo "<p class='warning'>⚠️ У пользователя НЕТ прав для управления пользователями (уровень {$accessLevel})</p>";
                        echo "<p>Требуется уровень доступа 2 (Администратор) или выше.</p>";
                        echo "<p>Кнопка должна показывать сообщение об ошибке доступа.</p>";
                    }
                    
                    // Дополнительная информация
                    echo "<hr>";
                    echo "<h4>Что происходит при нажатии кнопки:</h4>";
                    echo "<ol>";
                    echo "<li>Callback 'acc_lvl_manage_users' отправляется на сервер</li>";
                    echo "<li>AccessLevelHandler проверяет canHandle() - должен вернуть true</li>";
                    echo "<li>Вызывается handle() с категорией 'acc_lvl_manage_users'</li>";
                    
                    if ($accessLevel >= 2) {
                        echo "<li class='success'>Проверка прав доступа - ПРОЙДЕНА</li>";
                        echo "<li class='success'>Вызывается showUsersWithAccess()</li>";
                        echo "<li class='success'>Отправляется список пользователей с доступом</li>";
                    } else {
                        echo "<li class='warning'>Проверка прав доступа - НЕ ПРОЙДЕНА</li>";
                        echo "<li class='warning'>Отправляется сообщение об ошибке доступа</li>";
                    }
                    echo "</ol>";
                    
                } else {
                    echo "<p class='error'>❌ Пользователь с ID {$userId} не найден в базе данных</p>";
                }
                
                $stmt->close();
                $mysqli->close();
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Ошибка: " . $e->getMessage() . "</p>";
            }
            
            echo "</div>";
        }
        ?>

        <div class="box">
            <h3>📋 Уровни доступа в системе</h3>
            <ul>
                <li><strong>0 - Гость:</strong> Базовый доступ, может просматривать профиль</li>
                <li><strong>1 - Пользователь:</strong> Полный доступ к функциям бота</li>
                <li><strong>2 - Администратор:</strong> Может управлять пользователями (уровни 0-2)</li>
                <li><strong>3 - Суперадминистратор:</strong> Полный доступ ко всем функциям</li>
            </ul>
            
            <p><strong>Для работы кнопки "Управление пользователями" требуется уровень 2 или выше.</strong></p>
        </div>

        <div class="box">
            <h3>🔧 Дополнительные инструменты</h3>
            <p>
                <a href="debug_user_management_button.php" class="button">🔍 Мониторинг callback'ов</a>
                <a href="simple_callback_monitor.php" class="button">📊 Простой мониторинг</a>
            </p>
        </div>
    </div>
</body>
</html>