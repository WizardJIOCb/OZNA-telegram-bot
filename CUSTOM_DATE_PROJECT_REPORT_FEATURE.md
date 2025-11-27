# Custom Date Range Feature for Project Reports

## Overview
Added "Выбранный период" (Custom Period) button to project reports, allowing users to manually input start and end dates instead of selecting predefined periods.

## Features Added

### 1. New Button in Date Selection Menu
- **Button**: 📆 Выбранный период
- **Location**: After "Весь период" in project report date selection
- **Callback**: `proj_report_custom_{projectCode}`

### 2. Date Input Flow
1. User clicks "Выбранный период"
2. System prompts for start date (DD.MM.YYYY format)
3. User enters start date
4. System prompts for end date (DD.MM.YYYY format)
5. User enters end date
6. System shows filter selection menu

### 3. Filter Support
After entering custom dates, users can choose:
- ⚡ Быстрые фильтры (6 quick filters)
- 📊 Без фильтра (No filter - all data)

### 4. Date Validation
- Format validation: Must be DD.MM.YYYY
- Date validity check using `checkdate()`
- Clear error messages for invalid input

## Files Modified

### 1. ProjectHandler.php
**New Methods Added:**
- `showProjectReportStartDateMenu()` - Show start date input prompt
- `showProjectReportEndDateMenu()` - Show end date input prompt  
- `handleProjectReportDateInput()` - Process date inputs from user
- `showProjectReportCustomFilterMenu()` - Show filter menu for custom dates
- `showProjectReportCustomQuickFilterMenu()` - Show quick filter options
- `generateProjectReportCustom()` - Generate report with custom date range

**Updated Methods:**
- `showReportDateRangeMenu()` - Added "Выбранный период" button

**New Callback Handlers:**
- `proj_report_custom_{projectCode}` - Start custom date flow
- `proj_report_custom_filter_{projectCode}` - Show quick filters
- `proj_report_custom_nofilter_{projectCode}` - Generate without filter
- `proj_report_custom_quick_{projectCode}_{filterType}` - Generate with filter

### 2. GenericmessageCommand.php  
**New Methods Added:**
- `isAwaitingProjectReportDate()` - Check if waiting for project report date input
- `handleProjectReportDateInput()` - Route date input to ProjectHandler

**Updated Methods:**
- `execute()` - Added project report date input check

## Conversation State Management

### State Variables
Stored in `$conversation->notes`:
- `awaiting_project_report_start_date` - Waiting for start date
- `awaiting_project_report_end_date` - Waiting for end date
- `project_report_code` - Current project code
- `project_report_start_date` - Entered start date
- `project_report_end_date` - Entered end date

## User Flow Example

```
1. User: Проекты → Отчёт по проекту → По-проектный отчёт
2. User: Selects project "ПНР-001"
3. System: Shows date range menu
4. User: Clicks "📆 Выбранный период"
5. System: "Введите дату начала отчёта в формате ДД.ММ.ГГГГ"
6. User: Types "01.01.2025"
7. System: "Введите дату окончания отчёта в формате ДД.ММ.ГГГГ"
8. User: Types "31.01.2025"
9. System: Shows filter menu
10. User: Selects filter or no filter
11. System: Generates and sends CSV report
```

## CSV File Naming

### Without Filter
`project_report_{projectCode}_{startDate}_{endDate}_{timestamp}.csv`

Example: `project_report_ПНР-001_01.01.2025_31.01.2025_2025-01-15_14-30-00.csv`

### With Filter
`project_filtered_{projectCode}_{filterType}_{startDate}_{endDate}_{timestamp}.csv`

Example: `project_filtered_ПНР-001_warranty_01.01.2025_31.01.2025_2025-01-15_14-30-00.csv`

## Error Handling

### Invalid Date Format
```
⚠️ Неверный формат даты. Используйте формат ДД.ММ.ГГГГ (например, 01.10.2025)
```

### Invalid Date
```
⚠️ Некорректная дата. Проверьте введённые данные.
```

### No Entries Found
```
⚠️ Нет данных

За период с {startDate} по {endDate} нет активностей по проекту {projectCode}.
```

## Technical Notes

1. **Date Conversion**: Dates are converted from DD.MM.YYYY to SQL format YYYY-MM-DD HH:MM:SS
2. **Tester Exclusion**: All reports automatically exclude users with FN "Тестировщик"
3. **Filter Reuse**: Uses same `applyQuickFilter()` method as simple reports
4. **State Cleanup**: Conversation state flags are cleared after processing

## Testing Checklist

- [ ] Custom date button appears in menu
- [ ] Start date input accepts valid dates
- [ ] End date input accepts valid dates
- [ ] Invalid format shows error
- [ ] Invalid date shows error
- [ ] Filter menu appears after dates entered
- [ ] Quick filters work correctly
- [ ] No filter option works
- [ ] CSV file generated with correct data
- [ ] File naming includes custom dates
- [ ] Tester exclusion working
- [ ] Back button navigation works

## Deployment

```bash
# Upload files
scp "c:\ozna_bot\CustomCommands\Handlers\ProjectHandler.php" root@212.193.28.202:/var/www/html/ozna_bot/CustomCommands/Handlers/ProjectHandler.php

scp "c:\ozna_bot\CustomCommands\GenericmessageCommand.php" root@212.193.28.202:/var/www/html/ozna_bot/CustomCommands/GenericmessageCommand.php
```

No webhook restart needed - changes load automatically.

## Status
✅ **IMPLEMENTED** - Ready for deployment and testing
