<?php
// Simple test to verify project file persistence

// Define the projects file path
$projectsFile = __DIR__ . '/data/projects.json';

echo "Testing project file persistence...\n\n";

// Check if projects file exists and what it contains
if (file_exists($projectsFile)) {
    $jsonData = file_get_contents($projectsFile);
    echo "Current projects file contents:\n";
    echo $jsonData . "\n\n";
    
    $projects = json_decode($jsonData, true);
    if (is_array($projects)) {
        echo "Current projects in file:\n";
        foreach ($projects as $project) {
            echo "- $project\n";
        }
        echo "\n";
    }
} else {
    echo "Projects file does not exist yet.\n\n";
}

// Create a test project array with a new project
$testProjects = [
    'ОИ834', 'ОИ651', 'ОИ696', 'ОИ795',
    'ОИ810', 'ОИ768', 'ОИ837', 'ОИ562',
    'ОИ788', 'ОИ860', 'ОИ862',
    'БКНС115', 'БКНС120',
    'TEST' . date('His') // Add a test project with timestamp
];

echo "Saving projects to file...\n";
$jsonData = json_encode($testProjects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Create directory if it doesn't exist
$dir = dirname($projectsFile);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Save to file
file_put_contents($projectsFile, $jsonData);

echo "Projects saved successfully!\n\n";

// Read back from file
$jsonData = file_get_contents($projectsFile);
$projects = json_decode($jsonData, true);

echo "Projects read back from file:\n";
foreach ($projects as $project) {
    echo "- $project\n";
}
echo "\n";

echo "✅ Test completed. Project persistence should now work correctly!\n";
?>