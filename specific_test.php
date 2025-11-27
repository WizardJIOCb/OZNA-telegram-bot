<?php
/**
 * Specific test for the exact content we're looking for
 */

echo "=== SPECIFIC CONTENT TEST ===\n\n";

// Test the exact content we're looking for
$content = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. Looking for back button routing:\n";
echo "=================================\n";

$pattern1 = "/if \(\\\$category === 'Табель_ШМ_Back'\)/";
$pattern2 = "/return \\\$this->handleBackToSupervision\(\\\$/";

if (preg_match($pattern1, $content)) {
    echo "✅ Found back button routing condition\n";
} else {
    echo "❌ Missing back button routing condition\n";
    // Let's search for the exact line
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (strpos($line, 'Табель_ШМ_Back') !== false) {
            echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
        }
    }
}

if (preg_match($pattern2, $content)) {
    echo "✅ Found handleBackToSupervision call\n";
} else {
    echo "❌ Missing handleBackToSupervision call\n";
}

echo "\n2. Looking for Warranty Equipment keyboard logic:\n";
echo "=============================================\n";

$content2 = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

$pattern3 = "/\\\$backButtonCallback = \\\$context === 'supervision'\\s*\\? 'Табель_ШМ_Back'/";

if (preg_match($pattern3, $content2)) {
    echo "✅ Found Warranty Equipment keyboard logic\n";
} else {
    echo "❌ Missing Warranty Equipment keyboard logic\n";
    // Let's search for the exact lines
    $lines = explode("\n", $content2);
    $inMethod = false;
    foreach ($lines as $i => $line) {
        if (strpos($line, 'createWarrantyEquipmentKeyboard') !== false) {
            $inMethod = true;
            echo "Found method at line " . ($i + 1) . "\n";
        }
        
        if ($inMethod && (strpos($line, 'Табель_ШМ_Back') !== false || strpos($line, 'supervision') !== false)) {
            echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
        }
        
        if ($inMethod && strpos($line, '}') === 0 && trim($line) === '}') {
            $inMethod = false;
        }
    }
}

echo "\nTest completed.\n";
?>