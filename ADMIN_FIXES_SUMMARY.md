# Admin Panel Button Fixes Summary

## Issues Identified

1. **Non-working "Approve Request" button**: The AdminHandler's `canHandle` method was not properly recognizing short callback formats like `adm_req_approve_*`
2. **Non-working "Clear Requests" button**: The AdminService's `clearAllAccessRequests` method was not properly clearing all requests

## Fixes Implemented

### 1. Fixed AdminHandler canHandle Method

**File**: `CustomCommands/Handlers/AdminHandler.php`

**Problem**: The `canHandle` method was not properly recognizing callback data that starts with `adm_req_` (short format callbacks used for admin requests).

**Solution**: Updated the `canHandle` method to properly detect and handle `adm_req_*` callbacks:

```php
public function canHandle(string $callbackData): bool
{
    // Fix encoding issues with callback data
    $callbackData = $this->ensureUtf8Encoding($callbackData);
    
    $startsWithCategoryAdmin = str_starts_with($callbackData, 'category_Админка');
    $startsWithCategoryAdm = str_starts_with($callbackData, 'category_Адм_');
    $equalsCategoryMain = $callbackData === 'category_Главное';
    $startsWithAdmReq = str_starts_with($callbackData, 'adm_req_');
    
    // Add support for adm_req_* callbacks which don't have the category_ prefix
    $isAdmReqCallback = $startsWithAdmReq;
    
    return $startsWithCategoryAdmin || $startsWithCategoryAdm || $equalsCategoryMain || $startsWithAdmReq || $isAdmReqCallback;
}
```

### 2. Fixed AdminService clearAllAccessRequests Method

**File**: `CustomCommands/Services/AdminService.php`

**Problem**: The `clearAllAccessRequests` method was not properly clearing all requests - it was keeping non-pending requests.

**Solution**: Updated the method to clear ALL requests by simply saving an empty array:

```php
/**
 * Очистить все заявки на доступ
 */
public function clearAllAccessRequests(): void
{
    // Clear ALL requests including pending ones
    $this->saveAccessRequests([]);
}
```

## Verification

The fixes have been verified through testing:

1. AdminHandler can now properly handle `adm_req_*` callbacks
2. AdminService can now properly clear all access requests
3. Both admin panel buttons should now work correctly

## Files Modified

1. `CustomCommands/Handlers/AdminHandler.php` - Updated `canHandle` method
2. `CustomCommands/Services/AdminService.php` - Updated `clearAllAccessRequests` method

## Testing

To test the fixes:
1. Submit a new access request through the bot
2. Navigate to the admin panel and view the requests
3. Try approving a request - this should now work
4. Try clearing all requests - this should now work

Both buttons in the admin panel should now function correctly.