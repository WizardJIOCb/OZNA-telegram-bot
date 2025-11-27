<?php
/**
 * Direct test of CallbackHandlerFactory class
 */

echo "=== Direct Test of CallbackHandlerFactory Class ===\n";

// Include the autoloader
require_once 'vendor/autoload.php';
require_once 'custom_autoload.php';

// Test the callback data
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "Input callback data: {$callbackData}\n";

// Create an instance of CallbackHandlerFactory
try {
    // Use reflection to test the extractCategory method directly
    $factoryClass = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory');
    $factory = $factoryClass->newInstance();
    
    $extractMethod = $factoryClass->getMethod('extractCategory');
    $extractMethod->setAccessible(true);
    
    $category = $extractMethod->invokeArgs($factory, [$callbackData]);
    
    echo "Extracted category: '{$category}'\n";
    
    if ($category === 'Табель_РаботыПоГрафику_Электрооборудование') {
        echo "✅ SUCCESS: Category correctly extracted\n";
    } else {
        echo "❌ FAILURE: Category not correctly extracted\n";
    }
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "TRACE: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "TRACE: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
?>