<?php
// Debug script to show what callback data is sent by back buttons

echo "BACK BUTTON CALLBACK DEBUG\n";
echo "========================\n\n";

echo "Electrical Equipment Back Button:\n";
echo "  Callback: 'Табель_ШМ_Back'\n";
echo "  Should route to: handleBackToSupervision()\n\n";

echo "Automation Equipment Back Button:\n";
echo "  Callback: 'Табель_ШМ_Back'\n";
echo "  Should route to: handleBackToSupervision()\n\n";

echo "Fire Alarm Equipment Back Button:\n";
echo "  Callback: 'Табель_ШМ_Back'\n";
echo "  Should route to: handleBackToSupervision()\n\n";

echo "Security Alarm Equipment Back Button:\n";
echo "  Callback: 'Табель_ШМ_Back'\n";
echo "  Should route to: handleBackToSupervision()\n\n";

echo "Heating/Ventilation Equipment Back Button:\n";
echo "  Callback: 'Табель_ШМ_Back'\n";
echo "  Should route to: handleBackToSupervision()\n\n";

echo "Technological Equipment Back Button:\n";
echo "  Callback: 'Табель_ШМ_Back'\n";
echo "  Should route to: handleBackToSupervision()\n\n";

echo "Metrology Equipment Back Button:\n";
echo "  Callback: 'Табель_ШМ_Back'\n";
echo "  Should route to: handleBackToSupervision()\n\n";

echo "Additional Works Back Buttons:\n";
echo "  Automation: 'cat_ТабАвтом' -> handleAutomationEquipmentMenu()\n";
echo "  Fire Alarm: 'cat_ТабПожСигн' -> handleFireAlarmEquipmentMenu()\n";
echo "  Security Alarm: 'cat_ТабОхрСигн' -> handleSecurityAlarmEquipmentMenu()\n";
echo "  Heating/Ventilation: 'cat_ТабВентОтопл' -> handleHeatingVentilationEquipmentMenu()\n";
echo "  Technological: 'cat_ТабТехОб' -> handleTechnologicalEquipmentMenu()\n";
echo "  Metrology: 'cat_ТабМетрол' -> handleMetrologyMenu()\n";
echo "  Electrical: 'category_Табель_Электрооборудование' -> handleElectricalEquipmentMenu()\n\n";

echo "The issue is that we're looking for specific callbacks like:\n";
echo "  'Табель_Автоматизация_Back'\n";
echo "  'Табель_ПожарнаяСигнализация_Back'\n";
echo "  etc.\n\n";

echo "But the actual back buttons send:\n";
echo "  'Табель_ШМ_Back' (for main equipment menus)\n";
echo "  'cat_ТабXXX' (for additional works menus)\n\n";

echo "So we need to handle these actual callback values, not the ones we were looking for.\n";
?>