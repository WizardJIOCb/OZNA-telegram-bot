<?php

// Simple test to check string matching logic

echo "Testing string matching logic...\n";

// Test data
$callbackData = 'category_Проекты';

// ProjectHandler logic
$startsWithCategoryProjects = str_starts_with($callbackData, 'category_Проекты');
$startsWithProjectList = str_starts_with($callbackData, 'proj_list_');
$startsWithProjectControl = str_starts_with($callbackData, 'proj_ctrl_');

$projectCanHandle = $startsWithCategoryProjects || $startsWithProjectList || $startsWithProjectControl;

echo "ProjectHandler tests:\n";
echo "  startsWithCategoryProjects: " . ($startsWithCategoryProjects ? 'YES' : 'NO') . "\n";
echo "  startsWithProjectList: " . ($startsWithProjectList ? 'YES' : 'NO') . "\n";
echo "  startsWithProjectControl: " . ($startsWithProjectControl ? 'YES' : 'NO') . "\n";
echo "  ProjectHandler canHandle: " . ($projectCanHandle ? 'YES' : 'NO') . "\n";

// SupervisorHandler logic (original)
$supervisorCanHandleOriginal = str_starts_with($callbackData, 'category_Супервайзер');

// SupervisorHandler logic (fixed)
$supervisorCanHandleFixed = str_starts_with($callbackData, 'category_Супервайзер_');

echo "\nSupervisorHandler tests:\n";
echo "  Original logic (startsWith category_Супервайзер): " . ($supervisorCanHandleOriginal ? 'YES' : 'NO') . "\n";
echo "  Fixed logic (startsWith category_Супервайзер_): " . ($supervisorCanHandleFixed ? 'YES' : 'NO') . "\n";

// Additional test cases
$testCases = [
    'category_Проекты',
    'category_Проекты_МоиПроекты',
    'category_Супервайзер',
    'category_Супервайзер_Отчеты',
    'category_Админка',
    'category_Админка_Проекты'
];

echo "\nDetailed test cases:\n";
foreach ($testCases as $case) {
    $projectMatch = str_starts_with($case, 'category_Проекты');
    $supervisorMatchOriginal = str_starts_with($case, 'category_Супервайзер');
    $supervisorMatchFixed = str_starts_with($case, 'category_Супервайзер_');
    
    echo "  '{$case}':\n";
    echo "    ProjectHandler: " . ($projectMatch ? 'YES' : 'NO') . "\n";
    echo "    SupervisorHandler (original): " . ($supervisorMatchOriginal ? 'YES' : 'NO') . "\n";
    echo "    SupervisorHandler (fixed): " . ($supervisorMatchFixed ? 'YES' : 'NO') . "\n";
}

echo "\nTest completed.\n";