<?php
/**
 * Test to isolate if encoding functions are corrupting the Пусконаладка category
 */

echo "=== TESTING ENCODING FUNCTIONS ON ПУСКОНАЛАДКА ===\n\n";

class EncodingTest {
    
    private function ensureUtf8Encoding($text)
    {
        // If mbstring is available, use it for encoding conversion
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        // If mbstring is not available, return as is
        return $text;
    }
    
    private function fixKnownCyrillicPatterns($str)
    {
        // Only test patterns that won't cause syntax errors
        $replacements = [
            // Test if normal Russian text gets corrupted
        ];
        
        foreach ($replacements as $corrupted => $fixed) {
            if (strpos($str, $corrupted) !== false) {
                echo "  🔄 Found corrupted pattern '{$corrupted}', replacing with '{$fixed}'\n";
                $str = str_replace($corrupted, $fixed, $str);
            }
        }
        
        return $str;
    }
    
    public function testEncodingOnCommissioningCategory() {
        $originalCategory = 'Табель_Пусконаладка';
        
        echo "🧪 ORIGINAL: '{$originalCategory}'\n";
        echo "   Hex: " . bin2hex($originalCategory) . "\n";
        echo "   UTF-8 valid: " . (mb_check_encoding($originalCategory, 'UTF-8') ? 'YES' : 'NO') . "\n";
        echo "   Length: " . strlen($originalCategory) . " bytes\n\n";
        
        // Test ensureUtf8Encoding
        echo "🔧 STEP 1: ensureUtf8Encoding\n";
        $afterEncoding = $this->ensureUtf8Encoding($originalCategory);
        echo "   Result: '{$afterEncoding}'\n";
        echo "   Hex: " . bin2hex($afterEncoding) . "\n";
        echo "   Changed: " . ($originalCategory === $afterEncoding ? 'NO' : 'YES') . "\n";
        echo "   UTF-8 valid: " . (mb_check_encoding($afterEncoding, 'UTF-8') ? 'YES' : 'NO') . "\n\n";
        
        // Test fixKnownCyrillicPatterns
        echo "🔧 STEP 2: fixKnownCyrillicPatterns\n";
        $afterPatternFix = $this->fixKnownCyrillicPatterns($afterEncoding);
        echo "   Result: '{$afterPatternFix}'\n";
        echo "   Hex: " . bin2hex($afterPatternFix) . "\n";
        echo "   Changed: " . ($afterEncoding === $afterPatternFix ? 'NO' : 'YES') . "\n";
        echo "   UTF-8 valid: " . (mb_check_encoding($afterPatternFix, 'UTF-8') ? 'YES' : 'NO') . "\n\n";
        
        // Final comparison
        echo "📊 FINAL RESULT:\n";
        if ($originalCategory === $afterPatternFix) {
            echo "   ✅ GOOD: Category unchanged by encoding functions\n";
        } else {
            echo "   ❌ BAD: Category was corrupted by encoding functions!\n";
            echo "      Original: '{$originalCategory}'\n";
            echo "      Final:    '{$afterPatternFix}'\n";
        }
        
        return $afterPatternFix;
    }
}

$test = new EncodingTest();
$finalCategory = $test->testEncodingOnCommissioningCategory();

echo "\n=== SIMULATION: TabelHandler Logic ===\n";
echo "Final category after encoding: '{$finalCategory}'\n";
echo "Condition check: if (\$category === 'Табель_Пусконаладка')\n";
if ($finalCategory === 'Табель_Пусконаладка') {
    echo "✅ MATCH: Condition would be TRUE - commissioning handler would be called\n";
} else {
    echo "❌ NO MATCH: Condition would be FALSE - commissioning handler would NOT be called\n";
    echo "   This is why the button doesn't work!\n";
}

echo "\n=== RECOMMENDATION ===\n";
if ($finalCategory !== 'Табель_Пусконаладка') {
    echo "🔧 The encoding functions are corrupting the category!\n";
    echo "   Solution: Add a special case to preserve 'Табель_Пусконаладка' unchanged\n";
} else {
    echo "ℹ️  The encoding functions are not the issue.\n";
    echo "   Look for other causes (callback not reaching TabelHandler, etc.)\n";
}
?>