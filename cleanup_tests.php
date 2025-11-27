<?php
// Projects to remove
$projectsToRemove = ['ТЕСТ123', 'ОИ111', 'ОИ000'];

// Database connection (same credentials as DatabaseService)
$conn = new mysqli(
    '82.146.42.213',
    'WizardJIOCb',
    '7qQ#28051961',
    'tg_bot'
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Searching for test projects in database...\n";

// Find and delete from project_moderation table
foreach ($projectsToRemove as $projectName) {
    $stmt = $conn->prepare("SELECT id, current_name, status FROM project_moderation WHERE current_name = ?");
    $stmt->bind_param("s", $projectName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo "Found: {$row['current_name']} (ID: {$row['id']}, Status: {$row['status']})\n";
        
        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM project_moderation WHERE id = ?");
        $deleteStmt->bind_param("i", $row['id']);
        
        if ($deleteStmt->execute()) {
            echo "✓ Deleted from database: {$projectName}\n";
        } else {
            echo "✗ Failed to delete from database: {$projectName}\n";
        }
        
        $deleteStmt->close();
    } else {
        echo "Not found in database: {$projectName}\n";
    }
    
    $stmt->close();
}

$conn->close();

// Remove from projects.json
$projectsFile = __DIR__ . '/data/projects.json';

if (file_exists($projectsFile)) {
    $projectsData = json_decode(file_get_contents($projectsFile), true);
    
    if (is_array($projectsData)) {
        $originalCount = count($projectsData);
        $projectsData = array_filter($projectsData, function($project) use ($projectsToRemove) {
            return !in_array($project, $projectsToRemove);
        });
        
        $removedCount = $originalCount - count($projectsData);
        
        if ($removedCount > 0) {
            // Reindex array
            $projectsData = array_values($projectsData);
            
            if (file_put_contents($projectsFile, json_encode($projectsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                echo "\n✓ Removed {$removedCount} project(s) from projects.json\n";
            } else {
                echo "\n✗ Failed to save projects.json\n";
            }
        } else {
            echo "\nNo test projects found in projects.json\n";
        }
    }
}

echo "\nCleanup complete!\n";
