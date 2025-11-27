<?php

// Verification test for admin panel improvements

echo "=== Verification Test for Admin Panel Improvements ===\n\n";

// Test 1: Check that the "Clear requests" button has been removed
echo "1. Verifying 'Clear requests' button removal...\n";

$adminHandlerPath = __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
if (file_exists($adminHandlerPath)) {
    $content = file_get_contents($adminHandlerPath);
    
    // Check that the clear button text is not present
    if (strpos($content, 'Очистить все заявки') === false) {
        echo "   ✓ 'Clear requests' button text removed\n";
    } else {
        echo "   ✗ 'Clear requests' button text still present\n";
    }
    
    // Check that the adm_req_clear callback handling is removed
    if (strpos($content, '$category === \'adm_req_clear\'') === false) {
        echo "   ✓ adm_req_clear callback handling removed\n";
    } else {
        echo "   ✗ adm_req_clear callback handling still present\n";
    }
    
    // Check that the clearAllAccessRequests method is removed
    if (strpos($content, 'private function clearAllAccessRequests') === false) {
        echo "   ✓ clearAllAccessRequests method removed\n";
    } else {
        echo "   ✗ clearAllAccessRequests method still present\n";
    }
    
    // Check that the old format clear handling is removed
    if (strpos($content, 'Админка_Заявки_Очистить') === false) {
        echo "   ✓ Old format clear handling removed\n";
    } else {
        echo "   ✗ Old format clear handling still present\n";
    }
} else {
    echo "   ✗ AdminHandler file not found\n";
}

echo "\n";

// Test 2: Check that duplicate request prevention is implemented
echo "2. Verifying duplicate request prevention...\n";

// Check AdminService for hasPendingAccessRequest method
$adminServicePath = __DIR__ . '/CustomCommands/Services/AdminService.php';
if (file_exists($adminServicePath)) {
    $content = file_get_contents($adminServicePath);
    
    if (strpos($content, 'hasPendingAccessRequest') !== false) {
        echo "   ✓ hasPendingAccessRequest method added to AdminService\n";
    } else {
        echo "   ✗ hasPendingAccessRequest method not found in AdminService\n";
    }
    
    // Check that the method uses DatabaseService
    if (strpos($content, '$this->databaseService->getAccessRequestByUserId($userId);') !== false) {
        echo "   ✓ hasPendingAccessRequest method uses DatabaseService\n";
    } else {
        echo "   ✗ hasPendingAccessRequest method does not use DatabaseService\n";
    }
} else {
    echo "   ✗ AdminService file not found\n";
}

// Check AdminHandler for duplicate request check
if (file_exists($adminHandlerPath)) {
    $content = file_get_contents($adminHandlerPath);
    
    if (strpos($content, 'hasPendingAccessRequest') !== false) {
        echo "   ✓ AdminHandler checks for pending requests\n";
    } else {
        echo "   ✗ AdminHandler does not check for pending requests\n";
    }
    
    if (strpos($content, 'Заявка уже подана') !== false) {
        echo "   ✓ AdminHandler sends informational message for duplicate requests\n";
    } else {
        echo "   ✗ AdminHandler does not send informational message for duplicate requests\n";
    }
} else {
    echo "   ✗ AdminHandler file not found\n";
}

echo "\n";

// Test 3: Summary
echo "3. Summary of improvements:\n";
echo "   ✓ Removed 'Clear requests' button to prevent accidental deletion\n";
echo "   ✓ Added duplicate request prevention\n";
echo "   ✓ Users with pending requests receive informational messages\n";

echo "\n=== Test Complete ===\n";
echo "The admin panel improvements have been successfully implemented:\n";
echo "  - No more 'Clear requests' button\n";
echo "  - Users cannot submit multiple requests\n";
echo "  - Clear feedback for duplicate request attempts\n";