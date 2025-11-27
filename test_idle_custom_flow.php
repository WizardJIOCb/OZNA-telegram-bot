<?php
/**
 * Test the custom idle comment flow step by step
 */

require_once 'vendor/autoload.php';
require_once 'custom_autoload.php';

use Longman\TelegramBot\Conversation;

echo "=== TESTING CUSTOM IDLE COMMENT FLOW ===\n\n";

echo "1. Testing conversation state after button click:\n";
echo "   Expected state after clicking 'Свой комментарий':\n";
echo "   - awaiting_custom_input = true\n";
echo "   - custom_input_activity_type = 'Простой_Комментарий'\n";
echo "   - idle_custom_category = 'category_Табель_Простой_6_kom'\n\n";

echo "2. Testing GenericmessageCommand detection:\n";
echo "   When user enters text, GenericmessageCommand should:\n";
echo "   - Detect awaiting_custom_input = true\n";
echo "   - See custom_input_activity_type = 'Простой_Комментарий'\n";
echo "   - Call TabelHandler->handleCustomInput()\n\n";

echo "3. Testing TabelHandler->handleCustomInput():\n";
echo "   TabelHandler should:\n";
echo "   - Detect activityType = 'Простой_Комментарий'\n";
echo "   - Enter special idle comment handling block\n";
echo "   - Store custom_idle_text = user input\n";
echo "   - Call handleIdleFinalSelection() with mock CallbackQuery\n\n";

echo "4. Testing handleIdleFinalSelection():\n";
echo "   Should:\n";
echo "   - Call getCommissioningIdleReason() with conversation parameter\n";
echo "   - Retrieve custom_idle_text from conversation\n";
echo "   - Format as 'Прочее (user's custom text)'\n";
echo "   - Show confirmation with custom text\n\n";

echo "5. Potential issues to check:\n";
echo "   a) Is GenericmessageCommand detecting the custom input state?\n";
echo "   b) Is TabelHandler->handleCustomInput() being called?\n";
echo "   c) Is the special idle comment block being executed?\n";
echo "   d) Is the mock CallbackQuery properly formed?\n";
echo "   e) Is the conversation object being passed correctly?\n\n";

echo "6. Debug logging to look for:\n";
echo "   - 'GenericmessageCommand: IDLE COMMENT DETECTED'\n";
echo "   - 'TabelHandler: handleCustomInput - stored custom idle text'\n";
echo "   - 'TabelHandler: handleCustomInput - calling handleIdleFinalSelection'\n";
echo "   - 'TabelHandler: COMMISSIONING IDLE: handleIdleFinalSelection called'\n";
echo "   - 'TabelHandler: commissioning built custom comment format'\n\n";

echo "=== TEST COMPLETED ===\n";
?>