<?php
require __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;

// Bot credentials
$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

// Initialize bot
$telegram = new Telegram($bot_api_key, $bot_username);

// Test message to you (the user who requested this)
$testMessage = "🧪 <b>ТЕСТ РАССЫЛКИ</b>\n\nПроверяем работу бота перед массовой отправкой объявления.\n\nЕсли вы видите это сообщение - бот работает корректно! ✅";

echo "🧪 ТЕСТИРОВАНИЕ ОТПРАВКИ...\n\n";

try {
    $result = Request::sendMessage([
        'chat_id' => 230379668,  // Your user ID
        'text' => $testMessage,
        'parse_mode' => 'HTML'
    ]);
    
    if ($result->isOk()) {
        echo "✅ ТЕСТ УСПЕШЕН! Сообщение отправлено!\n";
        echo "🚀 Готов к массовой рассылке объявления.\n\n";
        
        echo "Хотите продолжить с массовой рассылкой? (это займет несколько минут)\n";
        echo "Нажмите Enter для продолжения или Ctrl+C для отмены: ";
        
        $handle = fopen("php://stdin", "r");
        $input = fgets($handle);
        fclose($handle);
        
        echo "\n🚀 НАЧИНАЕМ МАССОВУЮ РАССЫЛКУ!\n\n";
        
    } else {
        echo "❌ ТЕСТ НЕ УДАЛСЯ!\n";
        echo "Ошибка: " . $result->getDescription() . "\n";
        echo "Код ошибки: " . $result->getErrorCode() . "\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ ИСКЛЮЧЕНИЕ В ТЕСТЕ: " . $e->getMessage() . "\n";
    exit(1);
}

// If test passed, continue with mass sending
$message = "🎉 <b>НОВОЕ ОБНОВЛЕНИЕ БОТА!</b> 🎉

📈 <b>Представляем новый раздел \"Моя активность\"</b>

🆕 <b>Что нового:</b>
• 📈 <b>Персональная статистика</b> - видите всю свою активность одним взглядом
• 🏆 <b>Система достижений</b> - получайте награды за успехи (как в играх!)
• 📅 <b>Полные отчёты</b> - проверяйте правильность записей в табеле
• 🎯 <b>Анализ эффективности</b> - отслеживайте свой прогресс

🎮 <b>Достижения, которые можно получить:</b>
• 🥇 \"Мастер табеля\" (за 50+ записей)
• 🎆 \"Покоритель вех\" (за подтвержденные вехи)
• 🌍 \"Мастер многозадачности\" (за работу с разными проектами)
• 🔥 \"Неугасимое пламя\" (за постоянную активность)

🚀 <b>Скоро будет доступно:</b>
• 🏅 Эмодзи-значки рядом с именем
• 🏆 Соревнования между сотрудниками
• 📊 Детальная аналитика с графиками
• 🎁 Награды за достижения

📱 <b>Как попробовать:</b>
1️⃣ Нажмите кнопку \"📁 Проекты\"
2️⃣ Выберите \"📊 Моя активность\"
3️⃣ Изучайте свою статистику и достижения!

💡 <b>Зачем это нужно:</b>
• Видите свой прогресс и успехи
• Мотивация работать ещё лучше
• Возможность сравнивать результаты
• Подготовка к системе рангов и наград

🎯 <b>Попробуйте прямо сейчас!</b>

---
<i>Обновление установлено автоматически, перезапуск не требуется</i>";

try {
    $conn = new PDO('mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4', 'WizardJIOCb', '7qQ#28051961');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get only active users (with individual chats, not groups)
    $stmt = $conn->prepare("SELECT DISTINCT user_id FROM user_chat WHERE user_id > 0 AND chat_id = user_id");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Найдено пользователей для рассылки: " . count($users) . "\n\n";
    
    $sent = 0;
    $errors = 0;
    $skipped = 0;
    
    foreach ($users as $user) {
        $chatId = $user['user_id'];
        
        // Skip your own ID since you already got the test message
        if ($chatId == 230379668) {
            $skipped++;
            echo "⏭️ Пропущен: ID {$chatId} (это вы - уже получили тестовое сообщение)\n";
            continue;
        }
        
        try {
            $result = Request::sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
            
            if ($result->isOk()) {
                $sent++;
                echo "✅ Отправлено: ID {$chatId}\n";
            } else {
                $errors++;
                echo "❌ Ошибка: ID {$chatId} - " . $result->getDescription() . "\n";
            }
        } catch (Exception $e) {
            $errors++;
            echo "❌ Исключение для ID {$chatId}: " . $e->getMessage() . "\n";
        }
        
        // Longer delay to avoid rate limiting
        usleep(200000); // 0.2 second
    }
    
    echo "\n🎉 РАССЫЛКА ЗАВЕРШЕНА!\n";
    echo "✅ Успешно отправлено: {$sent}\n";
    echo "❌ Ошибок: {$errors}\n"; 
    echo "⏭️ Пропущено: {$skipped}\n";
    echo "📊 Всего обработано: " . count($users) . "\n\n";
    echo "🎯 Российские пользователи уведомлены о новом функционале \"Моя активность\"!\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
?>