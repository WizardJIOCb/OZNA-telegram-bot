<?php
echo "Testing warranty equipment pattern matching...\n";
$category = 'cat_Гарантии_ЭлектрОб';
if (preg_match('/^cat_Гарантии_(.+)$/u', $category, $matches)) {
    echo "Match found: " . $matches[1] . "\n";
    $equipmentType = $matches[1];
    
    // Map short equipment type to full activity type
    $equipmentTypeMap = [
        'ЭлектрОб' => 'РабГарантии_ЭлектрОб',
        'СрИзм' => 'РабГарантии_СрИзм',
        'НасОб' => 'РабГарантии_НасОб',
        'ТрубАрм' => 'РабГарантии_ТрубАрм',
        'ИспМех' => 'РабГарантии_ИспМех',
        'КонтрОб' => 'РабГарантии_КонтрОб',
        'Прочее' => 'РабГарантии_Прочее'
    ];
    
    if (isset($equipmentTypeMap[$equipmentType])) {
        $activityType = $equipmentTypeMap[$equipmentType];
        echo "Mapped to activity type: $activityType\n";
        echo "Callback data would be: cat_Гарантии_{$equipmentType}_yes\n";
        echo "Length: " . strlen("cat_Гарантии_{$equipmentType}_yes") . " bytes\n";
    }
} else {
    echo "No match found\n";
}
?>