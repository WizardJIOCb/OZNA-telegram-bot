<?php
// Runtime debug script to trace the actual flow of the "Работы по графику" button

// This script should be run on the server to trace the actual callback processing

// Add extensive logging to trace the flow
error_log("=== Runtime Debug Started ===");

// Simulate the callback data that would be received
$callbackData = 'cat_ТабЭлОб_РабПоГр';
error_log("Received callbackData: '{$callbackData}'");

// Include the necessary files
require_once 'vendor/autoload.php';

// Add our own logging to trace the flow
class DebugTabelHandler extends Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler {
    public function canHandle(string $callbackData): bool {
        error_log("DebugTabelHandler: canHandle called with callbackData: '{$callbackData}'");
        $result = parent::canHandle($callbackData);
        error_log("DebugTabelHandler: canHandle result: " . ($result ? 'true' : 'false'));
        return $result;
    }
    
    public function handle($callbackQuery, string $category, $conversation): \Longman\TelegramBot\Entities\ServerResponse {
        error_log("DebugTabelHandler: handle called with category: '{$category}'");
        return parent::handle($callbackQuery, $category, $conversation);
    }
}

// Log the end of the debug
error_log("=== Runtime Debug Ended ===");
?>