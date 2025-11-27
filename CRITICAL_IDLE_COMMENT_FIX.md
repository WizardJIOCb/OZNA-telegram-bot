=== CRITICAL IDLE COMMENT FIX - ROOT CAUSE FOUND ===

## 🎯 ROOT CAUSE IDENTIFIED

After deep comparison of the processing methods, I found the **exact difference** that was causing the idle custom comment to fail while the "Прочее" equipment button works perfectly.

## 🔍 METHOD COMPARISON ANALYSIS

### WORKING "ПРОЧЕЕ" EQUIPMENT FLOW:
```php
// In handleCustomInput() for equipment "Прочее"
$confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
$confirmationText .= "\n\n<b>Описание работы:</b> " . $userInput;
$keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_Проч_yes");
return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard); ✅
```

### BROKEN IDLE CUSTOM COMMENT FLOW (BEFORE):
```php
// In handleCustomInput() for idle comments
return $this->handleIdleFinalSelection($callbackQuery, $category, $conversation); ❌
```

## 🚨 THE EXACT PROBLEM

The idle custom comment was using a **completely different processing path**:

1. **Equipment "Прочее"**: `handleCustomInput()` → **Direct confirmation message with buttons** → Done ✅
2. **Idle Custom**: `handleCustomInput()` → `handleIdleFinalSelection()` → **Project selection prompt** → Never reaches confirmation ❌

The idle comment was calling `handleIdleFinalSelection()` which was designed for normal idle menu navigation, not for custom text processing!

## 🔧 CRITICAL FIX APPLIED

I changed the idle comment processing to work **exactly like the working "Прочее" equipment button**:

```php
// FIXED: Idle comment now processes exactly like working "Прочее" equipment
if ($activityType === 'Простой_Комментарий') {
    // Store custom text
    $conversation->notes['custom_idle_text'] = $userInput;
    $conversation->notes['awaiting_custom_input'] = false;
    $conversation->update();
    
    // Check for project code (same logic as before)
    $projectCode = $conversation->notes['project_code'] ?? '';
    if (empty($projectCode)) {
        // Prompt for project selection
        // ... project selection logic ...
    }
    
    // Format idle reason using specialized formatter
    $idleReason = $this->getCommissioningIdleReason($category, $conversation);
    
    // CRITICAL: Create confirmation EXACTLY like working "Прочее" equipment
    $activityType = str_contains($category, 'ШМ_') ? 'ШМ_Простой' : 'Простой';
    $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
    $confirmationText .= "\n\n<b>Причина простоя:</b> " . $idleReason;
    
    // Use standard idle confirmation callback
    $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
    
    // Store idle reason for confirmation handler
    $conversation->notes['idle_reason'] = $idleReason;
    $conversation->update();
    
    return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
}
```

## 🎯 KEY CHANGES

1. **Removed unnecessary `handleIdleFinalSelection()` call**
2. **Added direct confirmation generation** (same as working "Прочее")
3. **Added proper idle reason formatting** using `getCommissioningIdleReason()`
4. **Added project selection fallback** (if needed)
5. **Used standard idle confirmation callback** format

## ✅ EXPECTED RESULT

Now the idle custom comment flow works **identically** to the working "Прочее" equipment button:

```
1. User clicks "Свой комментарий"
2. User enters "Ожидание материалов"
3. System formats as "Прочее (Ожидание материалов)"
4. System shows confirmation with "Да/Нет" buttons ✅
5. User clicks "Да"
6. Report generated successfully ✅
```

## 🧪 TESTING

Test the complete flow:
1. Navigate to idle menu → "Свой комментарий"
2. Enter custom text (e.g., "Ожидание материалов")
3. **SHOULD NOW SEE**: Confirmation message with formatted reason and action buttons
4. Click "Да"
5. **SHOULD SEE**: Success report with custom reason

## 🎉 STATUS: CRITICAL FIX APPLIED

The idle custom comment functionality should now work perfectly, processing exactly like the proven working "Прочее" equipment button.

**Готово к тестированию!**