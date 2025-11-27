<?php
require __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;

// Bot credentials
$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

// Initialize bot
$telegram = new Telegram($bot_api_key, $bot_username);

// MySQL credentials
$mysql_credentials = [
    'host'     => '82.146.42.213',
    'port'     => 3306,
    'user'     => 'WizardJIOCb',
    'password' => '7qQ#28051961',
    'database' => 'tg_bot',
];
$telegram->enableMySql($mysql_credentials);

echo "🚀 ОТПРАВКА РУССКОГО ОБЪЯВЛЕНИЯ!\n\n";

// Russian announcement message - the final version from the confirmed script
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

// Get users directly from database
try {
    // Connect to database directly
    $conn = new PDO("mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4", "WizardJIOCb", "7qQ#28051961");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all users from user_chat table (filtering for individual chats, not groups)
    $stmt = $conn->prepare("SELECT DISTINCT user_id FROM user_chat WHERE user_id > 0 AND chat_id > 0");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Найдено пользователей в базе: " . count($users) . "\n";
    echo "🚀 Начинаю отправку...\n\n";
    
    $sent = 0;
    $errors = 0;
    
    foreach ($users as $user) {
        $chatId = $user['user_id'];
        
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
        
        // Small delay to avoid rate limiting
        usleep(100000); // 0.1 second
    }
    
    echo "\n🎉 ОТПРАВКА ЗАВЕРШЕНА!\n";
    echo "✅ Успешно: {$sent}\n";
    echo "❌ Ошибок: {$errors}\n";
    echo "📊 Всего: " . count($users) . "\n\n";
    echo "🎯 РЕЗУЛЬТАТ: Российские пользователи уведомлены о новом функционале \"Моя активность\"!\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка базы данных: " . $e->getMessage() . "\n";
}
?>