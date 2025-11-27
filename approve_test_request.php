<?php

// Script to manually approve a test access request

echo "=== Approve Test Access Request ===\n\n";

// Read the access requests file
$filePath = __DIR__ . '/data/access_requests.json';
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    $requests = json_decode($content, true);
    
    if (!empty($requests)) {
        echo "Found " . count($requests) . " requests\n";
        
        // Take the first request to approve
        $request = $requests[0];
        $userId = $request['user_id'];
        $userName = $request['user_name'];
        $userChatId = $request['chat_id'];
        
        echo "Approving request for user: {$userName} (ID: {$userId})\n";
        
        // To approve the request, we need to:
        // 1. Remove the request from the access_requests.json file
        // 2. Add the user to the database with admin access level
        
        // First, let's remove the request from the array
        $updatedRequests = array_filter($requests, function($req) use ($userId) {
            return $req['user_id'] != $userId;
        });
        
        // Re-index the array
        $updatedRequests = array_values($updatedRequests);
        
        echo "After removing request: " . count($updatedRequests) . " requests remain\n";
        
        // Save the updated requests back to the file
        $jsonData = json_encode($updatedRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filePath, $jsonData);
        
        echo "✓ Request removed from access_requests.json\n";
        
        // Now we would normally update the database to give the user admin access
        // But since we don't have direct access to the database here, we'll just
        // show what would need to be done
        
        echo "\nTo complete the approval process, you would need to:\n";
        echo "1. Add user ID {$userId} to the admins list in the database\n";
        echo "2. Or add user ID {$userId} to the \$superAdmins array in AdminService.php\n";
        
        echo "\nFor testing purposes, we'll add the user to the superAdmins array in AdminService.php\n";
        
        // Read the AdminService.php file
        $adminServicePath = __DIR__ . '/CustomCommands/Services/AdminService.php';
        if (file_exists($adminServicePath)) {
            $adminServiceContent = file_get_contents($adminServicePath);
            
            // Look for the superAdmins array
            if (preg_match('/private array \$superAdmins = \[(.*?)\];/s', $adminServiceContent, $matches)) {
                $currentArray = $matches[1];
                echo "Current superAdmins array:\n{$currentArray}\n";
                
                // Check if the user is already in the array
                if (strpos($currentArray, (string)$userId) === false) {
                    // Add the user to the array
                    $newArray = $currentArray . ",\n        {$userId}  // {$userName}";
                    $newContent = str_replace($currentArray, $newArray, $adminServiceContent);
                    
                    // Save the updated content
                    file_put_contents($adminServicePath, $newContent);
                    echo "✓ User {$userId} added to superAdmins array\n";
                } else {
                    echo "User {$userId} is already in superAdmins array\n";
                }
            } else {
                echo "Could not find superAdmins array in AdminService.php\n";
            }
        } else {
            echo "AdminService.php file not found\n";
        }
        
        echo "\n✓ Request approval process completed\n";
    } else {
        echo "No requests found to approve\n";
    }
} else {
    echo "access_requests.json file not found\n";
}

echo "\n=== Approval Process Complete ===\n";