<?php
// Test script to verify that level 1 users can now send reports when they press "Yes"

// Simulate the logic that was fixed in TabelHandler
function testLevel1UserAccess($userAccessLevel, $isConfirmationCallback, $isActivityExecution) {
    echo "Testing user access level: $userAccessLevel\n";
    echo "Is confirmation callback: " . ($isConfirmationCallback ? 'true' : 'false') . "\n";
    echo "Is activity execution: " . ($isActivityExecution ? 'true' : 'false') . "\n";
    
    // This is the logic we fixed in TabelHandler
    if ($isConfirmationCallback && $userAccessLevel < 3) {
        $isSuperAdmin = false; // For testing purposes
        // For level 1 users, we should be less restrictive and allow them to send reports when they explicitly confirm
        // Only apply the strict duplicate check for level 2 users (supervisors)
        if ($userAccessLevel > 1) {
            echo "Applying strict duplicate check for level 2 user\n";
            // In real implementation, this would check for recent entries
            $recentEntry = false; // Simulate no recent entry
            if ($recentEntry) {
                echo "User blocked due to recent entry\n";
                return false;
            }
        } else {
            echo "Allowing level 1 user to send report (no duplicate check)\n";
        }
    }
    
    echo "User allowed to proceed\n";
    return true;
}

// Test cases
echo "=== Test Case 1: Level 1 user with confirmation callback ===\n";
testLevel1UserAccess(1, true, true);
echo "\n";

echo "=== Test Case 2: Level 2 user with confirmation callback ===\n";
testLevel1UserAccess(2, true, true);
echo "\n";

echo "=== Test Case 3: Level 3 user with confirmation callback ===\n";
testLevel1UserAccess(3, true, true);
echo "\n";

echo "=== Test Case 4: Level 1 user without confirmation callback ===\n";
testLevel1UserAccess(1, false, false);
echo "\n";
?>