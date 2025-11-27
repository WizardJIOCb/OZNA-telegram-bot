# Project Addition Fix Summary

## Problem Description
When users added a new project using the "Добавить проект" button, the project was correctly added to the system and a confirmation message was sent. However, the added project did not appear in the project selection menu when creating a new entry in the timesheet (табель).

## Root Cause
The issue was in the `AdminService` class in two methods:
1. `saveProjectsToFile()` - This method was preparing the JSON data to save but had a commented-out `file_put_contents` call to avoid permission issues.
2. `saveAccessRequests()` - This method had the same issue.

As a result:
- Projects were added to the in-memory array correctly
- Confirmation messages were sent correctly
- But projects were NOT saved to the `data/projects.json` file
- When the bot restarted or when `ProjectSelector` loaded projects, it only loaded from the file, not from the in-memory array

## Solution
Uncommented and implemented the `file_put_contents` calls in both methods to actually save data to files:

### 1. Fixed `saveProjectsToFile()` method in `AdminService.php`:
```php
private function saveProjectsToFile(): void
{
    $filePath = __DIR__ . '/../../data/projects.json';
    
    // Создаем директорию если не существует
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $jsonData = json_encode($this->projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // Write data to file
    file_put_contents($filePath, $jsonData);
}
```

### 2. Fixed `saveAccessRequests()` method in `AdminService.php`:
```php
private function saveAccessRequests(array $requests): void
{
    // Создаем директорию если не существует
    $dir = dirname($this->accessRequestsFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $jsonData = json_encode(array_values($requests), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // Write data to file
    file_put_contents($this->accessRequestsFile, $jsonData);
}
```

## Verification
Created and ran multiple test scripts to verify:
1. Projects are correctly added to the in-memory array
2. Projects are correctly saved to the `data/projects.json` file
3. The complete project addition flow works correctly
4. New projects appear in the project selection menu

## Expected Result
After this fix, when users add a new project:
1. The project is added to the in-memory array
2. The project is saved to the `data/projects.json` file
3. The confirmation message is sent
4. When the bot restarts or when projects are loaded, the new project will be included
5. The new project will appear in the project selection menu when creating timesheet entries

This resolves the issue where added projects were not visible in the project selection menu.