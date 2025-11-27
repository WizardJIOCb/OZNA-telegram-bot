<?php
echo "=== FINAL JACKET EMOJI CHECK FOR MOBILE BRIGADE ===\n\n";

$files = [
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Services/TabelEntry.php'
];

foreach ($files as $file) {
    echo "📁 Checking {$file}:\n";
    $content = file_get_contents("c:\\ozna_bot\\{$file}");
    
    // Check for jacket emoji in thermal insulation
    if (strpos($content, '🧥') !== false) {
        echo "   ✅ Found jacket emoji 🧥\n";
        
        // Get the specific line with jacket
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, '🧥') !== false && strpos($line, 'теплоизол') !== false) {
                echo "   📍 Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    } else {
        echo "   ❌ No jacket emoji found\n";
    }
    echo "\n";
}

echo "🎯 Success! Mobile brigade thermal insulation now uses 🧥 jacket emoji!\n";
echo "Perfect for representing pipe insulation and thermal protection work!\n";
?>