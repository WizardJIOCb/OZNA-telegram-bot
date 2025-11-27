# Notes Feature Reminder Implementation

## Overview
Added automatic reminder about the notes feature after users submit specific timesheet activities.

## User Request
"что если после сформированного отчета, пользователь получит еще одно сообщение 'незабудь сообщить о важном, оставь заметку для коллег' но такое сообщение он будет видет после отчетов не всех активностей, а только следующих:
- Проведение ЗПИ
- Работы по графику  
- Работы неучтенные в ВОР"

## Implementation Details

### File Modified
- `CustomCommands/Handlers/TabelHandler.php`

### Changes Made

#### 1. Added Notes Reminder Logic (lines ~4050-4058)
After successfully saving timesheet entry and sending confirmation message:
```php
// Check if user should receive reminder about notes feature
if ($this->shouldSendNotesReminder($activityType)) {
    error_log("📝 Sending notes reminder for activity: {$activityType}");
    $reminderText = "💡 <b>Не забудь сообщить о важном!</b>\n\nОставь заметку для коллег через меню\n📌 <b>Заметки</b>";
    $this->messageService->sendMessage($chatId, $reminderText);
}
```

#### 2. Created Helper Method `shouldSendNotesReminder()` (lines ~4060-4090)
Determines which activity types should trigger the reminder:

**Activity Type 1: ZPI Participation (Проведение ЗПИ)**
- Activity key: `УчастиеВЗПИ`
- Display name: "Участие в ЗПИ"

**Activity Type 2: Works by Schedule (Работы по графику)**
- **PNR variants** (containing `РабПоГр`):
  - `РабПоГр_ЭлектрОб` (Electrical equipment)
  - `РабПоГр_ВентОтопл` (Heating & ventilation)
  - `РабПоГр_ОхрСигн` (Security alarm)
  - `РабПоГр_Автом` (Automation)
  - `РабПоГр_ПожСигн` (Fire alarm)
  - `РабПоГр_ТехОб` (Technological equipment)
  - `РабПоГр_Метрол` (Metrology)
- **ШМР variants** (containing `_РабГр`):
  - `ШМР_ЭлОб_МонтОб_РабГр` (Electrical equipment - mounting)
  - `ШМР_ЭлОб_МонтКбСист_РабГр` (Electrical equipment - cable systems)
  - `ШМР_ПожСигн_МонтОб_РабГр` (Fire alarm - mounting)
  - `ШМР_ОхрСигн_МонтОб_РабГр` (Security alarm - mounting)
  - `ШМР_Автом_МонтОб_РабГр` (Automation - mounting)
  - `ШМР_ОтоплВент_МонтОб_РабГр` (HVAC - mounting)
  - And all other ШМР sub-activities with `_РабГр`
- **ШМР technological equipment** (containing `РабПоГр`):
  - `ШМР_МонтажТрубопроводов_РабПоГр` (Pipeline installation)
  - `ШМР_ГидроПневмоИспытания_РабПоГр` (Hydro/pneumatic testing)
  - `ШМР_МонтажТехнологическогоОборудования_РабПоГр` (Technological equipment)
  - And all other technological equipment activities

**Activity Type 3: Works Not Accounted in VOR (Работы неучтенные в ВОР)**
- All variants containing `НеучВОР` in activity type name
- Includes:
  - Equipment installation variants
  - ШМР variants: `ШМР_МонтажТрубопроводов_НеучВОР`, `ШМР_ГидроПневмоИспытания_НеучВОР`, etc.

### Message Flow

**Before:**
1. User selects activity → Confirmation dialog → User confirms
2. System saves to database
3. System sends confirmation message: "✅ Запись добавлена в табель..."

**After:**
1. User selects activity → Confirmation dialog → User confirms
2. System saves to database
3. System sends confirmation message: "✅ Запись добавлена в табель..."
4. **IF activity matches criteria**: System sends reminder message:
   ```
   💡 Не забудь сообщить о важном!
   
   Оставь заметку для коллег через меню
   📌 Заметки
   ```

