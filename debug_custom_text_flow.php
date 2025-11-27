<?php
/**
 * Debug script to trace where custom text is getting lost
 */

echo "=== CUSTOM TEXT FLOW DEBUGGING ===\n\n";

echo "ANALYSIS OF STORAGE AND RETRIEVAL FLOW:\n";
echo "=====================================\n\n";

echo "1. Button click 'cat_ТабЭлОб_Проч' sets:\n";
echo "   - awaiting_custom_prochee_input = true\n";
echo "   - prochee_activity_type = 'Прочее_ЭлектрОб'\n\n";

echo "2. User enters text 'Проверка'\n\n";

echo "3. GenericmessageCommand detects NEW system:\n";
echo "   - isAwaitingNewProcheeInput() returns true\n";
echo "   - Calls handleNewProcheeInput()\n";
echo "   - Which calls TabelHandler->handleCustomInput()\n\n";

echo "4. TabelHandler->handleCustomInput() NEW system path:\n";
echo "   - Detects awaiting_custom_prochee_input = true\n";
echo "   - Sets prochee_custom_text = 'Проверка'\n";
echo "   - Sets awaiting_custom_prochee_input = false\n";
echo "   - Shows confirmation with callback 'cat_ТабЭлОб_Проч_yes'\n\n";

echo "5. User clicks 'Да' button:\n";
echo "   - Callback 'cat_ТабЭлОб_Проч_yes' received\n";
echo "   - Routes to handleNewProcheeConfirmation()\n\n";

echo "6. handleNewProcheeConfirmation() should retrieve:\n";
echo "   - customText = conversation->notes['prochee_custom_text']\n";
echo "   - Pass to addEntry(..., customText)\n\n";

echo "7. addEntry() passes customText to createTabelMessage()\n\n";

echo "8. createTabelMessage() should use:\n";
echo "   - if (activityType === 'Прочее_ЭлектрОб' && !empty(idleReason)) {\n";
echo "       activityText .= \" ({idleReason})\"\n";
echo "   }\n\n";

echo "🔍 POTENTIAL ISSUE POINTS:\n";
echo "=========================\n\n";

echo "Point A: Conversation state persistence\n";
echo "- Between text input and confirmation click\n";
echo "- Check if prochee_custom_text is properly saved\n\n";

echo "Point B: handleActivityExecution interference\n";
echo "- Our fix to handleActivityExecution might be interfering\n";
echo "- The callback 'cat_ТабЭлОб_Проч_yes' might also trigger handleActivityExecution\n\n";

echo "Point C: Multiple execution paths\n";
echo "- Both handleNewProcheeConfirmation and handleActivityExecution might execute\n";
echo "- Need to ensure only one path executes\n\n";

echo "🔧 DEBUGGING STEPS:\n";
echo "==================\n\n";

echo "1. Test the button and check logs for:\n";
echo "   - 'TabelHandler: Stored new custom text: Проверка'\n";
echo "   - 'TabelHandler: Retrieved custom text from conversation: Проверка'\n";
echo "   - 'TabelHandler: About to call addEntry with customText: Проверка'\n\n";

echo "2. Verify routing priority:\n";
echo "   - Check if handleNewProcheeConfirmation is called first\n";
echo "   - Check if handleActivityExecution is also called\n\n";

echo "3. Check TabelEntry debugging:\n";
echo "   - Look for 'TabelEntry::createTabelMessage called with:'\n";
echo "   - Verify idleReason parameter contains 'Проверка'\n\n";

echo "✅ IMMEDIATE ACTION NEEDED:\n";
echo "==========================\n";
echo "Test the button now and send the logs!\n";
echo "The extensive debugging should show exactly where the text is lost.\n";

?>