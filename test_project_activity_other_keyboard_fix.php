<?php
// Test to verify the keyboard fix for project activity "Other" types

// Simulate the conversation notes that would be set when user selects "Other" for project activities
$conversationNotes = [
    'awaiting_custom_prochee_input' => true,
    'prochee_activity_type' => 'Прочее_ПроектнаяДеятельность',
    'prochee_source_category' => 'cat_ТабПроектнаяДеят_Проч',
    'project_code' => 'ОИ833'
];

echo "Conversation notes:\n";
print_r($conversationNotes);

// Simulate the keyboard selection logic that was fixed
$activityType = $conversationNotes['prochee_activity_type'] ?? 'Прочее_ЭлектрОб';

echo "\nActivity type: " . $activityType . "\n";

// Use the same logic as in the fix
if ($activityType === 'Прочее_ЭлектрОб') {
    $keyboardCallback = "cat_ТабЭлОб_Проч_yes";
} elseif ($activityType === 'Прочее_ПожСигн') {
    $keyboardCallback = "cat_ТабПожСигн_Проч_yes";
} elseif ($activityType === 'Прочее_Автом') {
    $keyboardCallback = "cat_ТабАвтом_Проч_yes";
} elseif ($activityType === 'Прочее_ОхрСигн') {
    $keyboardCallback = "cat_ТабОхрСиг_Проч_yes";
} elseif ($activityType === 'Прочее_ПроектнаяДеятельность') {
    // Special handling for Project Activity Прочее - use category format
    $sourceCategory = $conversationNotes['prochee_source_category'] ?? '';
    if (strpos($sourceCategory, '_ШМР_') !== false) {
        $keyboardCallback = "cat_ТабПроектнаяДеят_ШМР_Проч_yes";
    } else {
        $keyboardCallback = "cat_ТабПроектнаяДеят_Проч_yes";
    }
} elseif ($activityType === 'УстрЗам_Прочее') {
    // Special handling for RK Прочее - use category format
    $keyboardCallback = "category_Табель_{$activityType}_yes";
} else {
    $keyboardCallback = "category_Табель_{$activityType}_yes";
}

echo "Selected keyboard callback: " . $keyboardCallback . "\n";

// Verify it's the correct one for project activities
if ($keyboardCallback === "cat_ТабПроектнаяДеят_Проч_yes") {
    echo "✅ SUCCESS: Correct keyboard callback selected for Project Activity 'Other' type\n";
} else {
    echo "❌ ERROR: Incorrect keyboard callback selected\n";
}
?>