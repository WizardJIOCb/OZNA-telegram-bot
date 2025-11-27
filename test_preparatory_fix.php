<?php

// Test the fix for corrupted "Подготовительные работы" callback
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';

// Create factory instance
$factory = new CallbackHandlerFactory();

// Test the exact corrupted callback from the logs
$corruptedCallback = 'cat_РЁРњР _РџРѕРґРіР Р°Р±';
echo "Original corrupted callback: '$corruptedCallback'\n";
echo "Hex: " . bin2hex($corruptedCallback) . "\n\n";

// Test if it would pass the early exit check now
echo "Early exit check (cat_Таб): " . (strpos($corruptedCallback, 'cat_Таб') === 0 ? 'PASS' : 'FAIL') . "\n";
echo "Early exit check (cat_РЁРњР ): " . (strpos($corruptedCallback, 'cat_РЁРњР ') === 0 ? 'PASS' : 'FAIL') . "\n\n";

// Test the fix via reflection (to access private method)
$reflection = new ReflectionClass($factory);
$fixMethod = $reflection->getMethod('fixKnownCyrillicPatterns');
$fixMethod->setAccessible(true);

$fixedCallback = $fixMethod->invokeArgs($factory, [$corruptedCallback]);
echo "Fixed callback: '$fixedCallback'\n";
echo "Hex: " . bin2hex($fixedCallback) . "\n\n";

// Test extractCategory
$extractMethod = $reflection->getMethod('extractCategory');
$extractMethod->setAccessible(true);

$category = $extractMethod->invokeArgs($factory, [$fixedCallback]);
echo "Extracted category: '$category'\n";

// Show what the expected result should be
echo "\nExpected result: 'ШМР_ПодгРаб'\n";
echo "Success: " . ($category === 'ШМР_ПодгРаб' ? 'YES' : 'NO') . "\n";