## Testing Scenarios

### Should Trigger Reminder:
1. ✅ Select "Участие в ЗПИ" (ZPI participation)
2. ✅ Select any "Работы по графику" option for any equipment type
3. ✅ Select any "Работы неучтенные в ВОР" option

### Should NOT Trigger Reminder:
1. ❌ "Формирование ИД" (ID formation)
2. ❌ "Подтверждение вех" (Milestone confirmation)
3. ❌ "Дополнительные работы" (Additional works)
4. ❌ "Простой" (Idle time)
5. ❌ Any other activity type

## Benefits

1. **User Education**: Proactively informs users about the notes feature
2. **Context-Aware**: Only shown after activities where notes are most relevant
3. **Non-Intrusive**: Just a friendly reminder, doesn't block workflow
4. **Increases Engagement**: Encourages team communication and knowledge sharing

## Technical Notes

- Reminder is sent AFTER confirmation message (sequential, not blocking)
- Uses existing `MessageService::sendMessage()` method
- Logging added for debugging: `📝 Sending notes reminder for activity: {activityType}`
- String matching is flexible: checks if activity type CONTAINS the key patterns
- No database changes required
- No changes to existing confirmation flow

## Related Features

- Notes Export (Project Reports menu)
- Notes Management (Notes menu)
- General Journal vs By Project export
- Active/Closed/All status filtering

---

## Bug Fix: ШМР Equipment "Работы по графику" Not Triggering Reminder

### Issue Found
After initial testing, user reported that ШМР electrical equipment "Работы по графику" activities were NOT triggering the notes reminder.

### Root Cause
- **PNR activities** use format: `РабПоГр_ЭлектрОб`, `РабПоГр_ВентОтопл`, etc.
- **ШМР equipment activities** use SHORT format: `ШМР_ЭлОб_МонтОб_РабГр` (note: `_РабГр` NOT `_РабПоГр`)
- **ШМР technological equipment** uses FULL format: `ШМР_МонтажТрубопроводов_РабПоГр`
- Original check only looked for `РабПоГр`, missing all ШМР equipment activities with `_РабГр`

### Fix Applied
Updated `shouldSendNotesReminder()` method to check for BOTH patterns:
```php
// 2. Works by schedule (Работы по графику)
// Check for all РабПоГр variants (PNR: electrical, HVAC, security, automation, fire, tech, metrology)
// AND all _РабГр variants (ШМР: electrical equipment, fire alarm, security, automation, HVAC)
if (str_contains($activityType, 'РабПоГр') || str_contains($activityType, 'РабПоГр_') ||
    str_contains($activityType, '_РабПоГр') || str_contains($activityType, '_РабГр')) {
    return true;
}
```

### Now Covers All Cases
✅ PNR: `РабПоГр_ЭлектрОб`, `РабПоГр_ВентОтопл`, etc.  
✅ ШМР Equipment: `ШМР_ЭлОб_МонтОб_РабГр`, `ШМР_ПожСигн_МонтОб_РабГр`, etc.  
✅ ШМР Tech Equipment: `ШМР_МонтажТрубопроводов_РабПоГр`, etc.  

### Test Scenarios Updated
**Should NOW trigger reminder (fixed):**
- ✅ ШМР → Электрооборудование → Монтаж оборудования → Работы по графику
- ✅ ШМР → Пожарная сигнализация → Монтаж оборудования → Работы по графику
- ✅ ШМР → Охранная сигнализация → Монтаж оборудования → Работы по графику
- ✅ ШМР → Автоматизация → Монтаж оборудования → Работы по графику
- ✅ ШМР → Отопление и вентиляция → Монтаж оборудования → Работы по графику
- ✅ And all other ШМР equipment sub-activities with "Работы по графику"
