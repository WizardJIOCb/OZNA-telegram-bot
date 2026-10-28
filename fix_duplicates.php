<?php
// Remove all duplicate functions after line 7890

$file_path = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
$lines = file($file_path);

echo "Original: " . count($lines) . " lines\n";

// Keep everything up to line 7890, then skip to where unique functions start again
// Find where duplicates end by searching for a unique function

// Find first occurrence of handleProjectCodeInput (it should be unique)
$unique_start = null;
for ($i = 7890; $i < count($lines); $i++) {
    if (strpos($lines[$i], 'private function handleProjectCodeInput') !== false) {
        // Go back to find the comment block
        for ($j = $i - 1; $j >= 0; $j--) {
            if (trim($lines[$j]) === '/**') {
                $unique_start = $j;
                break 2;
            }
        }
    }
}

if ($unique_start) {
    echo "Found unique functions starting at line: " . ($unique_start + 1) . "\n";
    $new_lines = array_merge(
        array_slice($lines, 0, 7890),
        array_slice($lines, $unique_start)
    );
    
    file_put_contents($file_path, implode('', $new_lines));
    echo "Fixed! New file: " . count($new_lines) . " lines\n";
    echo "Removed: " . (count($lines) - count($new_lines)) . " duplicate lines\n";
} else {
    echo "ERROR: Could not find unique function start!\n";
}
