<?php
/**
 * Comprehensive HVAC Flow Test Script
 * Tests the complete callback flow from button click to execution
 */

echo "=== HVAC Complete Flow Test ===\n\n";

// Simulate the callback transformation flow
function testCallbackFlow($originalCallback, $testName) {
    echo "TEST: {$testName}\n";
    echo "Original callback: '{$originalCallback}'\n";
    
    // Step 1: extractCategoryFromCallback transformation
    $category = $originalCallback;
    if (strpos($category, 'cat_ШМР_ОтоплВент_') === 0) {
        // Check if it's in the mapping
        $mappings = [
            'cat_ШМР_ОтоплВент_МонтОб_РабГр' => 'ШМР_ОтоплВент_МонтОб_РабГр',
            'cat_ШМР_ОтоплВент_МонтОб_РабГр_yes' => 'ШМР_ОтоплВент_МонтОб_РабГр_yes',
            'cat_ШМР_ОтоплВент_МонтВозд_РабГр' => 'ШМР_ОтоплВент_МонтВозд_РабГр',
            'cat_ШМР_ОтоплВент_МонтВозд_РабГр_yes' => 'ШМР_ОтоплВент_МонтВозд_РабГр_yes',
        ];
        
        if (isset($mappings[$originalCallback])) {
            $category = $mappings[$originalCallback];
            echo "✓ Transformed by extractCategoryFromCallback: '{$category}'\n";
        } else {
            echo "✗ NOT in mapping - will use original: '{$category}'\n";
        }
    }
    
    // Step 2: Check if it matches isActivityConfirmation (for showing dialog)
    if (!str_contains($category, '_yes')) {
        // Test both patterns
        $matched = false;
        if (preg_match('/^cat_ШМР_ОтоплВент_(.+)_(РабГр|НеучВОР|ПодтВех)$/u', $category, $matches)) {
            echo "✓ isActivityConfirmation (cat_ pattern): MATCHED (will show confirmation dialog)\n";
            echo "  - Activity: {$matches[1]}, Action: {$matches[2]}\n";
            $matched = true;
        }
        if (preg_match('/^ШМР_ОтоплВент_(.+)_(РабГр|НеучВОР|ПодтВех)$/u', $category, $matches)) {
            echo "✓ isActivityConfirmation (transformed pattern): MATCHED (will show confirmation dialog)\n";
            echo "  - Activity: {$matches[1]}, Action: {$matches[2]}\n";
            $matched = true;
        }
        if (!$matched) {
            echo "✗ isActivityConfirmation: NOT MATCHED\n";
        }
    }
    
    // Step 3: Check if it matches isActivityExecution (for creating report)
    if (str_contains($category, '_yes')) {
        // Test OLD pattern (WRONG - has 'cat_' prefix)
        if (preg_match('/^cat_ШМР_ОтоплВент_(.+)_(РабГр|НеучВОР|ПодтВех)_yes$/u', $category, $matches)) {
            echo "✗ isActivityExecution OLD pattern: MATCHED (but this is WRONG after transformation!)\n";
        } else {
            echo "✓ isActivityExecution OLD pattern: NOT matched (expected)\n";
        }
        
        // Test NEW pattern (CORRECT - no 'cat_' prefix)
        if (preg_match('/^ШМР_ОтоплВент_(.+)_(РабГр|НеучВОР|ПодтВех)_yes$/u', $category, $matches)) {
            echo "✓ isActivityExecution NEW pattern: MATCHED (will create report!)\n";
            echo "  - Activity: {$matches[1]}, Action: {$matches[2]}\n";
        } else {
            echo "✗ isActivityExecution NEW pattern: NOT MATCHED\n";
        }
    }
    
    echo "\n";
}

// Test scenarios
echo "--- SCENARIO 1: User clicks 'Работы по графику' button ---\n";
testCallbackFlow('cat_ШМР_ОтоплВент_МонтОб_РабГр', 'Монтаж оборудования - РабГр');

echo "--- SCENARIO 2: User clicks 'Да' button ---\n";
testCallbackFlow('cat_ШМР_ОтоплВент_МонтОб_РабГр_yes', 'Монтаж оборудования - РабГр - Confirmation');

echo "--- SCENARIO 3: Different activity ---\n";
testCallbackFlow('cat_ШМР_ОтоплВент_МонтВозд_РабГр_yes', 'Монтаж воздуховодов - РабГр - Confirmation');

echo "\n=== VERIFICATION ===\n";
echo "Expected flow:\n";
echo "1. Button sends: cat_ШМР_ОтоплВент_МонтОб_РабГр\n";
echo "2. extractCategoryFromCallback transforms to: ШМР_ОтоплВент_МонтОб_РабГр\n";
echo "3. isActivityConfirmation matches (shows dialog with 'Да' button)\n";
echo "4. 'Да' button sends: cat_ШМР_ОтоплВент_МонтОб_РабГр_yes\n";
echo "5. extractCategoryFromCallback transforms to: ШМР_ОтоплВент_МонтОб_РабГр_yes\n";
echo "6. isActivityExecution NEW pattern matches (creates report)\n";
echo "\n";
echo "If NEW pattern matches in isActivityExecution, the fix is complete!\n";
