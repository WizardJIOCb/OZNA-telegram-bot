<?php
/**
 * Verify that the "Дополнительные работы" button handler is correctly implemented
 */

echo "Verifying 'Дополнительные работы' button handler...\n\n";

// Read the TabelHandler.php file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check if the handler for cat_ТабЭлОб_ДопРаб exists
$handlerPattern = "/if\s*\(\s*\\\$\s*category\s*===\s*'Табель_ДопРаб_ЭлектрОб'\s*\|\|\s*\\\$\s*category\s*===\s*'cat_ТабЭлОб_ДопРаб'\s*\)/";

if (preg_match($handlerPattern, $tabelHandlerContent)) {
    echo "  ✓ Handler for 'cat_ТабЭлОб_ДопРаб' found in handleOtherWorkTypes method\n";
} else {
    echo "  ✗ Handler for 'cat_ТабЭлОб_ДопРаб' not found in handleOtherWorkTypes method\n";
    echo "  Looking for pattern: if (\\$category === 'Табель_ДопРаб_ЭлектрОб' || \\$category === 'cat_ТабЭлОб_ДопРаб')\n";
}

// Check if the confirmation callback handler exists
$confirmationHandlerPattern = "/'cat_ТабЭлОб_ДопРаб_yes' => 'Табель_ДопРаб_ЭлектрОб_yes'/";
if (preg_match($confirmationHandlerPattern, $tabelHandlerContent)) {
    echo "  ✓ Confirmation callback handler found\n";
} else {
    echo "  ✗ Confirmation callback handler not found\n";
}

echo "\nVerification completed.\n";