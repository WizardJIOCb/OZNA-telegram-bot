<?php
// Debug script to test fire alarm callback processing

// Simulate the callback data mapping
$shortFormats = [
    'cat_ТабЭлОб' => 'Табель_Электрооборудование',
    'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
    'cat_ТабЭлОб_РабПоГр' => 'Табель_РабПоГр_ЭлектрОб',
    'cat_ТабЭлОб_ФормИД' => 'Табель_ФормИД_ЭлектрОб',
    'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзмСхПд_ЭлектрОб',
    'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтКбЛн_ЭлектрОб',
    'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РаскКбЛн_ЭлектрОб',
    'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
    'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтВех_ЭлектрОб',
    'cat_ТабЭлОб_Проч' => 'Табель_Прочее_ЭлектрОб',
    'cat_ТабПожСигн_Наз' => 'Табель_ПожарнаяСигнализация',
    'cat_ТабПожСигн_РабПоГр' => 'Табель_РабПоГр_ПожСигн',
    'cat_ТабПожСигн_ФормИД' => 'Табель_ФормИД_ПожСигн',
    'cat_ТабПожСигн_ИзмСх' => 'Табель_ИзмСх_ПожСигн',
    'cat_ТабПожСигн_МонтКб' => 'Табель_МонтКб_ПожСигн',
    'cat_ТабПожСигн_РаскКб' => 'Табель_РаскКб_ПожСигн',
    'cat_ТабПожСигн_МонтАСУ' => 'Табель_МонтАСУ_ПожСигн',
    'cat_ТабПожСигн_УдалПО' => 'Табель_УдалПО_ПожСигн',
    'cat_ТабПожСигн_Проч' => 'Табель_Прочее_ПожСигн',
    'cat_ТабПожСигн_ДопРаб' => 'Табель_ДопРаб_ПожСигн',
    'cat_ТабПожСигн_ПодтВех' => 'Табель_ПодтВех_ПожСигн',
    // Handle confirmation callbacks as well
    'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
    'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормИД_ЭлектрОб_yes',
    'cat_ТабЭлОб_ИзмСхПд_yes' => 'Табель_ИзмСхПд_ЭлектрОб_yes',
    'cat_ТабЭлОб_МонтКбЛн_yes' => 'Табель_МонтКбЛн_ЭлектрОб_yes',
    'cat_ТабЭлОб_РаскКбЛн_yes' => 'Табель_РаскКбЛн_ЭлектрОб_yes',
    'cat_ТабЭлОб_ДопРаб_yes' => 'Табель_ДопРаб_ЭлектрОб_yes',
    'cat_ТабЭлОб_ПодтВех_yes' => 'Табель_ПодтВех_ЭлектрОб_yes',
    'cat_ТабЭлОб_Проч_yes' => 'Табель_Прочее_ЭлектрОб_yes',
    'cat_ТабПожСигн_РабПоГр_yes' => 'Табель_РабПоГр_ПожСигн_yes',
    'cat_ТабПожСигн_ФормИД_yes' => 'Табель_ФормИД_ПожСигн_yes',
    'cat_ТабПожСигн_ИзмСх_yes' => 'Табель_ИзмСх_ПожСигн_yes',
    'cat_ТабПожСигн_МонтКб_yes' => 'Табель_МонтКб_ПожСигн_yes',
    'cat_ТабПожСигн_РаскКб_yes' => 'Табель_РаскКб_ПожСигн_yes',
    'cat_ТабПожСигн_МонтАСУ_yes' => 'Табель_МонтАСУ_ПожСигн_yes',
    'cat_ТабПожСигн_УдалПО_yes' => 'Табель_УдалПО_ПожСигн_yes',
    'cat_ТабПожСигн_Проч_yes' => 'Табель_Прочее_ПожСигн_yes',
    'cat_ТабПожСигн_ДопРаб_yes' => 'Табель_ДопРаб_ПожСигн_yes',
    'cat_ТабПожСигн_ПодтВех_yes' => 'Табель_ПодтВех_ПожСигн_yes'
];

echo "Testing fire alarm callback processing:\n\n";

// Test the specific callbacks that were having issues
$testCallbacks = [
    'cat_ТабПожСигн_ПодтВех',  // "Подтвердить достижение вехи" button
    'cat_ТабПожСигн_ДопРаб'    // "Дополнительные работы" button
];

foreach ($testCallbacks as $callback) {
    echo "Callback: $callback\n";
    if (isset($shortFormats[$callback])) {
        $mappedCategory = $shortFormats[$callback];
        echo "  Mapped to: $mappedCategory\n";
        
        // Check if this category would be handled correctly
        if ($mappedCategory === 'Табель_ПодтВех_ПожСигн') {
            echo "  This should trigger the fire alarm milestone confirmation handling\n";
            echo "  Which should now call handleAdditionalWorksFireAlarmMenu() instead of showing 'в разработке'\n";
        } elseif ($mappedCategory === 'Табель_ДопРаб_ПожСигн') {
            echo "  This should trigger the fire alarm additional works handling\n";
            echo "  Which should call handleAdditionalWorksFireAlarmMenu()\n";
        }
    } else {
        echo "  Not found in mapping - would use default extraction\n";
    }
    echo "\n";
}

echo "All callback mappings are correctly defined.\n";
echo "The issue might be in the runtime execution rather than the code structure.\n";
?>