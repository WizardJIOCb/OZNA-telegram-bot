<?php

// This test simulates the exact flow that should happen when the mobile brigade button is pressed
// and adds extensive logging to identify where the issue might be

echo "=== Mobile Brigade Button Test ===\n";

// Simulate the callback data that should be sent when the button is pressed
$callback_data = 'category_Табель_Мобильная_Бригада';
echo "1. Callback data received: {$callback_data}\n";

// This would normally be processed by CallbackqueryCommand::execute()
echo "2. CallbackqueryCommand would process this callback data\n";

// Check if it passes the initial filter
if (strpos($callback_data, 'category_') === 0) {
    echo "3. Passed initial filter (starts with 'category_')\n";
} else {
    echo "3. FAILED initial filter - does not start with 'category_'\n";
    exit;
}

// The callback would be passed to CallbackHandlerFactory::handleCallback()
echo "4. CallbackHandlerFactory::handleCallback() called with: {$callback_data}\n";

// Extract category
$category = '';
if (strpos($callback_data, 'category_') === 0) {
    $category = substr($callback_data, strlen('category_'));
}
echo "5. Extracted category: {$category}\n";

// CallbackHandlerFactory would iterate through handlers and call canHandle()
echo "6. Checking handlers...\n";

// Simulate TabelHandler::canHandle()
$tabel_can_handle = strpos($callback_data, 'category_Табель') === 0;
echo "   TabelHandler::canHandle(): " . ($tabel_can_handle ? 'true' : 'false') . "\n";

if ($tabel_can_handle) {
    echo "7. TabelHandler selected to handle callback\n";
    
    // TabelHandler::handle() would be called
    echo "8. TabelHandler::handle() called with category: {$category}\n";
    
    // TabelHandler::handle() calls routeTabelCallback()
    echo "9. TabelHandler::routeTabelCallback() called with category: {$category}\n";
    
    // routeTabelCallback() calls handleOtherWorkTypes() for this category
    echo "10. TabelHandler::handleOtherWorkTypes() called with category: {$category}\n";
    
    // handleOtherWorkTypes() checks if category matches mobile brigade
    $is_mobile_brigade = ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг');
    echo "11. Mobile brigade check: " . ($is_mobile_brigade ? 'true' : 'false') . "\n";
    
    if ($is_mobile_brigade) {
        echo "12. Mobile brigade flow detected\n";
        
        // handleOtherWorkTypes() calls handleMobileBrigadeDateSelection()
        echo "13. TabelHandler::handleMobileBrigadeDateSelection() called\n";
        
        // handleMobileBrigadeDateSelection() creates date selection keyboard
        $prefix = 'category_Табель_Мобильная_Бригада';
        echo "14. Creating date selection keyboard with prefix: {$prefix}\n";
        
        // handleMobileBrigadeDateSelection() sends message with keyboard
        $message_text = '💼 <b>Выберите дату для Мобильной бригады</b>';
        echo "15. Sending message: {$message_text}\n";
        
        echo "\n=== SUCCESS ===\n";
        echo "The mobile brigade button should show a date selection menu with this text:\n";
        echo "\"{$message_text}\"\n";
        echo "================\n";
    } else {
        echo "12. Mobile brigade check failed\n";
    }
} else {
    echo "7. No handler found for callback\n";
}

echo "\nIf you're seeing this message but the button is not working in Telegram,\n";
echo "the issue is likely that the Telegram bot is not processing the callback properly.\n";
echo "This could be due to:\n";
echo "1. Caching issues - the old code might still be running\n";
echo "2. Webhook issues - the callback might not be reaching the server\n";
echo "3. Network issues - connectivity problems between Telegram and your server\n";

?>