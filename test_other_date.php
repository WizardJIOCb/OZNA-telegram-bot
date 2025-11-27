<?php

require_once __DIR__ . '/custom_autoload.php';

// Test the ProjectSelector date selection functionality for "Other date"
$projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();

// Test "Other date" callback
$otherDateCallback = 'category_Табель_ДругаяДата';

echo "Testing 'Other date' callback handling:\n\n";

echo "Callback: {$otherDateCallback}\n";

// Test isDateSelection method
$isDateSelection = $projectSelector->isDateSelection($otherDateCallback);
echo "isDateSelection: " . ($isDateSelection ? 'YES' : 'NO') . "\n";

// Test extractDate method
$extractedDate = $projectSelector->extractDate($otherDateCallback);
echo "extractDate: " . ($extractedDate ?? 'NULL') . "\n";

echo "\nThis should return NULL for 'Other date' so that the TabelHandler can ask for custom input.\n";