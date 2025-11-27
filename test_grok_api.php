<?php
/**
 * Test Grok API Connection
 * Простой тест для проверки что API ключ работает
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/GrokService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\GrokService;

echo "🤖 Тестирование Grok API...\n\n";

// Создаем сервисы
$databaseService = new DatabaseService();
$grokService = new GrokService($databaseService);

// Простой тестовый вопрос
echo "📝 Задаю вопрос Grok AI: 'Привет! Как дела?'\n\n";

$result = $grokService->answerProjectQuestion('Привет! Как дела?', '');

if ($result && $result['success']) {
    echo "✅ УСПЕХ! Grok ответил:\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo $result['answer'] . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    if (isset($result['usage'])) {
        echo "📊 Использовано токенов: \n";
        echo "   Input: " . ($result['usage']['prompt_tokens'] ?? 'N/A') . "\n";
        echo "   Output: " . ($result['usage']['completion_tokens'] ?? 'N/A') . "\n";
        echo "   Total: " . ($result['usage']['total_tokens'] ?? 'N/A') . "\n\n";
    }
    
    echo "🎉 API ключ работает! Можешь использовать AI-помощник в боте!\n";
} else {
    echo "❌ ОШИБКА: " . ($result['message'] ?? 'Не удалось получить ответ') . "\n\n";
    echo "Проверь:\n";
    echo "1. API ключ правильный\n";
    echo "2. Есть интернет соединение\n";
    echo "3. Подписка Grok активна\n";
}

echo "\n✅ Тест завершен!\n";
?>
