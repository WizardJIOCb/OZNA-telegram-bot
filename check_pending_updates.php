<?php
/**
 * Получить pending updates и обработать их
 */

echo "=== ОБРАБОТКА PENDING UPDATES ===\n\n";

// Load config
$config = require __DIR__ . '/config.php';
$bot_token = $config['api_key'];

// Get pending updates
echo "Получение pending updates...\n\n";
$ch = curl_init("https://api.telegram.org/bot{$bot_token}/getUpdates");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result && $result['ok']) {
    $updates = $result['result'];
    echo "📥 Найдено " . count($updates) . " pending updates\n\n";
    
    if (count($updates) > 0) {
        echo "Последние 5 обновлений:\n";
        echo "────────────────────────────────────────────\n";
        
        foreach (array_slice($updates, -5) as $update) {
            $updateId = $update['update_id'];
            
            if (isset($update['message'])) {
                $msg = $update['message'];
                $from = $msg['from']['first_name'] ?? 'Unknown';
                $text = $msg['text'] ?? '[no text]';
                $date = date('Y-m-d H:i:s', $msg['date']);
                
                echo "Update ID: $updateId\n";
                echo "Type: message\n";
                echo "From: $from\n";
                echo "Text: $text\n";
                echo "Date: $date\n";
                echo "────────────────────────────────────────────\n";
            } elseif (isset($update['callback_query'])) {
                $cb = $update['callback_query'];
                $from = $cb['from']['first_name'] ?? 'Unknown';
                $data = $cb['data'] ?? '[no data]';
                
                echo "Update ID: $updateId\n";
                echo "Type: callback_query\n";
                echo "From: $from\n";
                echo "Data: $data\n";
                echo "────────────────────────────────────────────\n";
            }
        }
        
        // Clear updates by calling getUpdates with offset
        $lastUpdateId = end($updates)['update_id'];
        $offsetUpdateId = $lastUpdateId + 1;
        
        echo "\n🗑️  Очистка updates (offset: $offsetUpdateId)...\n";
        $ch = curl_init("https://api.telegram.org/bot{$bot_token}/getUpdates?offset=$offsetUpdateId");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
        
        echo "✅ Updates очищены\n";
    }
} else {
    echo "❌ Ошибка получения updates\n";
}

echo "\n════════════════════════════════════════════════════════\n";
echo "✅ ГОТОВО!\n";
echo "════════════════════════════════════════════════════════\n";
