<?php

// Test script to verify the date selection fix

// Simulate the ProjectSelector class with only the methods we need for testing
class ProjectSelector {
    private function fixKnownCyrillicPatterns($str) {
        // Replace the known corrupted patterns
        $replacements = [
            'РЁРёС„СЂРџСЂРѕРµРєС‚Р°' => 'ШифрПроекта',
            'РћР834' => 'ОИ834',
            'РћР651' => 'ОИ651',
            'РћР696' => 'ОИ696',
            'РћР795' => 'ОИ795',
            'РћР810' => 'ОИ810',
            'РћР768' => 'ОИ768',
            'РћР837' => 'ОИ837',
            'РћР562' => 'ОИ562',
            'РћР788' => 'ОИ788',
            'РћР860' => 'ОИ860',
            'РћР862' => 'ОИ862',
            'Р‘РљРќРЎ115' => 'БКНС115',
            'Р‘РљРќРЎ120' => 'БКНС120',
            'Р”Р°С‚Р°' => 'Дата',
            'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
            'Р’С‡РµСЂР°' => 'Вчера'
        ];
        
        foreach ($replacements as $corrupted => $fixed) {
            $str = str_replace($corrupted, $fixed, $str);
        }
        
        return $str;
    }
    
    public function extractDate(string $category): ?string {
        // Fix encoding issues with category
        $category = $this->fixKnownCyrillicPatterns($category);
        
        if (str_contains($category, '_Сегодня')) {
            return date("d.m.Y");
        }
        
        if (str_contains($category, '_Вчера')) {
            return date("d.m.Y", time() - 86400);
        }
        
        if (str_contains($category, '_Позавчера')) {
            return date("d.m.Y", time() - 172800);
        }
        
        return null;
    }

    public function isDateSelection(string $category): bool {
        // Fix encoding issues with category
        $category = $this->fixKnownCyrillicPatterns($category);
        
        $result = str_contains($category, '_Сегодня') || 
                  str_contains($category, '_Вчера') || 
                  str_contains($category, '_Позавчера') || 
                  str_contains($category, '_ДругаяДата');
        echo "ProjectSelector isDateSelection called with category: {$category}, result: " . ($result ? 'true' : 'false') . "\n";
        return $result;
    }
}

echo "Testing date selection fix...\n\n";

// Test the ProjectSelector date selection detection
$projectSelector = new ProjectSelector();

// Test cases
$testCases = [
    'category_Табель_Сегодня',
    'category_Табель_Вчера',
    'category_Табель_Позавчера',
    'category_Табель_ДругаяДата',
    'category_Табель_Мобильная_Бригада_Сегодня',
    'category_Табель_Пусконаладка_Вчера',
    'category_Табель_Шеф_монтаж_Сегодня',
    'category_Табель_Текущая_Вчера',
    'category_Табель_InvalidCategory'
];

echo "Testing ProjectSelector::isDateSelection method:\n";
foreach ($testCases as $testCase) {
    $result = $projectSelector->isDateSelection($testCase);
    echo "  {$testCase}: " . ($result ? 'MATCH' : 'NO MATCH') . "\n";
}

echo "\nTesting ProjectSelector::extractDate method:\n";
$dateTestCases = [
    'category_Табель_Сегодня',
    'category_Табель_Вчера',
    'category_Табель_Позавчера',
    'category_Табель_InvalidCategory'
];

foreach ($dateTestCases as $testCase) {
    $date = $projectSelector->extractDate($testCase);
    echo "  {$testCase}: " . ($date ?: 'NULL') . "\n";
}

echo "\nTest completed.\n";