<?php
/**
 * Verify that the message text has been updated correctly
 */

echo "Verifying that the message text has been updated correctly...\n\n";

// Read the TabelHandler.php file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check if the old message text with \n\n is gone
if (strpos($tabelHandlerContent, '<b>🛠️ Дополнительные работы - Электрооборудование</b>\n\nВыберите тип дополнительных работ:') !== false) {
    echo "  ✗ Old message text with \\n\\n still exists\n";
} else {
    echo "  ✓ Old message text with \\n\\n correctly removed\n";
}

// Check if the new message text without \n\n exists
if (strpos($tabelHandlerContent, '<b>🛠️ Дополнительные работы - Электрооборудование</b> Выберите тип дополнительных работ:') !== false) {
    echo "  ✓ New message text without \\n\\n correctly added\n";
} else {
    echo "  ✗ New message text without \\n\\n not found\n";
}

echo "\nVerification completed.\n";