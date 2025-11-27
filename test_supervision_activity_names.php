<?php
/**
 * Simple test for supervision installation works report format
 * Verify activity names in TabelEntry.php
 */

echo "=== TESTING SUPERVISION INSTALLATION WORKS ACTIVITY NAMES ===\n\n";

// Read TabelEntry.php file
$fileContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

if ($fileContent === false) {
    echo "❌ ERROR: Could not read TabelEntry.php file\n";
    exit(1);
}

echo "✅ TabelEntry.php file read successfully\n\n";

// Test supervision installation works activities
$supervisionActivities = [
    'ШМ_РабПоГрафику' => 'Работы по графику',
    'ШМ_ПодготовкаКПоездке' => 'Подготовка к служебной поездке', 
    'ШМ_Дорога' => 'Дорога',
    'ШМ_Инструктаж' => 'Инструктаж',
    'ШМ_СдачаАвансовогоОтчета' => 'Сдача авансового отчета'
];

echo "🔍 CHECKING ACTIVITY NAMES IN TABELENTRY.PHP:\n";
echo "=" . str_repeat("=", 60) . "\n\n";

$allCorrect = true;

foreach ($supervisionActivities as $activityType => $expectedName) {
    echo "🔧 Activity: {$activityType}\n";
    echo "📝 Expected: {$expectedName}\n";
    
    // Check if the activity has the correct name (without Шеф-монтаж suffix)
    $pattern = '/\'' . preg_quote($activityType, '/') . '\'\s*=>\s*\'([^\']+)\'/';
    
    if (preg_match_all($pattern, $fileContent, $matches)) {
        $foundNames = $matches[1];
        echo "🔍 Found " . count($foundNames) . " occurrence(s):\n";
        
        foreach ($foundNames as $index => $foundName) {
            echo "  " . ($index + 1) . ". '{$foundName}'\n";
            
            if (strpos($foundName, '(Шеф-монтаж)') !== false) {
                echo "  ❌ ERROR: Still contains '(Шеф-монтаж)' suffix!\n";
                $allCorrect = false;
            } elseif ($foundName === $expectedName) {
                echo "  ✅ SUCCESS: Correct format without suffix\n";
            } else {
                echo "  ⚠️  WARNING: Different format: '{$foundName}'\n";
            }
        }
    } else {
        echo "❌ ERROR: Activity type '{$activityType}' not found in file!\n";
        $allCorrect = false;
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

if ($allCorrect) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "✅ All supervision installation works activities follow correct format\n";
    echo "✅ Successfully removed '(Шеф-монтаж)' suffix from all reports\n";
    echo "✅ Reports now match standard equipment activity format\n";
} else {
    echo "❌ SOME ISSUES FOUND!\n";
    echo "Please check the errors above\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>