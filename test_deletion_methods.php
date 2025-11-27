<?php
// Simple test to verify the deletion methods exist

echo "Testing if deletion methods exist in AdminHandler...\n\n";

// Read the AdminHandler file
$content = file_get_contents(__DIR__ . '/CustomCommands/Handlers/AdminHandler.php');

// Check for the methods
if (strpos($content, 'handleDeleteSpecificProject') !== false) {
    echo "✓ handleDeleteSpecificProject method exists\n";
} else {
    echo "✗ handleDeleteSpecificProject method is missing\n";
}

if (strpos($content, 'handleConfirmDeleteProject') !== false) {
    echo "✓ handleConfirmDeleteProject method exists\n";
} else {
    echo "✗ handleConfirmDeleteProject method is missing\n";
}

// Check for the routing logic
if (strpos($content, "str_starts_with(\$category, 'Адм_УдПр_')") !== false) {
    echo "✓ Routing for Адм_УдПр_ exists\n";
} else {
    echo "✗ Routing for Адм_УдПр_ is missing\n";
}

if (strpos($content, "str_starts_with(\$category, 'Админка_ПодтвУдал_')") !== false) {
    echo "✓ Routing for Админка_ПодтвУдал_ exists\n";
} else {
    echo "✗ Routing for Админка_ПодтвУдал_ is missing\n";
}

echo "\nTest completed.\n";