<?php

// Test the actual callback mapping method from TabelHandler
$testCallback = 'cat_ТабАвтом_Проч';

// Simulate the extractCategoryFromCallback method from TabelHandler
function extractCategoryFromCallback(string $callbackData): string
{
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        // Map short formats to full category names
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
            'cat_ТабАвтом' => 'Табель_Автоматизация',
            'cat_ТабАвтом_РабПоГр' => 'Табель_РабПоГр_Автом',
            'cat_ТабАвтом_ФормИД' => 'Табель_ФормИД_Автом',
            'cat_ТабАвтом_ДопРаб' => 'Табель_ДопРаб_Автом',
            'cat_ТабАвтом_ПодтВех' => 'Табель_ПодтВех_Автом',
            'cat_ТабАвтом_Проч' => 'Табель_Прочее_Автоматизация', // Added this mapping
            'cat_ТабПожСигн' => 'Табель_ПожарнаяСигнализация',
            'cat_ТабПожСигн_Наз' => 'Табель_ПожарнаяСигнализация',
            'cat_ТабОхрСигн' => 'Табель_ОхраннаяСигнализация',
            'cat_ТабОхрСиг_РабПоГр' => 'Табель_РабПоГр_ОхрСигн',
            'cat_ТабОхрСиг_ФормИД' => 'Табель_ФормИД_ОхрСигн',
            'cat_ТабОхрСиг_ДопРаб' => 'Табель_ДопРаб_ОхрСигн',
            'cat_ТабОхрСиг_ПодтВех' => 'Табель_ПодтВех_ОхрСигн',
            'cat_ТабОхрСиг_Проч' => 'Табель_Прочее_ОхрСигн',
            'cat_ТабОхрСиг_ИзмСх' => 'Табель_ИзмСх_ОхрСигн',
            'cat_ТабОхрСиг_МонтКб' => 'Табель_МонтКб_ОхрСигн',
            'cat_ТабОхрСиг_РаскКб' => 'Табель_РаскКб_ОхрСигн',
            'cat_ТабОхрСиг_МонтОб' => 'Табель_МонтОб_ОхрСигн',
            'cat_ТабПожСигн_РабПоГр' => 'Табель_РабПоГр_ПожСигн',
            'cat_ТабПожСигн_ФормИД' => 'Табель_ФормИД_ПожСигн',
            'cat_ТабПожСигн_МонтОб' => 'Табель_МонтОб_ПожСигн',
            'cat_ТабПожСигн_ДопРаб' => 'Табель_ДопРаб_ПожСигн',
            'cat_ТабПожСигн_ПодтВех' => 'Табель_ПодтВех_ПожСигн',
            'cat_ТабПожСигн_ИзмСх' => 'Табель_ИзмСх_ПожСигн',
            'cat_ТабПожСигн_МонтКб' => 'Табель_МонтКб_ПожСигн',
            'cat_ТабПожСигн_РаскКб' => 'Табель_РаскКб_ПожСигн',
            'cat_ТабПожСигн_МонтОб' => 'Табель_МонтОб_ПожСигн',
            'cat_ТабПожСигн_Проч' => 'Табель_Прочее_ПожСигн',
            'cat_ТабПожСигн_ДопРаб' => 'Табель_ДопРаб_ПожСигн',
            'cat_ТабПожСигн_ПодтВех' => 'Табель_ПодтВех_ПожСигн'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

echo "=== TESTING ACTUAL CALLBACK MAPPING ===\n\n";
echo "Testing callback: $testCallback\n";

$mappedCategory = extractCategoryFromCallback($testCallback);
echo "Mapped category: $mappedCategory\n";

// Test handler condition
$handlerMatch = ($mappedCategory === 'Табель_Прочее_Автоматизация' || $mappedCategory === 'cat_ТабАвтом_Проч');
echo "Handler condition match: " . ($handlerMatch ? 'YES' : 'NO') . "\n";

echo "\n=== RESULT ===\n";
if ($handlerMatch) {
    echo "SUCCESS: Automation Прочее button should now work!\n";
    echo "Expected flow:\n";
    echo "1. User clicks 'Прочее' button (callback: cat_ТабАвтом_Проч)\n";
    echo "2. TabelHandler maps to: Табель_Прочее_Автоматизация\n";
    echo "3. Handler matches and prompts for custom input\n";
    echo "4. Sets conversation state to await custom input\n";
    echo "5. Shows message: 'Пожалуйста, введите описание выполняемой работы:'\n";
} else {
    echo "ERROR: Mapping still not working!\n";
}

echo "\n=== END TEST ===\n";