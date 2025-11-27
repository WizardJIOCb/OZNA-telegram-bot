# Before/After Comparison - Idle Stage Information Fix

## Visual Comparison

### Before Fix ❌
```
😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: 
⚠️ Простой (Транспорт (Отсутствие свободных мест))
         └─────┘
          Missing stage info!
```

### After Fix ✅
```
😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: 
⚠️ Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))
         └───────────────┘
          Stage info now included!
```

## Code Comparison

### Before Fix
```php
// Idle activities ("Простой") - use new commissioning format
$idleActivities = ['Простой', 'ШМ_Простой'];

if (in_array($activityType, $idleActivities)) {
    return sprintf(
        "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
        $fio,
        $date,
        $projectCode,
        $activityText  // ← Only "Простой (reason)", missing stage
    );
}
```

### After Fix
```php
// Idle activities ("Простой") - use new commissioning format with stage information
$idleActivities = ['Простой', 'ШМ_Простой', 'МобБриг_Простой'];

if (in_array($activityType, $idleActivities)) {
    // Determine stage information based on activity type
    if ($activityType === 'МобБриг_Простой') {
        $stageInfo = 'Монтажные работы';
    } elseif ($activityType === 'ШМ_Простой') {
        $stageInfo = 'Шеф-монтаж';
    } else {
        $stageInfo = 'Пусконаладка';
    }
    
    // Build activity text with stage: "Простой (Пусконаладка) (причина)"
    if (strpos($activityText, 'Простой') === 0) {
        if (preg_match('/^Простой \((.+)\)$/', $activityText, $matches)) {
            $reason = $matches[1];
            $activityText = "Простой ({$stageInfo}) ({$reason})";
        } else {
            $activityText = "Простой ({$stageInfo})";
        }
    }
    
    return sprintf(
        "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
        $fio,
        $date,
        $projectCode,
        $activityText  // ← Now "Простой (Stage) (reason)" with stage!
    );
}
```

## All Department Examples

### 1. Commissioning Department (Отдел ПНР)
**Before:**
```
😨 ⚠️ 11.10.2025, Проект ОИ834: ИНЦИДЕНТ - Добавлена запись в табель: 
⚠️ Простой (Ожидание допоставки (Дефицит по спецификации))
```

**After:**
```
😨 ⚠️ 11.10.2025, Проект ОИ834: ИНЦИДЕНТ - Добавлена запись в табель: 
⚠️ Простой (Пусконаладка) (Ожидание допоставки (Дефицит по спецификации))
```

### 2. Supervision Department (Шеф-монтаж)
**Before:**
```
😨 ⚠️ 12.10.2025, Проект ОИ726: ИНЦИДЕНТ - Добавлена запись в табель: 
⚠️ Простой (Погодные условия (Мороз))
```

**After:**
```
😨 ⚠️ 12.10.2025, Проект ОИ726: ИНЦИДЕНТ - Добавлена запись в табель: 
⚠️ Простой (Шеф-монтаж) (Погодные условия (Мороз))
```

### 3. Mobile Brigade (Мобильная бригада)
**Before:**
```
😨 ⚠️ 13.10.2025, Проект ОИ912: ИНЦИДЕНТ - Добавлена запись в табель: 
⚠️ Простой (Готовность Заказчика (Отсутствие строительной готовности))
```

**After:**
```
😨 ⚠️ 13.10.2025, Проект ОИ912: ИНЦИДЕНТ - Добавлена запись в табель: 
⚠️ Простой (Монтажные работы) (Готовность Заказчика (Отсутствие строительной готовности))
```

## Why This Matters

### Business Value
1. **Clarity**: Immediately see which work stage/process the idle time belongs to
2. **Reporting**: Better analytics and tracking of idle time per department
3. **Consistency**: Confirmation messages already showed stage info, now reports match
4. **Compliance**: More detailed incident reporting for management reviews

### Technical Value
1. **Code Consistency**: `createTabelMessage()` now matches `getConfirmationMessage()` logic
2. **Maintainability**: Single source of truth for stage determination
3. **Debugging**: Added comprehensive logging for troubleshooting
4. **Coverage**: Handles all three department types (ПНР, ШМ, МобБриг)

## Stage Mapping

| Activity Type | Stage Name | Display Text |
|--------------|------------|--------------|
| `Простой` | Пусконаладка | Пусконаладочные работы |
| `ШМ_Простой` | Шеф-монтаж | Шеф-монтажные работы |
| `МобБриг_Простой` | Монтажные работы | Installation works |

## Testing Evidence

All test cases passed ✅:

1. ✅ Commissioning Department - Stage: "Пусконаладка"
2. ✅ Supervision Department - Stage: "Шеф-монтаж"
3. ✅ Mobile Brigade - Stage: "Монтажные работы"
4. ✅ User's specific example matches exactly

## Rollout Notes

- **No Database Changes**: This is purely a formatting fix
- **No API Changes**: Message format enhanced, no breaking changes
- **Backward Compatible**: Existing idle entries remain unchanged
- **Immediate Effect**: New idle entries will show stage info immediately after deployment
