<?php
/**
 * Debug test for warranty equipment report sending
 * Traces the complete flow to identify where reports fail
 */

echo "🔧 WARRANTY EQUIPMENT REPORT SENDING - DEBUG TRACE\n";
echo "=".str_repeat("=", 70)."\n\n";

// Read the TabelHandler file to check the handleActivityExecution method
$tabelHandlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$handlerContent = file_get_contents($tabelHandlerFile);

echo "1. CHECKING WARRANTY EQUIPMENT CONFIRMATION HANDLING\n";
echo "-".str_repeat("-", 50)."\n";

// Check if warranty equipment confirmation handling exists
if (strpos($handlerContent, "preg_match('/^cat_Гарантии_(.+)_yes\$/u', \$category, \$matches)") !== false) {
    echo "✅ Warranty equipment confirmation regex pattern found\n";
    
    if (strpos($handlerContent, "\$activityType = 'РабГарантии_' . \$equipmentType;") !== false) {
        echo "✅ Warranty activity type mapping found\n";
    } else {
        echo "❌ Missing warranty activity type mapping\n";
    }
} else {
    echo "❌ Missing warranty equipment confirmation regex pattern\n";
}

echo "\n2. CHECKING ACTIVITY TYPE VALIDATION\n";
echo "-".str_repeat("-", 50)."\n";

// Check if warranty activity types are in the activityTypes array
$warrantyTypes = [
    'РабГарантии_ЭлектрОб',
    'РабГарантии_СрИзм', 
    'РабГарантии_НасОб',
    'РабГарантии_ТрубАрм',
    'РабГарантии_ИспМех',
    'РабГарантии_КонтрОб',
    'РабГарантии_Прочее'
];

$missingTypes = [];
foreach ($warrantyTypes as $type) {
    if (strpos($handlerContent, "'$type'") !== false) {
        echo "✅ Found: $type\n";
    } else {
        echo "❌ Missing: $type\n";
        $missingTypes[] = $type;
    }
}

if (!empty($missingTypes)) {
    echo "\n❌ CRITICAL ISSUE: Missing warranty activity types in TabelHandler\n";
    echo "These types need to be added to the \$activityTypes array:\n";
    foreach ($missingTypes as $type) {
        echo "  - '$type'\n";
    }
} else {
    echo "\n✅ All warranty activity types are defined\n";
}

echo "\n3. CHECKING TabelEntry FORMATTING\n";
echo "-".str_repeat("-", 50)."\n";

// Check if TabelEntry has the warranty formatting group
$tabelEntryFile = __DIR__ . '/CustomCommands/Services/TabelEntry.php';
$entryContent = file_get_contents($tabelEntryFile);

if (strpos($entryContent, '$warrantyEquipmentActivities = [') !== false) {
    echo "✅ Warranty equipment activities array found in TabelEntry\n";
    
    if (strpos($entryContent, 'in_array($activityType, $warrantyEquipmentActivities)') !== false) {
        echo "✅ Warranty equipment formatting condition found\n";
    } else {
        echo "❌ Missing warranty equipment formatting condition\n";
    }
} else {
    echo "❌ Missing warranty equipment activities array in TabelEntry\n";
}

echo "\n4. SIMULATING WARRANTY EQUIPMENT REPORT FLOW\n";
echo "-".str_repeat("-", 50)."\n";

// Simulate the complete flow
$testCallbacks = [
    'cat_Гарантии_ЭлектрОб_yes' => 'РабГарантии_ЭлектрОб',
    'cat_Гарантии_СрИзм_yes' => 'РабГарантии_СрИзм',
    'cat_Гарантии_НасОб_yes' => 'РабГарантии_НасОб',
    'cat_Гарантии_Прочее_yes' => 'РабГарантии_Прочее'
];

foreach ($testCallbacks as $callback => $expectedActivityType) {
    echo "Testing: $callback\n";
    
    // Step 1: Check if callback matches warranty confirmation pattern
    if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback, $matches)) {
        $equipmentType = $matches[1];
        $activityType = 'РабГарантии_' . $equipmentType;
        
        echo "  ✅ Pattern matched, equipment type: $equipmentType\n";
        echo "  ✅ Activity type extracted: $activityType\n";
        
        // Step 2: Check if activity type matches expected
        if ($activityType === $expectedActivityType) {
            echo "  ✅ Activity type matches expected\n";
        } else {
            echo "  ❌ Activity type mismatch: expected $expectedActivityType, got $activityType\n";
        }
        
        // Step 3: Check if activity type is valid (in TabelHandler activityTypes)
        if (in_array($activityType, $warrantyTypes)) {
            echo "  ✅ Activity type is in validation array\n";
        } else {
            echo "  ❌ Activity type NOT in validation array\n";
        }
        
        // Step 4: Check if TabelEntry would format it correctly
        if (strpos($entryContent, "'$activityType'") !== false) {
            echo "  ✅ TabelEntry has this activity type\n";
        } else {
            echo "  ❌ TabelEntry missing this activity type\n";
        }
        
    } else {
        echo "  ❌ Pattern did not match\n";
    }
    echo "\n";
}

echo "5. POTENTIAL ISSUES ANALYSIS\n";
echo "-".str_repeat("-", 50)."\n";

// Look for common issues
$potentialIssues = [];

// Check if there's a validation check that might be failing
if (strpos($handlerContent, 'in_array($activityType, $this->activityTypes)') !== false) {
    echo "✅ Activity type validation check exists\n";
} else {
    $potentialIssues[] = "Missing activity type validation check";
}

// Check if there's proper error logging
if (strpos($handlerContent, 'WARRANTY DEBUG') !== false) {
    echo "✅ Warranty debug logging exists\n";
} else {
    $potentialIssues[] = "Missing warranty debug logging";
}

// Check if addEntry method is being called
if (strpos($handlerContent, '$this->tabelEntry->addEntry(') !== false) {
    echo "✅ addEntry method call exists\n";
} else {
    $potentialIssues[] = "Missing addEntry method call";
}

if (!empty($potentialIssues)) {
    echo "\n❌ POTENTIAL ISSUES FOUND:\n";
    foreach ($potentialIssues as $issue) {
        echo "  - $issue\n";
    }
} else {
    echo "\n✅ No obvious issues found in code structure\n";
}

echo "\n6. NEXT STEPS FOR DEBUGGING\n";
echo "=".str_repeat("=", 40)."\n";

echo "To debug the warranty equipment report sending issue:\n\n";

echo "1. Enable debug logging:\n";
echo "   - Add more detailed logging in handleActivityExecution\n";
echo "   - Log every step of the warranty confirmation process\n";
echo "   - Log the addEntry method call and its result\n\n";

echo "2. Check database connectivity:\n";
echo "   - Verify that database saves are successful\n";
echo "   - Check if the message is being created correctly\n";
echo "   - Verify history chat sending works\n\n";

echo "3. Test with actual bot:\n";
echo "   - Select warranty equipment from bot menu\n";
echo "   - Click 'Да' to confirm\n";
echo "   - Check bot logs for error messages\n\n";

echo "4. Verify user data:\n";
echo "   - Ensure user has FIO filled\n";
echo "   - Ensure user has functional direction set\n";
echo "   - Check if user has recent entry restrictions\n\n";

echo "🧪 SUGGESTED FIX:\n";
echo "Add comprehensive debug logging to handleActivityExecution method\n";
echo "to trace exactly where the warranty equipment flow fails.\n";
echo "\n";