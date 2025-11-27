<?php

// Test to verify conversation debugging for persistent "табель" issue
echo "=== CONVERSATION STATE DEBUGGING FOR PERSISTENT ISSUE ===\n\n";

echo "🚨 Problem: Custom text 'Проверка' is being replaced with 'табель'\n\n";

echo "🔍 Enhanced Debugging Added:\n\n";

echo "1. **Conversation Storage Verification**:\n";
echo "   - 'Stored and re-read custom text: [SHOULD_BE_YOUR_INPUT]'\n";
echo "   - 'CRITICAL ERROR - Custom text not properly stored!' (if storage fails)\n\n";

echo "2. **Full Conversation Notes Dump**:\n";
echo "   - Complete dump of all conversation data\n";
echo "   - Check for conflicting or cached values\n\n";

echo "3. **Legacy Fallback Check**:\n";
echo "   - Checks 'custom_input_text' if 'prochee_custom_text' is missing\n";
echo "   - Helps identify if old vs new state systems are conflicting\n\n";

echo "🎯 **Key Questions the Debug Will Answer**:\n\n";
echo "❓ Is 'Проверка' being correctly stored in conversation?\n";
echo "❓ Is 'Проверка' being correctly retrieved from conversation?\n";
echo "❓ Are there conflicting conversation notes causing issues?\n";
echo "❓ Is the conversation state being shared/cached between sessions?\n\n";

echo "📋 **Test Steps**:\n";
echo "1. Click 'Прочее' button\n";
echo "2. Enter 'Проверка'\n";
echo "3. Click 'Да'\n";
echo "4. Check logs for conversation debugging info\n\n";

echo "🔍 **What to Look For in Logs**:\n";
echo "- 'VERIFICATION - Stored and re-read custom text: Проверка' ✅\n";
echo "- 'VERIFICATION - Stored and re-read custom text: табель' ❌ (indicates caching issue)\n";
echo "- 'CRITICAL ERROR - Custom text not properly stored!' ❌ (indicates storage failure)\n\n";

echo "✅ Enhanced conversation debugging ready!\n";
echo "This will help us identify if the issue is in conversation storage/retrieval.\n";

?>