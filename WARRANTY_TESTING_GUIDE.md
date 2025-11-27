# WARRANTY EQUIPMENT TESTING GUIDE

## Current Status
✅ All warranty equipment code is properly implemented
✅ Enhanced debugging has been added to track warranty equipment flow
✅ Activity types are correctly defined in TabelHandler
✅ TabelEntry formatting is properly configured

## Testing Steps

### 1. Prepare for Testing
Before testing, ensure:
- Your user has FIO set: `/fio Фамилия Имя Отчество`
- Your user has functional direction set: `/profile`
- You have access to the bot logs to see debug messages

### 2. Test Warranty Equipment Flow
1. Open your Telegram bot
2. Send `/tabels` command
3. Click **"Пусконаладочные работы"**
4. Select any project and current date
5. Click **"🛡️ Работы по гарантии"**
6. Click **"⚡ Электрооборудование"** (or any other equipment type)
7. Click **"Да"** button

### 3. Expected Results
**SUCCESS**: You should see a confirmation message like:
```
✅25.09.2025, Проект Работы по гарантии: Добавлена запись в табель: ⚡ Работы по гарантии (Электрооборудование)
```

**FAILURE**: If you see "неизвестная команда табеля", there's still an issue.

### 4. Debug Information
If the test fails, check bot logs for these debug messages:

#### Warranty Equipment Processing:
- `🔧 WARRANTY EXECUTION DEBUG: Starting warranty equipment processing`
- `🔧 WARRANTY EXECUTION DEBUG: Category: 'cat_Гарантии_ЭлектрОб_yes'`
- `🔧 WARRANTY EXECUTION DEBUG: Equipment type: 'ЭлектрОб'`
- `🔧 WARRANTY EXECUTION DEBUG: Activity type: 'РабГарантии_ЭлектрОб'`

#### Database Operations:
- `🔧 WARRANTY EXECUTION DEBUG: About to create tabel entry for warranty equipment`
- `🔧 WARRANTY EXECUTION DEBUG: addEntry SUCCESS for warranty equipment!`
- `🔧 WARRANTY EXECUTION DEBUG: Creating confirmation message for warranty`

#### Message Sending:
- `🔧 WARRANTY EXECUTION DEBUG: Warranty message created, sending to user`

### 5. Common Issues and Solutions

#### Issue: "неизвестная команда табеля"
**Possible Causes:**
1. Callback not recognized by TabelHandler
2. Activity type not found in activityTypes array
3. Missing conversation context (project_code, tabel_data)

**Debug Steps:**
1. Check if debug messages appear in logs
2. Verify callback patterns match exactly
3. Ensure UTF-8 encoding is correct

#### Issue: No confirmation message
**Possible Causes:**
1. Database connection error
2. TabelEntry.addEntry() failing
3. Message formatting issues

**Debug Steps:**
1. Look for "addEntry FAILED" in logs
2. Check database connectivity
3. Verify TabelEntry service configuration

### 6. All Warranty Equipment Types to Test
- ⚡ Электрооборудование → `РабГарантии_ЭлектрОб`
- 📎 Средства измерений → `РабГарантии_СрИзм`
- 💧 Насосное оборудование → `РабГарантии_НасОб`
- 🔧 Трубопроводная арматура → `РабГарантии_ТрубАрм`
- ⚙️ Исполнительные механизмы → `РабГарантии_ИспМех`
- 💻 Контроллерное оборудование → `РабГарантии_КонтрОб`
- 📦 Прочее оборудование → `РабГарантии_Прочее`

Test each type to ensure they all work correctly.

### 7. If Issues Persist
If warranty equipment still doesn't work after these enhancements:
1. Share the specific error messages from bot logs
2. Confirm which step in the flow fails
3. Test with different equipment types to see if it's type-specific
4. Verify the bot has been restarted to load the new code

## Recent Enhancements
- Added detailed warranty equipment debugging logs
- Enhanced conversation context setup for warranty equipment
- Improved error tracking for warranty equipment confirmation flow
- Added specific logging for warranty equipment report generation

The warranty equipment functionality should now work correctly with comprehensive debugging to identify any remaining issues.