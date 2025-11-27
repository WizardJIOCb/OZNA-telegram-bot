<?php
// Debug script to verify exact routing issue for cat_ТабИнстр_yes

// Simplified debug without dependencies

echo "=== ROUTING DEBUG ANALYSIS ===\n\n";

// Simulate TabelHandler class
class MockTabelHandler {
    public function isActivityExecution(string $category): bool
    {
        error_log("isActivityExecution called with: {$category}");
        
        // Check standard activity execution
        if (str_contains($category, '_yes')) {
            // Handle short callback format for electrical equipment first
            if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $category, $matches)) {
                error_log("isActivityExecution: matched electrical pattern");
                return true;
            }
            
            // Handle short callback format for commissioning works
            if (preg_match('/^cat_(.+)_yes$/', $category, $matches)) {
                $shortActivityKey = $matches[1];
                if ($shortActivityKey === 'ТабПНРаботы') {
                    error_log("isActivityExecution: matched commissioning works pattern");
                    return true;
                }
                
                // Handle basic commissioning activities
                $basicCommissioningActivities = [
                    'ТабИнстр' => 'Инструктаж',
                    'ТабДорога' => 'Дорога',
                    'ТабПодгПоезд' => 'ПодготовкаКПоездке',
                    'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
                ];
                
                if (isset($basicCommissioningActivities[$shortActivityKey])) {
                    error_log("isActivityExecution: matched basic commissioning activity: {$shortActivityKey}");
                    return true;
                }
            }
        }
        
        error_log("isActivityExecution: returning false");
        return false;
    }
    
    public function isActivityConfirmation(string $category): bool
    {
        error_log("isActivityConfirmation called with: {$category}");
        
        // Check for short format commissioning activity confirmations
        if (preg_match('/^cat_(ТабИнстр|ТабДорога|ТабПодгПоезд|ТабСдачаОтч|ТабОхрСигн|ТабОтоплВент|ТабТехОб|ТабМетрол|ТабКомплОпр|ТабРежНал|ТабПНРаботы)_yes$/', $category)) {
            error_log("isActivityConfirmation: matched commissioning pattern");
            return true;
        }
        
        // Check for electrical equipment short format confirmations
        if (preg_match('/^cat_ТабЭлОб_.+_yes$/', $category)) {
            error_log("isActivityConfirmation: matched electrical pattern");
            return true;
        }
        
        $result = str_contains($category, '_yes') && str_contains($category, 'Табель_');
        error_log("isActivityConfirmation: fallback check result: " . ($result ? 'true' : 'false'));
        return $result;
    }
}

$handler = new MockTabelHandler();
$testCallback = 'cat_ТабИнстр_yes';

echo "Testing callback: {$testCallback}\n\n";

echo "1. Testing isActivityExecution():\n";
$executionResult = $handler->isActivityExecution($testCallback);
echo "   Result: " . ($executionResult ? 'TRUE' : 'FALSE') . "\n\n";

echo "2. Testing isActivityConfirmation():\n";
$confirmationResult = $handler->isActivityConfirmation($testCallback);
echo "   Result: " . ($confirmationResult ? 'TRUE' : 'FALSE') . "\n\n";

echo "=== ROUTING FLOW SIMULATION ===\n\n";

if ($executionResult) {
    echo "✓ Would route to handleActivityExecution() - SUCCESS!\n";
} elseif ($confirmationResult) {
    echo "✓ Would route to handleActivityConfirmation() - SUCCESS!\n";
} else {
    echo "✗ Would fall through to handleOtherWorkTypes() - ERROR!\n";
}

echo "\n=== WORKING BUTTON COMPARISON ===\n\n";

$workingCallback = 'cat_ТабЭлОб_РабПоГр_yes';
echo "Testing working callback: {$workingCallback}\n\n";

echo "1. Testing isActivityExecution() for working button:\n";
$workingExecutionResult = $handler->isActivityExecution($workingCallback);
echo "   Result: " . ($workingExecutionResult ? 'TRUE' : 'FALSE') . "\n\n";

if ($workingExecutionResult) {
    echo "✓ Working button routes to handleActivityExecution() - SUCCESS!\n";
}

echo "\n=== CONCLUSION ===\n\n";

if ($executionResult || $confirmationResult) {
    echo "The button logic should work correctly.\n";
    echo "If it's still failing, the issue might be:\n";
    echo "1. Server caching old PHP files\n";
    echo "2. Different method call order in actual server\n";
    echo "3. Another routing issue not covered in this test\n";
} else {
    echo "Found the routing issue! The button is not being handled by either method.\n";
    echo "This confirms why it falls through to handleOtherWorkTypes() and shows the error.\n";
}
?>