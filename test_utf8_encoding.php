<?php
/**
 * Test UTF-8 encoding of category names
 */

// Test category from button click
$testCategory = 'Табель_ШМР_ОтоплВент_МонтОб';

// Exclusion list from code
$shmrMenuCategories = [
    'Табель_ШМР_Электрооборудование',
    'Табель_ШМР_ПожарнаяСигнализация',
    'Табель_ШМР_ОхраннаяСигнализация',
    'Табель_ШМР_Автоматизация',
    'Табель_ШМР_ОтоплениеИВентиляция',
    'Табель_ШМР_ОтоплВент_МонтОб',
    'Табель_ШМР_ОтоплВент_МонтВозд',
    'Табель_ШМР_ОтоплВент_МонтКонд',
    'Табель_ШМР_ОтоплВент_МонтИТП',
    'Табель_ШМР_ОтоплВент_ПневмГидр',
    'Табель_ШМР_ОтоплВент_МонтКНС',
    'Табель_ШМР_ОтоплВент_ПроклКб',
    'Табель_ШМР_ОтоплВент_ПодклКб',
    'Табель_ШМР_ОтоплВент_МаркОб'
];

echo "Test category: {$testCategory}\n";
echo "Test category hex: " . bin2hex($testCategory) . "\n\n";

echo "Checking if in exclusion list:\n";
if (in_array($testCategory, $shmrMenuCategories)) {
    echo "✓ FOUND in exclusion list\n";
    echo "→ isActivityConfirmation should return FALSE\n";
    echo "→ Should route to handleOtherWorkTypes\n";
} else {
    echo "✗ NOT FOUND in exclusion list\n";
    echo "→ isActivityConfirmation might return TRUE\n";
    echo "→ Would show confirmation instead of menu\n";
}

echo "\n\nExclusion list items:\n";
foreach ($shmrMenuCategories as $item) {
    echo "  - {$item} (hex: " . bin2hex($item) . ")\n";
    if ($item === $testCategory) {
        echo "    ★ MATCHES test category!\n";
    }
}
