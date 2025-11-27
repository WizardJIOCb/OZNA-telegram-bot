<?php
/**
 * Check unique key constraint on project_moderation table
 */

$mysqli = new \mysqli('localhost', 'buhuser', '123456', 'buh_db');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

echo "=== Checking project_moderation Table Structure ===\n\n";

// Get table structure
$query = "SHOW CREATE TABLE project_moderation";
$result = $mysqli->query($query);
if ($result) {
    $row = $result->fetch_assoc();
    echo "CREATE TABLE Statement:\n";
    echo $row['Create Table'] . "\n\n";
}

// Get all indexes
$query = "SHOW INDEX FROM project_moderation";
$result = $mysqli->query($query);
echo "Indexes:\n";
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  Key: {$row['Key_name']} | Column: {$row['Column_name']} | Non_unique: {$row['Non_unique']}\n";
    }
}

echo "\n=== Checking Current Data ===\n\n";

// Check for potential duplicates that might exist despite unique key
$query = "SELECT current_name, status, is_active, COUNT(*) as count 
          FROM project_moderation 
          GROUP BY current_name, status, is_active
          HAVING count > 1";
$result = $mysqli->query($query);
if ($result && $result->num_rows > 0) {
    echo "Found duplicate combinations:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['current_name']} | status:{$row['status']} | active:{$row['is_active']} | count:{$row['count']}\n";
    }
} else {
    echo "No duplicates found in (current_name, status, is_active) combinations\n";
}

echo "\n=== Testing Duplicate Insert Scenarios ===\n\n";

// Get first active project to test with
$query = "SELECT current_name FROM project_moderation WHERE status='active' AND is_active=1 LIMIT 1";
$result = $mysqli->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $testProject = $row['current_name'];
    echo "Test project: {$testProject}\n\n";
    
    // Scenario 1: Try to insert same name with 'pending' status (should work due to unique key on current_name+status)
    echo "Scenario 1: Insert '{$testProject}' with status='pending' (should work)\n";
    $query = "INSERT INTO project_moderation (project_code, original_name, current_name, status, added_by, added_at) 
              VALUES (?, ?, ?, 'pending', 999999, NOW())";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $testProject, $testProject, $testProject);
    
    if ($stmt->execute()) {
        echo "  ✅ SUCCESS - Inserted with ID: {$mysqli->insert_id}\n";
        // Clean up
        $mysqli->query("DELETE FROM project_moderation WHERE id = {$mysqli->insert_id}");
        echo "  (Cleaned up test entry)\n";
    } else {
        echo "  ❌ FAILED - Error: {$stmt->error}\n";
    }
    
    // Scenario 2: Try to insert same name with 'active' status (should fail due to unique key)
    echo "\nScenario 2: Insert '{$testProject}' with status='active' (should fail)\n";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $testProject, $testProject, $testProject);
    
    // Change status to 'active'
    $query = "INSERT INTO project_moderation (project_code, original_name, current_name, status, added_by, added_at) 
              VALUES (?, ?, ?, 'active', 999999, NOW())";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $testProject, $testProject, $testProject);
    
    if ($stmt->execute()) {
        echo "  ❌ UNEXPECTED SUCCESS - Unique key not working! ID: {$mysqli->insert_id}\n";
        // Clean up
        $mysqli->query("DELETE FROM project_moderation WHERE id = {$mysqli->insert_id}");
    } else {
        echo "  ✅ CORRECTLY FAILED - Error: {$stmt->error}\n";
    }
}

$mysqli->close();
echo "\n=== Test Complete ===\n";
