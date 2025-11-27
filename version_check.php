<?php
// Version checker - add this temporary endpoint to check file versions on server
header('Content-Type: text/plain');

echo "=== FILE VERSION CHECKER ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

$files = [
    'CustomCommands/CallbackqueryCommand.php',
    'CustomCommands/Services/CallbackHandlerFactory.php'
];

foreach ($files as $file) {
    echo "File: $file\n";
    if (file_exists($file)) {
        echo "  Last modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
        echo "  Size: " . filesize($file) . " bytes\n";
        
        // Check for our fix pattern
        $content = file_get_contents($file);
        if (strpos($content, 'cat_РЁРњР') !== false) {
            echo "  ✅ Contains corruption fix patterns\n";
        } else {
            echo "  ❌ Missing corruption fix patterns\n";
        }
        
        if (strpos($content, 'РЁРњР ') !== false) {
            echo "  ✅ Contains ШМР corruption fix\n";
        } else {
            echo "  ❌ Missing ШМР corruption fix\n";
        }
    } else {
        echo "  ❌ File not found\n";
    }
    echo "\n";
}

echo "=== END ===\n";
?>