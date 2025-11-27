<?php
/**
 * URGENT DEBUGGING GUIDE: What to check when testing the "Да" button
 */

echo "=== URGENT DEBUGGING GUIDE FOR 'ДА' BUTTON ===\n\n";

echo "I've added extensive debug logging to track exactly where the button fails.\n\n";

echo "🔍 TESTING STEPS:\n";
echo "================\n";
echo "1. Click 'Прочее' button in Telegram\n";
echo "2. Enter custom text (e.g., 'Тестовая работа')\n";
echo "3. Click 'Да' button\n";
echo "4. Immediately check server logs\n\n";

echo "📋 EXPECTED LOG ENTRIES (in order):\n";
echo "====================================\n\n";

echo "When you click 'Да', you should see these logs:\n\n";

echo "LOG 1: Callback received by TabelHandler\n";
echo "----------------------------------------\n";
echo "=== URGENT DEBUG: routeTabelCallback ENTRY ===\n";
echo "TabelHandler: routeTabelCallback called with category: 'новое_прочее_yes'\n";
echo "TabelHandler: Checking if category matches 'новое_прочее_yes': YES\n\n";

echo "LOG 2: Routing to confirmation handler\n";
echo "--------------------------------------\n";
echo "=== URGENT DEBUG: CALLBACK RECEIVED ===\n";
echo "TabelHandler: CALLBACK 'новое_прочее_yes' RECEIVED AND RECOGNIZED!\n";
echo "TabelHandler: About to call handleNewProcheeConfirmation()\n\n";

echo "LOG 3: Confirmation method entry\n";
echo "--------------------------------\n";
echo "=== URGENT DEBUG: handleNewProcheeConfirmation ENTRY POINT ===\n";
echo "TabelHandler: handleNewProcheeConfirmation method HAS BEEN CALLED!\n";
echo "TabelHandler: This proves the callback routing is working!\n\n";

echo "LOG 4: Processing data\n";
echo "---------------------\n";
echo "TabelHandler: chatId: [CHAT_ID], userId: [USER_ID]\n";
echo "TabelHandler: Retrieved custom text from conversation: '[YOUR_TEXT]'\n";
echo "TabelHandler: Tabel entry data - FIO: '[NAME]', Date: '[DATE]', Project: '[PROJECT]'\n\n";

echo "LOG 5: Success\n";
echo "-------------\n";
echo "TabelHandler: TabelEntry->addEntry result: SUCCESS\n";
echo "TabelHandler: Tabel message created successfully\n\n";

echo "🚨 TROUBLESHOOTING:\n";
echo "===================\n\n";

echo "IF YOU DON'T SEE LOG 1:\n";
echo "   ❌ TabelHandler is not being selected\n";
echo "   ❌ Server may need restart\n";
echo "   ❌ Check if another handler is intercepting\n\n";

echo "IF YOU SEE LOG 1 BUT NOT LOG 2:\n";
echo "   ❌ Category doesn't match 'новое_прочее_yes'\n";
echo "   ❌ Encoding issue with callback data\n";
echo "   ❌ Wrong callback being sent\n\n";

echo "IF YOU SEE LOG 2 BUT NOT LOG 3:\n";
echo "   ❌ Method call failed\n";
echo "   ❌ PHP fatal error\n";
echo "   ❌ Exception thrown\n\n";

echo "IF YOU SEE LOG 3 BUT NO SUCCESS:\n";
echo "   ❌ Missing conversation data\n";
echo "   ❌ User validation failed (FIO/functional direction)\n";
echo "   ❌ Database error\n";
echo "   ❌ TabelEntry service error\n\n";

echo "📂 WHERE TO CHECK LOGS:\n";
echo "=======================\n";
echo "• Windows: Check PHP error log or Apache error log\n";
echo "• Development: Look in console/terminal output\n";
echo "• Production: /var/log/apache2/error.log or /var/log/php/error.log\n\n";

echo "⚡ IMMEDIATE ACTION:\n";
echo "===================\n";
echo "1. Test the button now\n";
echo "2. Check logs immediately\n";
echo "3. Report which log entries you see\n";
echo "4. This will pinpoint exactly where it fails\n\n";

echo "✅ DEBUGGING GUIDE COMPLETE - READY TO TEST!\n";
?>