# Fix for Custom Work Descriptions in Project Activities

## Issue Description
Custom work descriptions entered by regular "user" level employees were not appearing in the final report for project activities, even though they were visible in the confirmation message before clicking "Yes". This was working correctly for other "Other" activities like Automation equipment.

## Root Cause
The issue was in the `getConfirmationMessage` method in `TabelEntry.php`. The `projectActivities` array was missing the `'Прочее_ПроектнаяДеятельность'` activity type, which caused inconsistent handling between confirmation messages and final report messages.

In the `createTabelMessage` method, `'Прочее_ПроектнаяДеятельность'` was correctly included in the project activities array and had special handling to add custom descriptions. However, in the `getConfirmationMessage` method, it was missing from the array, which meant the custom description handling logic wasn't being applied consistently.

## Fix Applied
Added `'Прочее_ПроектнаяДеятельность'` to the `projectActivities` array in the `getConfirmationMessage` method in `TabelEntry.php`:

```php
// Project activities ("Проектная деятельность") - use new format
$projectActivities = [
    'УчастиеВЗПИ', 'РазрПрограммы', 'РазрГрафика', 'ОргДопоставки', 'УчастиеВОПИ', 'ИсполнениеОбязМастера', 'Прочее_ПроектнаяДеятельность'
];
```

## Verification
Created and ran multiple test scripts to verify:
1. Custom descriptions are properly included in final report messages for project activities
2. The behavior is now consistent with how Automation equipment handles custom descriptions
3. The complete flow from user input to final report works correctly

## Result
Custom work descriptions now appear correctly in final reports for project activities, just like they do for Automation equipment and other activity types.