# Notes Export Feature - Technical Documentation

## Overview

Added comprehensive notes export functionality to the project reports menu, allowing users to export project notes in CSV format with flexible date range selection.

## Changes Made

### 1. Modified Files

#### `CustomCommands/Handlers/ProjectHandler.php`

**Added Methods:**

1. **`showNotesExportProjectMenu()`** (Line ~3425)
   - Shows project selection menu for notes export
   - Uses ProjectSelector to get all projects
   - Applies intelligent sorting

2. **`showNotesExportDateRangeMenu()`** (Line ~3465)
   - Shows date range selection menu
   - Provides predefined periods (today, yesterday, week, month, all)
   - Includes custom date range option

3. **`showNotesExportStartDateMenu()`** (Line ~3500)
   - Prompts user for custom start date
   - Sets conversation state for date input
   - Validates DD.MM.YYYY format

4. **`showNotesExportEndDateMenu()`** (Line ~3525)
   - Prompts user for custom end date
   - Sets conversation state for date input
   - Validates DD.MM.YYYY format

5. **`handleNotesExportDateInput()`** (Line ~3550)
   - Public method called from GenericmessageCommand
   - Handles both start and end date validation
   - Routes to appropriate menu based on state

6. **`generateNotesExport()`** (Line ~3610)
   - Generates CSV for predefined periods
   - Retrieves notes from database
   - Creates and sends CSV file

7. **`generateNotesExportCustom()`** (Line ~3660)
   - Generates CSV for custom date ranges
   - Converts DD.MM.YYYY to SQL format
   - Creates and sends CSV file

8. **`getProjectNotes()`** (Line ~3710)
   - Retrieves notes from database using NotesService
   - Filters by date range if specified
   - Returns array of notes

9. **`createNotesExportCsv()`** (Line ~3750)
   - Creates CSV file with proper UTF-8 BOM
   - Includes comprehensive headers
   - Formats dates and status for display

**Callback Handlers Added:**

```php
// Line ~1336
if ($callbackData === 'proj_report_notes') {
    return $this->showNotesExportProjectMenu($chatId, $userId);
}

// Line ~1487
if (str_starts_with($callbackData, 'proj_notes_export_')) {
    $projectCode = str_replace('proj_notes_export_', '', $callbackData);
    return $this->showNotesExportDateRangeMenu($chatId, $userId, $conversation, $projectCode);
}

// Line ~1493
if (str_starts_with($callbackData, 'proj_notes_period_')) {
    $remaining = str_replace('proj_notes_period_', '', $callbackData);
    $parts = explode('_', $remaining, 2);
    if (count($parts) >= 2) {
        $projectCode = $parts[0];
        $period = $parts[1];
        return $this->generateNotesExport($chatId, $userId, $conversation, $projectCode, $period);
    }
}

// Line ~1503
if (str_starts_with($callbackData, 'proj_notes_custom_')) {
    $projectCode = str_replace('proj_notes_custom_', '', $callbackData);
    return $this->showNotesExportStartDateMenu($chatId, $userId, $conversation, $projectCode);
}
```

**Modified `showProjectReportMenu()`:**

Added notes export button to the menu (Line ~2147):
```php
[
    ['text' => '📌 Выгрузка заметок', 'callback_data' => 'proj_report_notes']
]
```

#### `CustomCommands/GenericmessageCommand.php`

**Added Methods:**

1. **`isAwaitingNotesExportDate()`** (Line ~1030)
   - Checks if user is awaiting notes export date input
   - Monitors conversation state flags

2. **`handleNotesExportDateInput()`** (Line ~1045)
   - Routes date input to ProjectHandler
   - Creates ProjectHandler instance
   - Returns appropriate ServerResponse

**Modified Text Input Flow:**

Added check for notes export date input (Line ~171):
```php
// Check if user is awaiting notes export date input
if ($this->isAwaitingNotesExportDate($conversation, $userId)) {
    return $this->handleNotesExportDateInput($chatId, $userId, $text, $conversation);
}
```

### 2. New Files Created

#### `test_notes_export.php`
- Comprehensive test script
- Validates database connection
- Tests notes retrieval
- Tests CSV generation
- Provides detailed output

#### `NOTES_EXPORT_GUIDE_RU.md`
- User-friendly documentation in Russian
- Step-by-step usage guide
- Examples and FAQ
- Technical details for users

#### `NOTES_EXPORT_TECHNICAL.md`
- This file
- Technical implementation details
- Code structure documentation
- Integration points

## Architecture

### Data Flow

1. **User initiates export:**
   ```
   User clicks "📌 Выгрузка заметок"
   → CallbackqueryCommand captures callback
   → Routes to ProjectHandler::handleProjectReportCallback()
   → Matches 'proj_report_notes'
   → Calls showNotesExportProjectMenu()
   ```

2. **Project selection:**
   ```
   User selects project
   → Callback: proj_notes_export_{PROJECT_CODE}
   → Calls showNotesExportDateRangeMenu()
   ```

3. **Date range selection:**
   ```
   User selects period
   → Callback: proj_notes_period_{PROJECT}_{PERIOD}
   → Calls generateNotesExport()
   ```

4. **Custom date flow:**
   ```
   User selects custom period
   → Callback: proj_notes_custom_{PROJECT}
   → Calls showNotesExportStartDateMenu()
   → Sets conversation state: awaiting_notes_export_start_date
   → User enters date → GenericmessageCommand::handleNotesExportDateInput()
   → Shows showNotesExportEndDateMenu()
   → Sets conversation state: awaiting_notes_export_end_date
   → User enters date → generateNotesExportCustom()
   ```

