<?php
/**
 * Cleanup test projects from database
 */

require_once __DIR__ . '/set.php';

$projectsToRemove = ['ТЕСТ123', 'ОИ111', 'ОИ000'];

echo "=== Cleanup Test Projects ===\n\n";

// Connect to database
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Check current state
echo "Current state:\n";
$stmt = $db->prepare("SELECT id, current_name, status, is_active FROM project_moderation WHERE current_name IN (?, ?, ?) ORDER BY id");
$stmt->bind_param("sss", $projectsToRemove[0], $projectsToRemove[1], $projectsToRemove[2]);
$stmt->execute();
$result = $stmt->get_result();

$projectIds = [];
while ($row = $result->fetch_assoc()) {
    echo "  ID: {$row['id']} | Name: {$row['current_name']} | Status: {$row['status']} | Active: {$row['is_active']}\n";
    $projectIds[] = $row['id'];
}

if (empty($projectIds)) {
    echo "\nNo test projects found in database.\n";
} else {
    echo "\nDeleting " . count($projectIds) . " test projects...\n";
    
    // Delete from database
    $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
    $stmt = $db->prepare("DELETE FROM project_moderation WHERE id IN ($placeholders)");
    $types = str_repeat('i', count($projectIds));
    $stmt->bind_param($types, ...$projectIds);
    
    if ($stmt->execute()) {
        echo "✓ Deleted from database\n";
    } else {
        echo "✗ Failed to delete from database: " . $stmt->error . "\n";
    }
}

// Also remove from projects.json if exists
$projectsFile = __DIR__ . '/data/projects.json';
if (file_exists($projectsFile)) {
    $projects = json_decode(file_get_contents($projectsFile), true);
    $originalCount = count($projects);
    
    $projects = array_values(array_diff($projects, $projectsToRemove));
    
    $removedCount = $originalCount - count($projects);
    
    if ($removedCount > 0) {
        file_put_contents($projectsFile, json_encode($projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "✓ Removed {$removedCount} projects from projects.json\n";
    } else {
        echo "  No test projects found in projects.json\n";
    }
}

echo "\n=== Cleanup Complete ===\n";

$db->close();
