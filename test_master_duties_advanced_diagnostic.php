<?php
/**
 * ADVANCED MASTER DUTIES DIAGNOSTIC
 * This checks for potential conversation state and access issues
 */

echo "🔍 ADVANCED MASTER DUTIES DIAGNOSTIC\n";
echo "====================================\n\n";

echo "1. CONVERSATION STATE REQUIREMENTS:\n";
echo "===================================\n";
echo "For Master Duties to work, the user must:\n";
echo "✓ Be in 'Отдел ШМР' (supervision) department\n";
echo "✓ Have selected a date\n";
echo "✓ Have selected a project (project_code in conversation)\n";
echo "✓ Be in 'Проектная деятельность' menu\n";
echo "✓ Have proper access level (not guest)\n";
echo "✓ Have FIO and functional direction set\n\n";

echo "2. POTENTIAL BLOCKING CONDITIONS:\n";
echo "=================================\n";
echo "Conditions that could block Master Duties:\n";
echo "a) Access level = 0 (guest) → blocked\n";
echo "b) Missing FIO → blocked\n";
echo "c) Missing functional direction → blocked\n";
echo "d) Missing project_code in conversation → might fail\n";
echo "e) Recent tabel entry (for non-admin users) → blocked\n";
echo "f) Database connection issues → might fail\n\n";

echo "3. DEBUGGING STEPS:\n";
echo "===================\n";
echo "To identify the exact issue:\n\n";

echo "STEP 1: Check if button appears\n";
echo "- Navigate: /tabel → ШМР → Project → Проектная деятельность\n";
echo "- Look for: '👷 Исполнение обязанностей мастера' button\n";
echo "- If missing: MessageService issue\n";
echo "- If present: Continue to step 2\n\n";

echo "STEP 2: Check button response\n";
echo "- Click the Master Duties button\n";
echo "- Expected: Confirmation dialog\n";
echo "- If no response: Handler routing issue\n";
echo "- If error message: Access/state issue\n\n";

echo "STEP 3: Check logs for our debug messages\n";
echo "Look for these specific log entries:\n";
echo "- 'TabelHandler: handle called with category...'\n";
echo "- '🔥 MASTER DUTIES: Handling Master Duties confirmation'\n";
echo "- 'TabelHandler: unknown command' (if handler not reached)\n\n";

echo "4. TEMPORARY DEBUG SCRIPT:\n";
echo "==========================\n";
echo "Add this temporary code to TabelHandler.php handle() method\n";
echo "right after the extractCategoryFromCallback() call:\n\n";
echo "```php\n";
echo "// TEMPORARY DEBUG FOR MASTER DUTIES\n";
echo "if (strpos(\$callbackData, 'ТабИсполнОбяз') !== false || \n";
echo "    strpos(\$category, 'ИсполнениеОбязМастера') !== false) {\n";
echo "    error_log('🎯 MASTER DUTIES DEBUG: Raw callback: ' . \$callbackData);\n";
echo "    error_log('🎯 MASTER DUTIES DEBUG: Extracted category: ' . \$category);\n";
echo "    error_log('🎯 MASTER DUTIES DEBUG: User ID: ' . \$userId);\n";
echo "    error_log('🎯 MASTER DUTIES DEBUG: Chat ID: ' . \$chatId);\n";
echo "    error_log('🎯 MASTER DUTIES DEBUG: Conversation notes: ' . json_encode(\$conversation->notes));\n";
echo "}\n";
echo "```\n\n";

echo "5. NEXT STEPS:\n";
echo "==============\n";
echo "1. Test the button and observe behavior\n";
echo "2. Check error logs for our debug messages\n";
echo "3. If no debug messages appear → TabelHandler not receiving callback\n";
echo "4. If debug messages appear but no response → Handler logic issue\n";
echo "5. If access/FIO error → User setup issue\n\n";

echo "🎯 CRITICAL QUESTIONS TO ANSWER:\n";
echo "================================\n";
echo "When you click the Master Duties button:\n";
echo "1. Does ANYTHING happen? (loading, error, etc.)\n";
echo "2. Do you see any error messages?\n";
echo "3. Does the button appear pressed/clicked?\n";
echo "4. Are you using the correct navigation path?\n";
echo "5. Do other buttons in the same menu work?\n\n";

echo "=====================================\n";
echo "🏁 DIAGNOSTIC COMPLETE\n";
echo "Please test and report the exact behavior you observe!\n";
?>