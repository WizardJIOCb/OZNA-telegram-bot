<?php
// Trace script to add detailed logging to TabelHandler for commissioning flow

echo "=== Adding Detailed Logging to TabelHandler ===\n\n";

// Read the current TabelHandler file
$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$content = file_get_contents($tabelHandlerPath);

if ($content === false) {
    die("Error: Could not read TabelHandler.php\n");
}

echo "1. Read TabelHandler.php successfully\n";

// Add detailed logging to handleCommissioningDateSelection method
$pattern = '/private function handleCommissioningDateSelection\(string \$chatId\): ServerResponse\s*{/';
$replacement = "private function handleCommissioningDateSelection(string \$chatId): ServerResponse\n    {\n        error_log('TabelHandler: handleCommissioningDateSelection called for chatId: ' . \$chatId);\n        // Используем уникальный префикс для избежания конфликта с Пусконаладочные работы\n        \$prefix = 'category_Табель_Пусконаладка';\n        error_log('TabelHandler: handleCommissioningDateSelection using prefix: ' . \$prefix);\n        \$keyboard = \$this->messageService->createDateSelectionKeyboard(\$prefix);\n        error_log('TabelHandler: handleCommissioningDateSelection created keyboard: ' . json_encode(\$keyboard, JSON_UNESCAPED_UNICODE));\n        \$message = '⚙️ <b>Выберите дату для Пусконаладки</b>';\n        error_log('TabelHandler: handleCommissioningDateSelection sending message: ' . \$message);\n        \$result = \$this->messageService->sendMessage(\$chatId, \$message, \$keyboard);\n        error_log('TabelHandler: handleCommissioningDateSelection sendMessage result: ' . (is_object(\$result) && method_exists(\$result, 'isOk') ? (\$result->isOk() ? 'success' : 'failed') : 'unknown'));\n        if (is_object(\$result) && method_exists(\$result, 'isOk') && !\$result->isOk()) {\n            error_log('TabelHandler: handleCommissioningDateSelection sendMessage error: ' . \$result->getDescription());\n        }";

if (strpos($content, 'handleCommissioningDateSelection') !== false) {
    $content = preg_replace($pattern, $replacement, $content);
    echo "2. Added detailed logging to handleCommissioningDateSelection method\n";
} else {
    echo "2. Warning: handleCommissioningDateSelection method not found\n";
}

// Add detailed logging to handleOtherWorkTypes method for the Пусконаладка condition
$pattern = '/if \(\$category === \'Табель_Пусконаладка\'\) {\s*error_log\("TabelHandler: handleOtherWorkTypes: handling commissioning date selection"\);\s*return \$this->handleCommissioningDateSelection\(\$chatId\);\s*}/';
$replacement = "if (\$category === 'Табель_Пусконаладка') {\n            error_log('TabelHandler: handleOtherWorkTypes: handling commissioning date selection for category: ' . \$category);\n            return \$this->handleCommissioningDateSelection(\$chatId);\n        }";

$content = preg_replace($pattern, $replacement, $content);
echo "3. Enhanced logging in handleOtherWorkTypes for Пусконаладка condition\n";

// Add detailed logging to the canHandle method
$pattern = '/public function canHandle\(string \$callbackData\): bool\s*{/';
$replacement = "public function canHandle(string \$callbackData): bool\n    {\n        // Add detailed logging\n        error_log('TabelHandler: canHandle called with callbackData: ' . \$callbackData);";

$content = preg_replace($pattern, $replacement, $content, 1);
echo "4. Added detailed logging to canHandle method\n";

// Add detailed logging to the handle method
$pattern = '/public function handle\(CallbackQuery \$callbackQuery, string \$category, Conversation \$conversation\): ServerResponse\s*{/';
$replacement = "public function handle(CallbackQuery \$callbackQuery, string \$category, Conversation \$conversation): ServerResponse\n    {\n        \$chatId = \$callbackQuery->getMessage()->getChat()->getId();\n        \$userId = \$callbackQuery->getFrom()->getId();\n        \$userFirstName = \$callbackQuery->getFrom()->getFirstName();\n        \$callbackData = \$callbackQuery->getData();\n        \n        // Add detailed logging\n        error_log('TabelHandler: handle called with category: ' . \$category . ' and callbackData: ' . \$callbackData . ' for chatId: ' . \$chatId);";

$content = preg_replace($pattern, $replacement, $content, 1);
echo "5. Added detailed logging to handle method\n";

// Write the modified content back to the file
if (file_put_contents($tabelHandlerPath, $content) !== false) {
    echo "6. Successfully updated TabelHandler.php with detailed logging\n";
} else {
    echo "6. Error: Could not write to TabelHandler.php\n";
}

echo "\n=== Logging Added Successfully ===\n";
echo "Now when you click the 'Пусконаладка' button, detailed logs will be written to the error log.\n";
echo "Check the error log to see exactly what's happening in the execution flow.\n";
?>