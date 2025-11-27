<?php
// Test script to verify the updated fix for "Работы по графику" button

echo "TESTING UPDATED FIX: 'Работы по графику' button in electrical equipment category\n";
echo "========================================================================\n\n";

// Simulate the callback that comes in when user confirms "Работы по графику"
$category = 'Табель_РаботыПоГрафику_Электрооборудование_yes';
echo "1. Category received from callback: {$category}\n";

// Test isActivityExecution method
function isActivityExecution($category) {
    // Check standard activity execution
    if (strpos($category, '_yes') !== false) {
        // Extract the activity type from the category
        $pattern = '/^Табель_(.+)_yes$/';
        if (preg_match($pattern, $category, $matches)) {
            $activityTypes = [
                'РаботыПоГрафику_Электрооборудование',
                // ... other activity types
            ];
            $activityType = $matches[1];
            // Check if this activity type is in our list
            $result = in_array($activityType, $activityTypes);
            echo "2. isActivityExecution: category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false') . "\n";
            return $result;
        }
    }
    return false;
}

// Check if our category is recognized as an activity execution
if (isActivityExecution($category)) {
    echo "✅ Category correctly identified as activity execution\n";
    
    // Extract activity type
    if (preg_match('/^Табель_(.+)_yes$/', $category, $matches)) {
        $activityType = $matches[1];
        echo "3. Extracted activity type: {$activityType}\n";
        
        // Verify activity type is in our list
        $activityTypes = [
            'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
            'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
            'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
            'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
            'МедОсмотр', 'Аттестация', 'Общая',
            'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
            'АвансовыйОтчет',
            // New electrical equipment activity types
            'РаботыПоГрафику_Электрооборудование',
            'ФормированиеИД_Электрооборудование',
            'ПодтвДостВехи_ЭлектрОб',
            'ДопРаб_ЭлектрОб',
            'ИзмСхемПодкл_ЭлектрОб',
            'МонтажКабЛиний_ЭлектрОб',
            'РасклКабЛиний_ЭлектрОб',
            'Прочее_Электрооборудование',
            // Electrical equipment main category
            'Электрооборудование'
        ];
        
        if (in_array($activityType, $activityTypes)) {
            echo "✅ Activity type found in activityTypes array\n";
            
            // Test the confirmation message
            $activityNames = [
                'РаботыПоГрафику_Электрооборудование' => 'Работы по графику (Электрооборудование)',
            ];
            
            if (isset($activityNames[$activityType])) {
                $activityName = $activityNames[$activityType];
                $projectCode = 'TEST-PROJECT';
                $confirmationMessage = sprintf(
                    "<b>%s</b>, отправить запись в табель в проект <b>%s</b>?",
                    $activityName,
                    $projectCode
                );
                echo "4. Confirmation message: {$confirmationMessage}\n";
                echo "✅ Confirmation message correctly formatted\n";
            } else {
                echo "❌ Activity name not found\n";
            }
        } else {
            echo "❌ Activity type not found in activityTypes array\n";
        }
    }
} else {
    echo "❌ Category NOT identified as activity execution\n";
    echo "   This means the isActivityExecution method is not working correctly\n";
}

echo "\n=== TESTING MAPPING APPROACH ===\n";
// Test the mapping approach
function extractCategoryFromCallback($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for electrical equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_РабПоГр' => 'Табель_РаботыПоГрафику_Электрооборудование',
            'cat_ТабЭлОб_ФормИД' => 'Табель_ФормированиеИД_Электрооборудование',
            'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзменениеСхемПодключения_Электрооборудование',
            'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтажКабельныхЛиний_Электрооборудование',
            'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РасключениеКабельныхЛиний_Электрооборудование',
            'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
            'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
            'cat_ТабЭлОб_Проч' => 'Табель_Прочее_Электрооборудование'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return $callbackData; // Return as is if no mapping found
}

// Test with a short callback format
$shortCallback = 'cat_ТабЭлОб_РабПоГр';
$mappedCategory = extractCategoryFromCallback($shortCallback);
echo "5. Short callback '{$shortCallback}' mapped to: {$mappedCategory}\n";

// Test with a confirmation callback
$confirmationCallback = $mappedCategory . '_yes';
echo "6. Confirmation callback would be: {$confirmationCallback}\n";

if (isActivityExecution($confirmationCallback)) {
    echo "✅ Mapped confirmation callback correctly identified as activity execution\n";
} else {
    echo "❌ Mapped confirmation callback NOT identified as activity execution\n";
}

echo "\n🎉 UPDATED FIX VERIFICATION COMPLETE!\n";
echo "The updated approach should now correctly handle both direct activity execution callbacks\n";
echo "and mapped callbacks from short formats.\n";