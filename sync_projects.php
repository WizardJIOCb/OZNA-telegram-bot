<?php
// Create a comprehensive project synchronization tool

try {
    $host = '82.146.42.213';
    $username = 'WizardJIOCb';
    $password = '7qQ#28051961';
    $database = 'tg_bot';
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    
    echo "PROJECT SYNCHRONIZATION TOOL\n";
    echo "============================\n\n";
    
    // Get projects from database
    echo "1. Loading projects from DATABASE...\n";
    $result = $conn->query("SELECT code, name FROM project WHERE is_active = 1 ORDER BY code");
    $dbProjects = [];
    while ($row = $result->fetch_assoc()) {
        $dbProjects[] = $row['code'];
        echo "   - {$row['code']} ({$row['name']})\n";
    }
    echo "   Database projects: " . count($dbProjects) . "\n\n";
    
    // Get projects from file
    echo "2. Loading projects from FILE...\n";
    $projectsFile = __DIR__ . '/data/projects.json';
    $fileProjects = [];
    if (file_exists($projectsFile)) {
        $jsonData = file_get_contents($projectsFile);
        $fileProjects = json_decode($jsonData, true) ?: [];
        foreach ($fileProjects as $project) {
            echo "   - {$project}\n";
        }
    } else {
        echo "   File does not exist\n";
    }
    echo "   File projects: " . count($fileProjects) . "\n\n";
    
    // Merge and synchronize
    echo "3. SYNCHRONIZATION...\n";
    $allProjects = array_unique(array_merge($dbProjects, $fileProjects));
    sort($allProjects);
    
    echo "   Combined unique projects: " . count($allProjects) . "\n";
    echo "   All projects:\n";
    foreach ($allProjects as $project) {
        $inDb = in_array($project, $dbProjects) ? "✓ DB" : "✗ DB";
        $inFile = in_array($project, $fileProjects) ? "✓ FILE" : "✗ FILE";
        echo "   - {$project} ({$inDb}, {$inFile})\n";
    }
    
    // Save synchronized list to file
    echo "\n4. SAVING SYNCHRONIZED LIST...\n";
    $dir = dirname($projectsFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "   Created directory: {$dir}\n";
    }
    
    $jsonData = json_encode($allProjects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($projectsFile, $jsonData);
    echo "   ✅ Saved synchronized project list to file\n";
    echo "   Total projects now available: " . count($allProjects) . "\n\n";
    
    // Show what was added
    $newFromDb = array_diff($dbProjects, $fileProjects);
    if (!empty($newFromDb)) {
        echo "5. NEWLY ADDED FROM DATABASE:\n";
        foreach ($newFromDb as $project) {
            echo "   + {$project}\n";
        }
        echo "\n";
    }
    
    echo "✅ SYNCHRONIZATION COMPLETE!\n\n";
    echo "The bot will now have access to all projects from both:\n";
    echo "- Database table 'project' (active projects)\n";
    echo "- Previously stored file projects\n\n";
    echo "Total projects available: " . count($allProjects) . "\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>