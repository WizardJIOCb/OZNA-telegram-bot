<?php
echo "=== TEST UPDATED ШМР PROCHEE LOGIC ===\n";

$activityType = 'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр';
$idleReason = 'Тест';
$activityText = '📝 Прочее (Технологическое оборудование, ШМР)';

echo "Before: " . $activityText . "\n";
echo "Activity Type: " . $activityType . "\n";
echo "Idle Reason: " . $idleReason . "\n";
echo "Checking condition...\n";

if ($activityType === 'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр' && !empty($idleReason)) {
    $activityText .= " ({$idleReason})";
    echo "✅ Condition matched - custom text added\n";
} else {
    echo "❌ Condition not matched\n";
    echo "Activity type match: " . ($activityType === 'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр' ? 'YES' : 'NO') . "\n";
    echo "Idle reason not empty: " . (!empty($idleReason) ? 'YES' : 'NO') . "\n";
}

echo "After: " . $activityText . "\n";
echo "Expected: 📝 Прочее (Технологическое оборудование, ШМР) (Тест)\n";
echo "\n=== THIS SHOULD MATCH THE WORKING FORMAT ===\n";
echo "Working format: 📝 Прочее (Электрооборудование, Дополнительные работы) (Тест)\n";
echo "Our format:     📝 Прочее (Технологическое оборудование, ШМР) (Тест)\n";