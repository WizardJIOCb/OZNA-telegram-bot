<?php
// Check the project table in database

try {
    $host = '82.146.42.213';
    $username = 'WizardJIOCb';
    $password = '7qQ#28051961';
    $database = 'tg_bot';
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    
    echo "Checking PROJECT table structure and data...\n\n";
    
    // Check table structure
    $result = $conn->query("DESCRIBE project");
    echo "PROJECT table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }
    
    // Check project data
    echo "\nProjects in DATABASE:\n";
    $result = $conn->query("SELECT * FROM project ORDER BY id");
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | Code: {$row['code']} | Name: {$row['name']} | Active: {$row['is_active']}\n";
    }
    
    // Count projects
    $result = $conn->query("SELECT COUNT(*) as count FROM project");
    $count = $result->fetch_assoc()['count'];
    echo "\nTotal projects in database: {$count}\n";
    
    // Compare with file
    echo "\nComparing with projects.json file:\n";
    $projectsFile = __DIR__ . '/data/projects.json';
    if (file_exists($projectsFile)) {
        $jsonData = file_get_contents($projectsFile);
        $fileProjects = json_decode($jsonData, true);
        echo "Projects in file: " . count($fileProjects) . "\n";
        echo "File projects: " . implode(', ', $fileProjects) . "\n";
        
        // Check which projects are missing
        $result = $conn->query("SELECT code FROM project WHERE is_active = 1 ORDER BY code");
        $dbProjects = [];
        while ($row = $result->fetch_assoc()) {
            $dbProjects[] = $row['code'];
        }
        
        $missingInFile = array_diff($dbProjects, $fileProjects);
        $missingInDb = array_diff($fileProjects, $dbProjects);
        
        if (!empty($missingInFile)) {
            echo "\nProjects in DATABASE but NOT in file:\n";
            foreach ($missingInFile as $project) {
                echo "- {$project}\n";
            }
        }
        
        if (!empty($missingInDb)) {
            echo "\nProjects in FILE but NOT in database:\n";
            foreach ($missingInDb as $project) {
                echo "- {$project}\n";
            }
        }
        
        if (empty($missingInFile) && empty($missingInDb)) {
            echo "\n✅ Projects are synchronized between database and file!\n";
        }
    } else {
        echo "projects.json file not found\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>