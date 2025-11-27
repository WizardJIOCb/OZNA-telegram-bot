<?php

// Test script to verify the activity mappings

// Include the TabelEntry file to access the activityTypeMappings
include __DIR__ . '/CustomCommands/Services/TabelEntry.php';

// Test the activity mappings directly
$reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry');
$property = $reflection->getProperty('activityTypeMappings');
$property->setAccessible(true);

// Create a dummy instance to access the property
$instance = $reflection->newInstanceWithoutConstructor();
$activityTypeMappings = $property->getValue($instance);

echo "Testing activity mappings:\n\n";

// Test mobile brigade activities
echo "Mobile brigade Подготовка к служебной поездке:\n";
echo "  Key: МобильнаяБригада_ПодготовкаКПоездке\n";
echo "  Value: " . ($activityTypeMappings['МобильнаяБригада_ПодготовкаКПоездке'] ?? 'NOT FOUND') . "\n\n";

echo "Mobile brigade Сдача авансового отчета:\n";
echo "  Key: МобильнаяБригада_СдачаАвансовогоОтчета\n";
echo "  Value: " . ($activityTypeMappings['МобильнаяБригада_СдачаАвансовогоОтчета'] ?? 'NOT FOUND') . "\n\n";

// Test commissioning activities for comparison
echo "Commissioning Подготовка к служебной поездке:\n";
echo "  Key: ПодготовкаКПоездке\n";
echo "  Value: " . ($activityTypeMappings['ПодготовкаКПоездке'] ?? 'NOT FOUND') . "\n\n";

echo "Commissioning Сдача авансового отчета:\n";
echo "  Key: СдачаАвансовогоОтчета\n";
echo "  Value: " . ($activityTypeMappings['СдачаАвансовогоОтчета'] ?? 'NOT FOUND') . "\n\n";

echo "Test completed!\n";