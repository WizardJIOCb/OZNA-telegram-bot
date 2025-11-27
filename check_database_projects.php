<?php
// Simple script to check database for project sources

try {
    $host = '82.146.42.213';
    $username = 'WizardJIOCb';
    $password = '7qQ#28051961';
    $database = 'tg_bot';
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    
    echo "Checking for project-related tables and data...\n\n";
    
    // Check if there's a projects table
    $result = $conn->query("SHOW TABLES LIKE '%project%'");
    echo "Tables with 'project' in name:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
    
    // Check if there are any other project sources
    $result = $conn->query("SHOW TABLES");
    echo "\nAll tables in database:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
    
    // Check for any project-related data in existing tables
    echo "\nChecking ozna_data table for project info:\n";
    $result = $conn->query("SELECT DISTINCT JSON_KEYS(data) as keys FROM ozna_data WHERE data IS NOT NULL LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        echo "Data keys: " . $row['keys'] . "\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>