### Conversation State Variables

| Variable | Type | Purpose |
|----------|------|---------|
| `awaiting_notes_export_start_date` | boolean | User is entering start date |
| `awaiting_notes_export_end_date` | boolean | User is entering end date |
| `notes_export_project_code` | string | Current project being exported |
| `notes_export_start_date` | string | Selected start date (DD.MM.YYYY) |
| `notes_export_end_date` | string | Selected end date (DD.MM.YYYY) |

### Callback Data Patterns

| Pattern | Purpose | Example |
|---------|---------|---------|
| `proj_report_notes` | Open notes export menu | `proj_report_notes` |
| `proj_notes_export_{CODE}` | Select project | `proj_notes_export_ХХХ` |
| `proj_notes_period_{CODE}_{PERIOD}` | Select predefined period | `proj_notes_period_ХХХ_week` |
| `proj_notes_custom_{CODE}` | Start custom period selection | `proj_notes_custom_ХХХ` |

### Database Integration

**Method:** Uses NotesService with PDO connection

**Query Pattern:**
```php
// Get all notes for project
$notesService->getNotesByProject($projectCode);

// Then filter by date in PHP
array_filter($notes, function($note) use ($startTime, $endTime) {
    $noteTime = strtotime($note['created_at']);
    return $noteTime >= $startTime && $noteTime <= $endTime;
});
```

**Database Credentials:**
- Host: 82.146.42.213
- Database: tg_bot
- User: WizardJIOCb
- (Hardcoded, same as other system components)

## CSV File Structure

### Headers
```csv
№,Дата создания,Автор,Контекст,Этап,Текст заметки,Статус,Дата закрытия,Закрыл
```

### Sample Row
```csv
13,"18.10.2025 21:28","Тест тест тест","ПНР","Электрооборудование","Тест заметки...","Активна","",""
```

### File Naming Convention
```
notes_export_{PROJECT_CODE}_{PERIOD}_{TIMESTAMP}.csv
```

Examples:
- `notes_export_ХХХ_today_2025-10-18_18-41-37.csv`
- `notes_export_ОИ834_week_2025-10-18_18-41-37.csv`
- `notes_export_ХХХ_01.10.2025_15.10.2025_2025-10-18_18-41-37.csv`

## Testing

### Test Coverage

1. **Database Connection** ✅
   - Validates credentials
   - Confirms connection

2. **Notes Retrieval** ✅
   - Gets projects with notes
   - Retrieves notes by project
   - Displays sample note

3. **CSV Generation** ✅
   - Creates file with proper encoding
   - Writes headers
   - Writes data rows
   - Validates file size

4. **File Cleanup** ✅
   - Removes test files after sending

### Running Tests

```bash
php test_notes_export.php
```

Expected output:
```
╔══════════════════════════════════════════════════╗
║   ТЕСТ ФУНКЦИИ ВЫГРУЗКИ ЗАМЕТОК                 ║
╚══════════════════════════════════════════════════╝

1. Проверка подключения к БД...
   ✅ Подключение успешно

2. Получение списка проектов с заметками...
   ✅ Найдено проектов с заметками: 1
      • ХХХ: 7 заметок

3. Получение заметок для проекта 'ХХХ'...
   ✅ Получено заметок: 7
      ...

4. Тест генерации CSV файла...
   ✅ CSV файл создан успешно
      ...

╔══════════════════════════════════════════════════╗
║   ✅ ВСЕ ТЕСТЫ ПРОЙДЕНЫ УСПЕШНО                 ║
╚══════════════════════════════════════════════════╝
```

## Security Considerations

1. **File Access:** CSV files are created in `Uploads/` directory with restrictive permissions (0755)
2. **File Cleanup:** Files are deleted immediately after sending via Telegram API
3. **Input Validation:** All dates validated with `checkdate()` and regex patterns
4. **SQL Injection:** Protected by PDO prepared statements in NotesService
5. **Access Control:** Only authorized users can access project reports menu

## Performance

- **Database Queries:** Single query per export via NotesService
- **Memory:** Notes loaded into memory, filtered in PHP
- **File I/O:** Minimal - single write, immediate delete
- **Network:** Single API call to send document

## Future Enhancements

Potential improvements:
1. Export multiple projects at once
2. Filter by note status (active/closed only)
3. Filter by author
4. Filter by stage
5. Export in different formats (JSON, PDF)
6. Scheduled automated exports
7. Email delivery option

## Maintenance

### Monitoring
- Check error logs for `ProjectHandler::getProjectNotes error`
- Monitor file creation in `Uploads/` directory
- Watch for conversation state issues

### Common Issues

**Issue:** Notes not showing in export  
**Solution:** Check `project_id` in database matches project code exactly

**Issue:** Date validation fails  
**Solution:** Ensure DD.MM.YYYY format, valid dates only

**Issue:** CSV encoding issues  
**Solution:** BOM already added, ensure Excel/Sheets supports UTF-8

## Dependencies

- PHP Telegram Bot library (Longman)
- NotesService (CustomCommands/Services/)
- ProjectHandler (CustomCommands/Handlers/)
- MessageService (CustomCommands/Services/)
- DatabaseService (CustomCommands/Services/)
- ProjectSelector (CustomCommands/Services/)

## Version History

- **v1.0** (2025-10-18)
  - Initial implementation
  - Basic CSV export
  - Predefined and custom date ranges
  - Full integration with existing menu

---

*Implementation completed: October 18, 2025*  
*Developer: AI Assistant*  
*Project: OZNA Work Bot*
