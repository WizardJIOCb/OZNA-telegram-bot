<?php
// Script to remove duplicate lines from ProjectHandler.php

$file_path = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

// Read all lines
$lines = file($file_path);
$total = count($lines);

echo "Original file: $total lines\n";

// Remove lines 7884-8719 (0-indexed: 7883-8718)
// Keep lines 0-7883 and 8719+
$new_lines = array_merge(
    array_slice($lines, 0, 7883),
    array_slice($lines, 8719)
);

$new_total = count($new_lines);
$removed = $total - $new_total;

// Write back
file_put_contents($file_path, implode('', $new_lines));

echo "Fixed! Removed $removed lines of duplicate code\n";
echo "New file: $new_total lines\n";
