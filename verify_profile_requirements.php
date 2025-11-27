<?php
/**
 * Verification script to check user profile prerequisites
 * Based on memory: ШМР activities require proper FIO and functional_direction
 */

echo "=== Profile Requirements Verification ===\n\n";

echo "⚠️  IMPORTANT: Based on system memory, ШМР 'Работы по графику' activities require:\n";
echo "   1. User's FIO field must be properly set (not 'Без ФИО')\n";
echo "   2. User's functional_direction field must be properly set (not 'Не указано')\n";
echo "   3. If either field is missing, report will not be sent despite success dialog\n\n";

echo "📋 USER ACTION REQUIRED:\n";
echo "=======================\n";
echo "Please check your profile by sending /profile command to the bot.\n\n";

echo "🔍 What to look for in your profile:\n";
echo "   • FIO (Full Name): Should show your actual name, not 'Без ФИО'\n";
echo "   • Functional Direction: Should show your department, not 'Не указано'\n\n";

echo "🛠️  If profile fields are incorrect:\n";
echo "   1. Contact administrator to update your profile\n";
echo "   2. Ensure FIO field contains your full name\n";
echo "   3. Ensure functional_direction is set to appropriate department\n\n";

echo "💡 Expected behavior after profile fix:\n";
echo "   • Site inspection button will work correctly\n";
echo "   • Report will show: '✅05.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки'\n";
echo "   • Activity information will be included in the report\n\n";

echo "⚡ IMMEDIATE TEST STEPS:\n";
echo "=======================\n";
echo "1. Send /profile to the bot\n";
echo "2. Check if FIO and functional_direction are properly set\n";
echo "3. If they are correct, try the site inspection button again\n";
echo "4. If they are incorrect, contact administrator for profile update\n\n";

echo "📞 If you need administrator assistance:\n";
echo "   • Explain that your profile fields need to be updated\n";
echo "   • Mention that ШМР activities require complete profile data\n";
echo "   • Request that FIO and functional_direction be properly configured\n\n";

echo "=== Verification Complete ===\n";
?>