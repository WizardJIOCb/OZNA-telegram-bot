<?php

// Debug script to check automation activity types validation

class TabelHandlerDebug {
    private array $activityTypes = [
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет',
        // Mobile brigade activity types
        'Мобильная бригада',
        // Electrical equipment activity types
        'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ПодтВех_ЭлектрОб',
        'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 'МонтОб_ЭлектрОб', 'Прочее_ЭлектрОб',
        // Electrical equipment main category
        'Электрооборудование',
        // Automation equipment activity types
        'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
        'ИзмСх_Автом', 'МонтКб_Автом', 'РаскКб_Автом',
        'МонтАСУ_Автом', 'УдалПО_Автом', 'Прочее_Автом',
        // Fire alarm equipment activity types
        'РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн',
        'ИзмСх_ПожСигн', 'МонтКб_ПожСигн', 'РаскКб_ПожСигн',
        'МонтОб_ПожСигн', 'Прочее_ПожСигн',
        // Security alarm equipment activity types
        'РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн',
        'Прочее_ОхрСигн',
        'ИзмСх_ОхрСигн', 'МонтКб_ОхрСигн', 'РаскКб_ОхрСигн', 'МонтОб_ОхрСигн',
        // Heating and ventilation equipment activity types
        'РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл',
        'ИзмСх_ВентОтопл', 'МонтКб_ВентОтопл', 'РаскКб_ВентОтопл',
        'МонтАСУ_ВентОтопл', 'УдалПО_ВентОтопл', 'Прочее_ВентОтопл',
        // Fix for commissioning works issue - added missing activity type
        'ПусконаладочныеРаботы',
        // Missing commissioning activity types
        'КомплексноеОпробование',
        'РежимнаяНаладка',
        'ПодготовкаКПоездке',
        'СдачаАвансовогоОтчета',
        'ОхраннаяСигнализация',
        'ОтоплениеИВентиляция',
        'ТехнологическоеОборудование',
        'Метрология'
    ];

    public function debugAutomationActivities() {
        echo "=== DEBUGGING AUTOMATION ACTIVITY TYPES ===\n";
        
        $automationActivities = [
            'ИзмСх_Автом',
            'МонтКб_Автом', 
            'РаскКб_Автом',
            'МонтАСУ_Автом',
            'УдалПО_Автом',
            'Прочее_Автом'
        ];
        
        foreach ($automationActivities as $activity) {
            $isValidated = in_array($activity, $this->activityTypes);
            echo "Activity: {$activity} - Valid: " . ($isValidated ? 'YES' : 'NO') . "\n";
        }
        
        echo "\n=== TESTING CALLBACK EXTRACTION ===\n";
        
        $callbacks = [
            'category_Табель_ИзмСх_Автоматизация',
            'category_Табель_МонтКб_Автоматизация',
            'category_Табель_РаскКб_Автоматизация',
            'category_Табель_МонтАСУ_Автоматизация',
            'category_Табель_УдалПО_Автоматизация',
            'category_Табель_Прочее_Автоматизация'
        ];
        
        foreach ($callbacks as $callback) {
            $extracted = $this->extractCategoryFromCallback($callback);
            echo "Callback: {$callback} -> Extracted: {$extracted}\n";
        }
        
        echo "\n=== TESTING AUTOMATION WORK MAPPING ===\n";
        
        $automationWorksMap = [
            'ИзмСх' => 'ИзмСх_Автом',
            'МонтКб' => 'МонтКб_Автом',
            'РаскКб' => 'РаскКб_Автом',
            'МонтАСУ' => 'МонтАСУ_Автом',
            'УдалПО' => 'УдалПО_Автом',
            'Прочее' => 'Прочее_Автом'
        ];
        
        foreach ($automationWorksMap as $workType => $fullActivity) {
            $isValidated = in_array($fullActivity, $this->activityTypes);
            echo "WorkType: {$workType} -> Activity: {$fullActivity} - Valid: " . ($isValidated ? 'YES' : 'NO') . "\n";
        }
    }
    
    private function extractCategoryFromCallback(string $callbackData): string
    {
        // Handle original category format
        if (strpos($callbackData, 'category_') === 0) {
            return substr($callbackData, strlen('category_'));
        }
        
        return '';
    }
}

$debug = new TabelHandlerDebug();
$debug->debugAutomationActivities();

?>