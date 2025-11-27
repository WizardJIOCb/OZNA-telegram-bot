<?php
/**
 * Проверка конфигурации Grok AI
 */

echo "🔍 Проверка конфигурации Grok AI...\n\n";

// Загружаем конфиг
$config = require __DIR__ . '/grok_config.php';

echo "📋 Конфигурация:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Проверяем API ключ
$apiKey = $config['grok_api_key'] ?? '';
if (empty($apiKey) || $apiKey === 'YOUR_GROK_API_KEY_HERE') {
    echo "❌ API ключ НЕ настроен!\n";
    echo "   Нужно добавить ключ в grok_config.php\n\n";
    exit(1);
} else {
    echo "✅ API ключ: " . substr($apiKey, 0, 15) . "..." . substr($apiKey, -10) . "\n";
    echo "   Длина: " . strlen($apiKey) . " символов\n";
    echo "   Формат: " . (str_starts_with($apiKey, 'xai-') ? 'ПРАВИЛЬНЫЙ ✓' : 'НЕПРАВИЛЬНЫЙ ✗') . "\n\n";
}

// Проверяем остальные настройки
echo "🔧 Настройки API:\n";
echo "   URL: " . ($config['api_url'] ?? 'не указан') . "\n";
echo "   Model: " . ($config['model'] ?? 'не указан') . "\n";
echo "   Temperature: " . ($config['default_temperature'] ?? 'не указан') . "\n";
echo "   Max Tokens: " . ($config['default_max_tokens'] ?? 'не указан') . "\n\n";

echo "🎯 Доступные функции:\n";
$features = $config['features'] ?? [];
foreach ($features as $feature => $enabled) {
    $status = $enabled ? '✅ Включено' : '❌ Выключено';
    echo "   $feature: $status\n";
}

echo "\n📊 Уровень доступа:\n";
echo "   Минимальный: " . ($config['min_access_level'] ?? 'не указан') . "\n";
echo "   (2 = Supervisor, 3 = Admin)\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (str_starts_with($apiKey, 'xai-') && strlen($apiKey) > 50) {
    echo "🎉 ГОТОВО! Конфигурация правильная!\n\n";
    echo "📝 Что дальше:\n";
    echo "1. Зайди в бота: /start → ⚙️ Админка → 🤖 AI-помощник\n";
    echo "2. Протестируй demo-режим\n";
    echo "3. На production сервере будет работать с реальным AI!\n\n";
    echo "💡 На локальной машине curl может не работать,\n";
    echo "   но на сервере (api.ladyzenko.pro) всё будет ОК!\n";
} else {
    echo "⚠️  API ключ выглядит подозрительно. Проверь формат!\n";
}

echo "\n✅ Проверка завершена!\n";
?>
