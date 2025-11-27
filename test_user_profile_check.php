<?php
/*
 * Test script to check user profile data requirements
 * This will help identify if missing profile data is preventing report sending
 */

echo "🔍 User Profile Check for ШМР Report Sending\n";
echo "=============================================\n\n";

// This would need the actual user ID from the person testing
echo "📋 PROFILE REQUIREMENTS FOR REPORT SENDING:\n";
echo "1. FIO (Full Name) must be set - check with /fio command\n";
echo "2. functional_direction must be set - check with /profile command\n\n";

echo "🔧 COMMON ISSUES:\n";
echo "- If FIO shows 'Без ФИО' → set with: /fio Фамилия Имя Отчество\n";
echo "- If functional_direction shows 'Не указано' → set with /profile command\n\n";

echo "📊 TO CHECK YOUR PROFILE DATA:\n";
echo "1. Send /profile command to the bot\n";
echo "2. Verify that both FIO and functional_direction are properly set\n";
echo "3. If either is missing, set them using the appropriate commands\n\n";

echo "🎯 EXPECTED BEHAVIOR AFTER FIXING PROFILE:\n";
echo "1. Click 'Работы по графику' → confirmation dialog appears\n";
echo "2. Click 'Да' → report should be sent immediately\n";
echo "3. You should see a success message with the report details\n\n";

echo "❗ IF PROFILE IS CORRECT BUT STILL NOT WORKING:\n";
echo "There may be an internal issue with the addEntry method that needs investigation.\n";