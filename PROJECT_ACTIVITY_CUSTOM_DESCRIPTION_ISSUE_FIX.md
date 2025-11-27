# Project Activity Custom Description Issue - Analysis and Fix

## Issue Description
Custom work descriptions entered by regular "user" level employees are not appearing in the final report for project activities (Прочее_ПроектнаяДеятельность), even though they are visible in the confirmation message before clicking "Yes".

## Root Cause Analysis
After thorough investigation, we identified two main issues:

### 1. Missing Activity Type in Project Activities Array
**Fixed**: The `'Прочее_ПроектнаяДеятельность'` activity type was missing from the `projectActivities` array in the `getConfirmationMessage` method in `TabelEntry.php`. This caused inconsistent handling between confirmation messages and final report messages.

**Fix Applied**: Added `'Прочее_ПроектнаяДеятельность'` to the `projectActivities` array.

### 2. Incorrect Database Service Usage
**Fixed**: The `TabelEntry` service was using the old `pDB::getUserData($userId)` method directly instead of using the injected `DatabaseService`. This caused fatal errors because the `pDB` class was not being found or loaded properly.

**Fix Applied**: Updated the `TabelEntry` service to use `$this->databaseService->getUserData($userId)` instead.

## Verification
Created and ran multiple test scripts to verify:
1. Custom descriptions are properly included in final report messages for project activities
2. The behavior is now consistent with how Automation equipment handles custom descriptions
3. The complete flow from user input to final report works correctly

## Technical Details
The fix involved two key changes:

1. **TabelEntry.php** - Added `'Прочее_ПроектнаяДеятельность'` to the project activities array:
   ```php
   // Project activities ("Проектная деятельность") - use new format
   $projectActivities = [
       'УчастиеВЗПИ', 'РазрПрограммы', 'РазрГрафика', 'ОргДопоставки', 'УчастиеВОПИ', 'ИсполнениеОбязМастера', 'Прочее_ПроектнаяДеятельность'
   ];
   ```

2. **TabelEntry.php** - Fixed database service usage:
   ```php
   // Before (causing fatal errors):
   $userData = \pDB::getUserData($userId);
   
   // After (correct usage):
   $userData = $this->databaseService->getUserData($userId);
   ```

## Result
Custom work descriptions now appear correctly in final reports for project activities, just like they do for Automation equipment and other activity types. The fatal error that was preventing the system from working has been resolved.