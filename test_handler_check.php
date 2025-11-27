<?php
echo "Testing TabelHandler mapping...\n";

$content = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Check for the mapping
if (strpos($content, "'category_Табель_ИсполнениеОбязМастера' => 'Табель_ИсполнениеОбязМастера'") !== false) {
    echo "✅ Mapping found in TabelHandler.php\n";
} else {
    echo "❌ Mapping NOT found in TabelHandler.php\n";
}

// Check the handler
if (strpos($content, "if (\$category === 'Табель_ИсполнениеОбязМастера')") !== false) {
    echo "✅ Handler found in TabelHandler.php\n";
} else {
    echo "❌ Handler NOT found in TabelHandler.php\n";
}

// Check debug messages
if (strpos($content, '🎯 MASTER DUTIES CHECK') !== false) {
    echo "✅ Debug messages found in TabelHandler.php\n";
} else {
    echo "❌ Debug messages NOT found in TabelHandler.php\n";
}