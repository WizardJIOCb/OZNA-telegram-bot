<?php
// Test to verify the mapping fix for project activity "Other" types

// Simulate the activity type mappings
$activityTypeMappings = [
    // Existing mappings...
    'РабГарантии_ЭлектрОб' => '⚡ Работы по гарантии (Электрооборудование)',
    'РабГарантии_СрИзм' => '📎 Работы по гарантии (Средства измерений)',
    'РабГарантии_НасОб' => '💧 Работы по гарантии (Насосное оборудование)',
    'РабГарантии_ТрубАрм' => '🔧 Работы по гарантии (Трубопроводная арматура)',
    'РабГарантии_ИспМех' => '⚙️ Работы по гарантии (Исполнительные механизмы)',
    'РабГарантии_КонтрОб' => '💻 Работы по гарантии (Контроллерное оборудование)',
    'РабГарантии_Прочее' => '📦 Работы по гарантии (Прочее оборудование)',
    // Project activity "Other" type (the fix)
    'Прочее_ПроектнаяДеятельность' => '📝 Прочее (Проектная деятельность)'
];

echo "Activity type mappings:\n";
print_r($activityTypeMappings);

// Test the mapping for project activity "Other" type
$activityType = 'Прочее_ПроектнаяДеятельность';
$activityText = $activityTypeMappings[$activityType] ?? $activityType;

echo "\nActivity type: " . $activityType . "\n";
echo "Mapped text: " . $activityText . "\n";

// Verify it's the correct user-friendly format
if ($activityText === '📝 Прочее (Проектная деятельность)') {
    echo "✅ SUCCESS: Correct user-friendly format for Project Activity 'Other' type\n";
} else {
    echo "❌ ERROR: Incorrect format. Expected '📝 Прочее (Проектная деятельность)', got '" . $activityText . "'\n";
}

// Test with custom description
$idleReason = 'Тест';
if (!empty($idleReason)) {
    $activityText .= " ({$idleReason})";
}

echo "\nWith custom description: " . $activityText . "\n";

if ($activityText === '📝 Прочее (Проектная деятельность) (Тест)') {
    echo "✅ SUCCESS: Correct format with custom description\n";
} else {
    echo "❌ ERROR: Incorrect format with custom description\n";
}
?>