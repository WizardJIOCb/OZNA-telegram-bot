<?php
/**
 * Debug script to identify which storage system is being used
 */

echo "=== STORAGE SYSTEM CONFLICT ANALYSIS ===\n\n";

echo "🔍 PROBLEM IDENTIFIED:\n";
echo "=======================\n";
echo "There are TWO different storage systems for custom text:\n\n";

echo "1. **NEW SYSTEM**:\n";
echo "   - Button sets: awaiting_custom_prochee_input = true\n";
echo "   - Text stored in: prochee_custom_text\n";
echo "   - Retrieved from: prochee_custom_text\n\n";

echo "2. **OLD SYSTEM**:\n";
echo "   - Button sets: awaiting_custom_input = true\n";
echo "   - Text stored in: custom_input_text\n";
echo "   - Retrieved from: custom_input_text\n\n";

echo "🚨 CONFLICT ANALYSIS:\n";
echo "=====================\n";

echo "In GenericmessageCommand.php, the flow is:\n";
echo "1. Check if isAwaitingNewProcheeInput() → calls handleNewProcheeInput()\n";
echo "2. Check if isAwaitingCustomInput() → calls handleCustomInput()\n\n";

echo "Both systems call the SAME TabelHandler->handleCustomInput() method!\n";
echo "This method has logic for BOTH old and new systems:\n\n";

echo "NEW system block (line ~1801):\n";
echo "if (awaiting_custom_prochee_input) {\n";
echo "    store in prochee_custom_text\n";
echo "    show confirmation with callback: cat_ТабЭлОб_Проч_yes\n";
echo "}\n\n";

echo "OLD system block (line ~1835):\n";
echo "if (awaiting_custom_input) {\n";
echo "    store in custom_input_text\n";
echo "    show confirmation with callback: cat_ТабЭлОб_Проч_yes\n";
echo "}\n\n";

echo "❗ BOTH USE THE SAME CALLBACK: cat_ТабЭлОб_Проч_yes\n\n";

echo "When user clicks 'Да', handleNewProcheeConfirmation() is called:\n";
echo "- It looks for prochee_custom_text\n";
echo "- If not found, falls back to custom_input_text\n\n";

echo "🔍 DIAGNOSIS:\n";
echo "=============\n";
echo "The issue might be:\n";
echo "1. NEW system button sets awaiting_custom_prochee_input = true\n";
echo "2. User enters text\n";
echo "3. GenericmessageCommand detects NEW system and calls handleNewProcheeInput()\n";
echo "4. handleNewProcheeInput() calls TabelHandler->handleCustomInput()\n";
echo "5. TabelHandler->handleCustomInput() processes NEW system correctly\n";
echo "6. Text stored in prochee_custom_text ✅\n";
echo "7. User clicks 'Да'\n";
echo "8. handleNewProcheeConfirmation() retrieves prochee_custom_text ✅\n";
echo "9. BUT... something else is sending '📊 Табель'\n\n";

echo "🎯 POSSIBLE CAUSES:\n";
echo "===================\n";
echo "1. **Multiple message sends**: Another part of code sends '📊 Табель'\n";
echo "2. **Wrong callback routing**: Callback goes to wrong handler\n";
echo "3. **Database issue**: addEntry() doesn't save the custom text properly\n";
echo "4. **Message override**: History chat message overrides user message\n\n";

echo "🔧 DEBUGGING STEPS:\n";
echo "===================\n";
echo "1. Check error logs for: 'CRITICAL DEBUG - About to send message to user'\n";
echo "2. Look for multiple message sends\n";
echo "3. Verify that only handleNewProcheeConfirmation() is called\n";
echo "4. Check if TextmenuCommand or other handlers interfere\n\n";

echo "✅ NEXT ACTION:\n";
echo "===============\n";
echo "Add unique debug markers to trace the exact message flow!\n";
?>