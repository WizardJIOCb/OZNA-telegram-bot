<?php
// Simple test script to verify project addition fix

// Read current projects from file
$filePath = __DIR__ . '/data/projects.json';
if (file_exists($filePath)) {
    $jsonData = file_get_contents($filePath);
    $projects = json_decode($jsonData, true);
    echo "Current projects in file:\n";
    foreach ($projects as $project) {
        echo "- $project\n";
    }
} else {
    echo "Projects file not found.\n";
}

// Simulate adding a project
echo "\nSimulating adding project 'TEST123'...\n";
$projects[] = 'TEST123';

// Save to file
$jsonData = json_encode($projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($filePath, $jsonData);
echo "Project 'TEST123' added and saved to file.\n";

// Read again to verify
if (file_exists($filePath)) {
    $jsonData = file_get_contents($filePath);
    $projects = json_decode($jsonData, true);
    echo "\nProjects in file after addition:\n";
    foreach ($projects as $project) {
        echo "- $project\n";
    }
}

echo "\nTest completed.\n";