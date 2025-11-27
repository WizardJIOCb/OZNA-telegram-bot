<?php
/**
 * Debug script to trace callback processing
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Debug Callback Processing ===\n\n";

// Function to simulate the full callback processing chain
function debugCallbackProcessing(string $callbackData): void {
    $logFile = __DIR__ . '/debug_callback_processing.log';
    
    // Clear the log file
    file_put_contents($logFile, '');
    
    $log = function($message) use ($logFile) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
        echo "[{$timestamp}] {$message}\n";
    };
    
    $log("Starting callback processing for: '{$callbackData}'");
    
    // Simulate CallbackqueryCommand::execute
    $log("CallbackqueryCommand::execute called");
    
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    $log("Fixed encoding, callback_data: '{$callbackData}'");
    
    // Early exit if no category specified
    if (strpos($callbackData, 'category_') !== 0 && strpos($callbackData, 'adm_req_') !== 0 && strpos($callbackData, 'acc_lvl_') !== 0) {
        $log("Exiting - no category specified");
        return;
    }
    
    $log("Category found, continuing processing");
    
    // Extract category
    $category = '';
    if (strpos($callbackData, 'adm_req_') === 0) {
        $category = $callbackData;
    } else if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        $category = mb_convert_encoding($category, 'UTF-8', 'auto');
    }
    
    $log("Extracted category: '{$category}'");
    
    // Simulate CallbackHandlerFactory::handleCallback
    $log("CallbackHandlerFactory::handleCallback called");
    
    // Simulate AdminHandler::canHandle
    $log("AdminHandler::canHandle called with: '{$callbackData}'");
    
    $startsWithCategoryAdmin = strpos($callbackData, 'category_Админка') === 0;
    $startsWithCategoryAdm = strpos($callbackData, 'category_Адм_') === 0;
    $equalsCategoryMain = $callbackData === 'category_Главное';
    $startsWithAdmReq = strpos($callbackData, 'adm_req_') === 0;
    $isAdmReqCallback = $startsWithAdmReq;
    $isAccessLevelCallback = strpos($callbackData, 'acc_lvl_') === 0;
    
    $canHandle = !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
    
    $log("AdminHandler::canHandle result: " . ($canHandle ? 'true' : 'false'));
    
    if ($canHandle) {
        $log("AdminHandler can handle this callback");
        
        // Simulate AdminHandler::handle
        $log("AdminHandler::handle called with category: '{$category}'");
        
        // Simulate user ID (should be an admin)
        $userId = 5261465966;
        $log("User ID: {$userId}");
        
        // Check if this is a special case
        if ($category === 'Админка_Заявки') {
            $log("Handling access requests");
            
            // Simulate AdminHandler::handleAccessRequests
            $log("AdminHandler::handleAccessRequests called");
            
            // Get all access requests
            $log("Getting all access requests");
            $accessRequestsFile = __DIR__ . '/data/access_requests.json';
            if (file_exists($accessRequestsFile)) {
                $log("Access requests file exists");
                $jsonData = file_get_contents($accessRequestsFile);
                if ($jsonData !== false) {
                    $log("File content read successfully");
                    $requests = json_decode($jsonData, true);
                    if (is_array($requests)) {
                        $log("Found " . count($requests) . " access requests");
                        
                        // Simulate AdminHandler::showAccessRequestsList
                        $log("Showing access requests list");
                        $text = "👥 <b>Заявки на доступ к боту</b>\n\n";
                        $text .= "Всего заявок: " . count($requests) . "\n\n";
                        
                        if (empty($requests)) {
                            $text .= "Нет новых заявок на доступ.";
                            $log("No access requests found");
                        } else {
                            $text .= "Выберите заявку для просмотра:\n\n";
                            $log("Showing " . min(count($requests), 20) . " requests");
                        }
                        
                        $log("Sending response with text: " . substr($text, 0, 100) . "...");
                    } else {
                        $log("Failed to decode JSON");
                    }
                } else {
                    $log("Failed to read file content");
                }
            } else {
                $log("Access requests file does not exist");
            }
        } else {
            $log("Unknown category: '{$category}'");
        }
    } else {
        $log("AdminHandler cannot handle this callback");
    }
    
    $log("Callback processing complete");
}

// Test with the callback data that should be sent by "Заявки на доступ" button
$callbackData = 'category_Админка_Заявки';
debugCallbackProcessing($callbackData);

echo "\n=== Debug Complete ===\n";
echo "Check the log file: debug_callback_processing.log\n";
