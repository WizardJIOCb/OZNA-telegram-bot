<?php
// Simple test to verify back button functionality

echo "BACK BUTTON VERIFICATION\n";
echo "=======================\n\n";

echo "1. MAIN EQUIPMENT MENUS (use 'Табель_ШМ_Back'):\n";
echo "   - Electrical Equipment Back Button\n";
echo "   - Automation Equipment Back Button\n";
echo "   - Fire Alarm Equipment Back Button\n";
echo "   - Security Alarm Equipment Back Button\n";
echo "   - Heating/Ventilation Equipment Back Button\n";
echo "   - Technological Equipment Back Button\n";
echo "   - Metrology Equipment Back Button\n";
echo "   → All should route to handleBackToSupervision() and show debug messages\n\n";

echo "2. ADDITIONAL WORKS MENUS (use parent callback):\n";
echo "   - Automation Additional Works Back Button: 'cat_ТабАвтом'\n";
echo "   - Fire Alarm Additional Works Back Button: 'cat_ТабПожСигн'\n";
echo "   - Security Alarm Additional Works Back Button: 'cat_ТабОхрСигн'\n";
echo "   - Heating/Ventilation Additional Works Back Button: 'cat_ТабВентОтопл'\n";
echo "   - Technological Additional Works Back Button: 'cat_ТабТехОб'\n";
echo "   - Metrology Additional Works Back Button: 'cat_ТабМетрол'\n";
echo "   - Electrical Additional Works Back Button: 'category_Табель_Электрооборудование'\n";
echo "   → All should route to their respective equipment menu handlers\n\n";

echo "If you're seeing 'Неизвестная команда табеля', it means:\n";
echo "1. The callback is not being recognized by TabelHandler::canHandle()\n";
echo "2. The callback is not being handled in the routing logic\n";
echo "3. There's an issue with the callback data being sent\n\n";

echo "Please tell me which specific back button is showing the error and what diagnostic message it shows.\n";
?>