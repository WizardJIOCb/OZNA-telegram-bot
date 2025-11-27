# Fix for "Заявки на доступ" Button Issue

## Problem Description
The "Заявки на доступ" (Access Requests) button in the Telegram bot admin panel was not working - nothing happened when clicked. Users wanted to see access requests from other users.

## Root Cause Analysis
1. The AdminService was configured to use [access_requests_db.json](file://c:\ozna_bot\data\access_requests_db.json) as the file for storing access requests
2. However, the actual access requests were being stored in [access_requests.json](file://c:\ozna_bot\data\access_requests.json)
3. The [access_requests_db.json](file://c:\ozna_bot\data\access_requests_db.json) file was empty (0KB), while [access_requests.json](file://c:\ozna_bot\data\access_requests.json) contained 18 access requests
4. When the database was not available (MySQLi extension missing), the AdminService fell back to the wrong file

## Solution
Updated the AdminService constructor to use the correct file path:

**File**: [CustomCommands/Services/AdminService.php](file://c:\ozna_bot\CustomCommands\Services\AdminService.php)
**Line**: 26

**Before**:
```php
$this->accessRequestsFile = __DIR__ . '/../../data/access_requests_db.json';
```

**After**:
```php
$this->accessRequestsFile = __DIR__ . '/../../data/access_requests.json';
```

## Verification
1. Created test scripts to verify the fix
2. Confirmed that the AdminService now correctly falls back to [access_requests.json](file://c:\ozna_bot\data\access_requests.json) when database is unavailable
3. Verified that all 18 access requests are now accessible through the AdminService

## Result
The "Заявки на доступ" button in the admin panel should now work correctly and display the list of access requests from users.