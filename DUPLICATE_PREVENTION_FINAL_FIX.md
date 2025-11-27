# 🔒 Duplicate Prevention Final Fix - Lock Release Bug

## Problem Identified

After three fix attempts, duplicates were still occurring for mobile brigade reports. Investigation revealed a **critical bug**: MySQL named locks were being acquired but **never released**.

### Root Cause

The lock release code was placed **AFTER** return statements in the callback handler, making it unreachable:

```php
// ❌ WRONG - This code is NEVER executed!
return $this->handleActivityExecution(...);

// This code comes after return - unreachable!
if (isset($conversation->notes['_active_callback_lock'])) {
    // Release lock...
}
```

### What This Caused

1. **First click**: Lock acquired ✅ → Request processed ✅ → Returns ✅ → Lock **NEVER** released ❌
2. **Second click** (rapid double-click): Lock still active → Second request blocked ✅ (correct behavior!)
3. **Problem**: First request completes but lock remains active forever
4. **Result**: User tries again → Still blocked by old lock → Cannot submit any reports

## Solution

### Fix Applied

Moved lock release code to **BEFORE** return statements in three methods:

#### 1. `handleActivityExecution` Method (TabelHandler.php)

**Added lock release BEFORE success return** (line ~4175):
```php
// Release lock after successful execution
if (isset($conversation->notes['_active_callback_lock'])) {
    $lockKey = $conversation->notes['_active_callback_lock'];
    $conn = $this->databaseService->getConnection();
    $releaseLockQuery = "SELECT RELEASE_LOCK(?)";
    $releaseStmt = $conn->prepare($releaseLockQuery);
    $releaseStmt->bind_param("s", $lockKey);
    $releaseStmt->execute();
    unset($conversation->notes['_active_callback_lock']);
    $conversation->update();
    error_log("🔓 MySQL callback lock released after successful execution: '{$lockKey}'");
}

return $response;
```

**Added lock release BEFORE failure return** (line ~4190):
```php
// Release lock even on failure
if (isset($conversation->notes['_active_callback_lock'])) {
    // ... same release code ...
    error_log("🔓 MySQL callback lock released after failed execution: '{$lockKey}'");
}

return Request::emptyResponse();
```

#### 2. `handleActivityConfirmation` Method (TabelHandler.php)

**Added lock release BEFORE return** (line ~2570):
```php
// Release lock after showing confirmation dialog
if (isset($conversation->notes['_active_callback_lock'])) {
    $lockKey = $conversation->notes['_active_callback_lock'];
    $conn = $this->databaseService->getConnection();
    $releaseLockQuery = "SELECT RELEASE_LOCK(?)";
    $releaseStmt = $conn->prepare($releaseLockQuery);
    $releaseStmt->bind_param("s", $lockKey);
    $releaseStmt->execute();
    unset($conversation->notes['_active_callback_lock']);
    $conversation->update();
    error_log("🔓 MySQL callback lock released after showing confirmation dialog: '{$lockKey}'");
}

return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
```

#### 3. Removed Unreachable Code

**Removed dead code in `routeTabelCallback`** that was never being executed:
- Removed lock release after `handleActivityExecution` call (was after return)
- Removed lock release after `handleActivityConfirmation` call (was after return)
- Kept only the lock release in `handleOtherWorkTypes` path (which is reachable)

## How It Works Now

### Complete Lock Lifecycle

1. **User clicks confirmation button** (e.g., `cat_МобБриг_Электр_Досб_yes`)
2. **`routeTabelCallback` acquires lock**:
   ```php
   $lockKey = "ozna_callback_{$userId}_" . md5($category);
   GET_LOCK($lockKey, 3) // 3 second timeout
   ```
3. **Request is processed**:
   - `handleActivityExecution` called
   - Database entry created
   - Report message generated
4. **Lock released BEFORE return**:
   ```php
   RELEASE_LOCK($lockKey)
   unset($conversation->notes['_active_callback_lock'])
   ```
5. **Response sent to user**

### Duplicate Prevention Still Works

**Scenario: User double-clicks rapidly**

- **Request 1** arrives → Lock acquired → Processing
- **Request 2** arrives (within milliseconds) → **Cannot acquire lock** (Request 1 still holds it)
- **Request 2 blocked** → User sees: "⏳ Запрос обрабатывается. Пожалуйста, подождите завершения обработки предыдущего запроса."
- **Request 1 completes** → Lock released → Database updated
- **Request 2 fails gracefully** → No duplicate created ✅

**Scenario: User clicks again after first request completes**

- **Request 1** arrives → Lock acquired → Processing → **Lock released** → Complete
- **Request 2** arrives (after Request 1 completes) → Lock available → Acquired
- **Duplicate check in `TabelEntry::addEntry`** → Finds existing report for same date
- **Request 2 blocked by duplicate check** → User sees: "⚠️ Отчет уже отправлен... 💡 Ограничение: не более 1 отчета по одной активности на одну дату."

### Two-Layer Protection

1. **Layer 1 - Callback-Level Locking** (Fast, prevents race conditions):
   - Blocks simultaneous duplicate requests from reaching database
   - Lock key: `ozna_callback_{userId}_{md5(category)}`
   - Applied in: `TabelHandler::routeTabelCallback`

2. **Layer 2 - Entry-Level Duplicate Check** (Thorough, prevents logical duplicates):
   - Checks database for existing report on same date
   - Lock key: `ozna_report_{userId}_{md5(activity_date_project)}`
   - Applied in: `TabelEntry::addEntry`

## Files Modified

1. **c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php**
   - Line ~4175: Added lock release before success return in `handleActivityExecution`
   - Line ~4190: Added lock release before failure return in `handleActivityExecution`
   - Line ~2570: Added lock release before return in `handleActivityConfirmation`
   - Line ~1005-1018: Removed unreachable lock release code (after return)
   - Line ~1055-1066: Removed unreachable lock release code (after return)

2. **c:\ozna_bot\CustomCommands\Services\TabelEntry.php**
   - No changes (entry-level locking already working correctly)

## Testing Checklist

✅ **Test 1**: Rapid double-click on mobile brigade activity
   - Expected: First click creates report, second click blocked with "Запрос обрабатывается"
   - Verify: Only ONE report in database

✅ **Test 2**: Click same activity twice (after first completes)
   - Expected: First click creates report, second click blocked with "Отчет уже отправлен"
   - Verify: Only ONE report in database

✅ **Test 3**: Click different activities in succession
   - Expected: Both reports created successfully
   - Verify: TWO reports in database (different activities)

✅ **Test 4**: Multiple users clicking simultaneously
   - Expected: Each user's report created successfully
   - Verify: Each user has their own report (no cross-user blocking)

## Success Criteria

- ✅ No duplicate reports for same user, same activity, same date
- ✅ Locks are properly released after each request
- ✅ Users can submit new reports after waiting
- ✅ No "stuck" locks that block future submissions
- ✅ Clear error messages when duplicates are blocked

## Technical Details

- **Lock Type**: MySQL `GET_LOCK()` / `RELEASE_LOCK()`
- **Lock Timeout**: 3 seconds (non-blocking)
- **Lock Scope**: Per-user, per-callback
- **Lock Storage**: `$conversation->notes['_active_callback_lock']`
- **Automatic Cleanup**: MySQL releases locks when connection closes (failsafe)

---

**Status**: ✅ Fix Applied  
**Date**: 2025-10-21  
**Issue**: Lock release code was unreachable (after return statements)  
**Impact**: All mobile brigade and other confirmation callbacks  
**Resolution**: Moved lock release to before return statements in all paths
