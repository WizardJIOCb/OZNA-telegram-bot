<?php

// Detailed test of the callback handling flow for electrical equipment
echo "=== Detailed Callback Handling Flow Test ===\n\n";

// Simulate the callback data
$callbackData = 'category_Табель_Электрооборудование';

echo "Input callback data: {$callbackData}\n";
echo "Length: " . strlen($callbackData) . " bytes\n\n";

// Simulate CallbackHandlerFactory processing
echo "=== CallbackHandlerFactory Processing ===\n";

// Fix encoding issues with callback data - only once at the beginning
$processedCallbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
echo "After encoding fix: '{$processedCallbackData}'\n";

// Extract category from callback data
$category = '';

// Special handling for electrical equipment - check this first and return immediately
if (strpos($processedCallbackData, 'category_Табель_Электрооборудование') === 0) {
    $category = 'Табель_Электрооборудование';
    echo "Extracted category (special case): '{$category}'\n";
} else if (strpos($processedCallbackData, 'category_') === 0) {
    $category = substr($processedCallbackData, strlen('category_'));
    echo "Extracted category: '{$category}'\n";
} else {
    $category = $processedCallbackData;
    echo "No recognized prefix found, using as category: '{$category}'\n";
}

echo "Final extracted category: {$category}\n\n";

// Simulate handler checking in priority order
echo "=== Handler Priority Check ===\n";

// Define handler priorities (as in the actual code)
$handlers = [
    'AdminHandler' => 60,
    'AccessLevelHandler' => 25,
    'SupervisorHandler' => 25,
    'TabelHandler' => 20,
    'ProfileHandler' => 15,
    'FunctionalDirectionHandler' => 10,
    'HelpHandler' => 1,
    'MainMenuHandler' => 50
];

// Sort handlers by priority (highest first)
uasort($handlers, function($a, $b) {
    return $b - $a;
});

echo "Handler priorities (highest first):\n";
foreach ($handlers as $handler => $priority) {
    echo "  {$handler}: {$priority}\n";
}
echo "\n";

// Test each handler's canHandle method
echo "=== Testing Handler canHandle Methods ===\n";

foreach ($handlers as $handler => $priority) {
    $canHandle = false;
    
    switch ($handler) {
        case 'AdminHandler':
            // Check specifically for electrical equipment callback data
            if (strpos($processedCallbackData, 'Электрооборудование') !== false) {
                $canHandle = false; // AdminHandler should not handle electrical equipment
                echo "AdminHandler: detected электрооборудование, returning false\n";
            } else {
                $startsWithCategoryAdmin = str_starts_with($processedCallbackData, 'category_Админка');
                $startsWithCategoryAdm = str_starts_with($processedCallbackData, 'category_Адм_');
                $equalsCategoryMain = $processedCallbackData === 'category_Главное';
                $startsWithAdmReq = str_starts_with($processedCallbackData, 'adm_req_');
                $isAdmReqCallback = $startsWithAdmReq;
                $isAccessLevelCallback = str_starts_with($processedCallbackData, 'acc_lvl_');
                $canHandle = !$isAccessLevelCallback && !$equalsCategoryMain && 
                            ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
                
                if ($processedCallbackData === 'category_Админка_ЗапросДоступ') {
                    $canHandle = true;
                }
            }
            break;
            
        case 'TabelHandler':
            // Special handling for electrical equipment - check this first
            if (str_starts_with($processedCallbackData, 'category_Табель_Электрооборудование')) {
                $canHandle = true;
            } else if (strpos($processedCallbackData, 'Электрооборудование') !== false) {
                $canHandle = true;
            } else {
                $canHandle = str_starts_with($processedCallbackData, 'category_Табель');
            }
            break;
            
        case 'ProfileHandler':
            $canHandle = str_starts_with($processedCallbackData, 'category_Профиль');
            break;
            
        case 'HelpHandler':
            $canHandle = str_starts_with($processedCallbackData, 'category_Помощь');
            break;
            
        case 'MainMenuHandler':
            $canHandle = str_starts_with($processedCallbackData, 'category_Главное');
            break;
            
        case 'AccessLevelHandler':
            $canHandle = str_starts_with($processedCallbackData, 'acc_lvl_');
            break;
            
        case 'SupervisorHandler':
            $canHandle = str_starts_with($processedCallbackData, 'category_Супервайзер');
            break;
            
        case 'FunctionalDirectionHandler':
            $canHandle = str_starts_with($processedCallbackData, 'category_Профиль_ФН');
            break;
    }
    
    echo "{$handler} canHandle: " . ($canHandle ? 'true' : 'false') . "\n";
    
    if ($canHandle) {
        echo "  -> {$handler} will handle this callback\n";
        break;
    }
}

echo "\n=== Summary ===\n";
echo "The callback should be handled by TabelHandler.\n";
echo "If it's still not working, check the error logs for runtime issues.\n";