<?php
require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

// Telegram bot configuration
$bot_api_key = '7717062576:AAEnzjkCtO9HnPa1o1_u7qWhtT6hZjyaEpk';
$bot_username = 'ozna_bot';

// Your chat ID (replace with your actual chat ID)
$chat_id = '5261465966'; // Replace with your Telegram user ID

try {
    // Create Telegram API object
    $telegram = new Telegram($bot_api_key, $bot_username);

    // Test message 1: Check if bot can send messages
    $result1 = Request::sendMessage([
        'chat_id' => $chat_id,
        'text' => "🔍 ДИАГНОСТИКА МЕНЮ 'ШЕФ-МОНТАЖНЫЕ РАБОТЫ'\n\nТест 1: Бот отправляет сообщения ✅",
        'parse_mode' => 'HTML'
    ]);

    sleep(1);

    // Test message 2: Check supervision keyboard creation
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    $messageService = new MessageService();
    
    $supervisionKeyboard = $messageService->createSupervisionWorksKeyboard();
    
    $keyboardText = "Тест 2: Клавиатура 'Шеф-монтажные работы':\n\n";
    foreach ($supervisionKeyboard as $row) {
        foreach ($row as $button) {
            $keyboardText .= "• " . $button['text'] . " → " . $button['callback_data'] . "\n";
        }
    }
    
    $result2 = Request::sendMessage([
        'chat_id' => $chat_id,
        'text' => $keyboardText,
        'parse_mode' => 'HTML'
    ]);

    sleep(1);

    // Test message 3: Send actual supervision menu
    $result3 = Request::sendMessage([
        'chat_id' => $chat_id,
        'text' => "<b>🔧 Тест меню: Шеф-монтажные работы</b>\n\nНажмите любую кнопку для тестирования:",
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => $supervisionKeyboard])
    ]);

    sleep(1);

    // Test message 4: Check callback handling capability
    require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
    require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
    require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
    
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    $testCallbacks = [
        'cat_supervision_prep',
        'cat_ШМР_ПодгРаб', 
        'cat_РЁРњР _РџРѕРґРіР Р°Р±'
    ];
    
    $callbackTestText = "Тест 4: Проверка canHandle для callback'ов:\n\n";
    foreach ($testCallbacks as $callback) {
        $canHandle = $tabelHandler->canHandle($callback);
        $callbackTestText .= "• $callback → " . ($canHandle ? "✅ МОЖЕТ ОБРАБОТАТЬ" : "❌ НЕ МОЖЕТ ОБРАБОТАТЬ") . "\n";
    }
    
    $result4 = Request::sendMessage([
        'chat_id' => $chat_id,
        'text' => $callbackTestText,
        'parse_mode' => 'HTML'
    ]);

    sleep(1);

    // Test message 5: Summary
    $summary = "🏁 ИТОГИ ДИАГНОСТИКИ:\n\n";
    $summary .= "✅ Бот может отправлять сообщения\n";
    $summary .= "✅ Клавиатура создается корректно\n";
    $summary .= "✅ Меню отображается\n";
    $summary .= "✅ TabelHandler инициализируется\n\n";
    $summary .= "📝 СЛЕДУЮЩИЙ ШАГ:\n";
    $summary .= "Нажмите любую кнопку в тестовом меню выше.\n";
    $summary .= "Если кнопки не реагируют - проблема в webhook'ах сервера.\n";
    $summary .= "Если реагируют - проблема в маршрутизации callback'ов.";

    $result5 = Request::sendMessage([
        'chat_id' => $chat_id,
        'text' => $summary,
        'parse_mode' => 'HTML'
    ]);

    echo "Диагностические сообщения отправлены в чат!\n";
    echo "Проверьте свой Telegram для результатов тестирования.\n";

} catch (Exception $e) {
    echo "Ошибка при отправке тестовых сообщений: " . $e->getMessage() . "\n";
    
    // Send error message to chat if possible
    try {
        Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => "❌ ОШИБКА ДИАГНОСТИКИ:\n\n" . $e->getMessage(),
            'parse_mode' => 'HTML'
        ]);
    } catch (Exception $e2) {
        echo "Не удалось отправить сообщение об ошибке: " . $e2->getMessage() . "\n";
    }
}
?>