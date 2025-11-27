# Export Security System - Implementation Guide

## 🔒 Overview

This system provides comprehensive data protection for CSV exports with:
1. **Export Audit Log** - Complete tracking of all exports
2. **Personal Export Keys** - Password protection for exported files
3. **Super Admin Override** - Reset forgotten passwords
4. **Access Level Control** - Permission-based export access

## 🎯 Features

### 1. Export Audit Log
- Tracks who exported data
- Records what was exported (type, scope, project)
- Logs when exports occurred
- Counts records in each export
- Stores period/filter information

### 2. Personal Export Keys (Passwords)
- Each user sets their own export key
- Minimum 4 characters required
- Used to password-protect CSV exports
- Stored securely in database
- Can be changed by user

### 3. Super Admin Override
- Super admins can reset any user's export key
- Generates secure random keys
- Notifies user of reset
- Full audit trail maintained

### 4. Access Control
- Guest (0): No export access
- User (1+): Can export data
- Export keys required for all users

## 📊 Database Schema

### New Table: `export_logs`

```sql
CREATE TABLE export_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    user_fio VARCHAR(255) NOT NULL,
    export_type VARCHAR(50) NOT NULL,      -- 'report' or 'notes'
    export_scope VARCHAR(100),              -- 'project_XXX', 'general', 'simple'
    project_code VARCHAR(50),
    period_start DATE,
    period_end DATE,
    status_filter VARCHAR(20),              -- For notes: 'active', 'closed', 'all'
    records_count INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    exported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_export_type (export_type),
    INDEX idx_exported_at (exported_at),
    INDEX idx_project_code (project_code)
);
```

### Updated Field: `ozna_data.data` JSON

New fields added to user data JSON:
```json
{
  "fio": "User Full Name",
  "functional_direction": "Department",
  "access_level": 1,
  "export_key": "user_password_123",
  "export_key_set_at": "2025-10-28 10:30:00"
}
```

## 🔧 Implementation Files

### 1. Database Migration
**File:** `database/migrations/add_export_security.sql`
- Creates `export_logs` table
- Documents new JSON fields

### 2. Database Service Updates
**File:** `CustomCommands/Services/DatabaseService.php`

New methods:
- `getUserExportKey(userId)` - Get user's export key
- `setUserExportKey(userId, key)` - Set/update export key
- `verifyUserExportKey(userId, key)` - Verify key matches
- `logExport(...)` - Log export operation
- `getUserExportLogs(userId)` - Get user's export history
- `getAllExportLogs()` - Get all exports (admin)
- `getExportStats()` - Get export statistics

### 3. Export Security Service
**File:** `CustomCommands/Services/ExportSecurityService.php`

Main service for export security:
- Export key management
- CSV password protection
- Audit logging
- Access control checks
- Export history formatting

### 4. Export Key Handler
**File:** `CustomCommands/Handlers/ExportKeyHandler.php`

UI handler for user interactions:
- Export key menu
- Key setup wizard
- Key change process
- Export history display
- Admin key reset

## 🚀 Usage Flow

### First-Time Setup (User)

```
1. User opens Profile menu
2. Selects "🔐 Кодовое слово для выгрузки"
3. System shows: No export key set
4. User clicks "✅ Установить кодовое слово"
5. System suggests random secure key
6. User enters key (or uses suggested)
7. System confirms: "Кодовое слово установлено"
```

### Exporting Data (User)

```
1. User requests export (Reports or Notes)
2. System checks:
   ✓ User has export_key set?
   ✓ User access level >= 1?
3. System prompts: "Введите кодовое слово"
4. User enters export key
5. System verifies key
6. System generates CSV
7. System password-protects CSV with user's key
8. System logs export operation
9. System sends protected ZIP file + key reminder
10. User downloads and opens with their key
```

### Changing Export Key (User)

```
1. User: Profile → 🔐 Кодовое слово
2. Click "🔄 Изменить кодовое слово"
3. Enter new key (min 4 chars)
4. System confirms change
5. All future exports use new key
```

### Viewing Export History (User)

```
1. User: Profile → 🔐 Кодовое слово
2. Click "📋 История выгрузок"
3. System shows:
   - Date/time of each export
   - Type (Report/Notes)
   - Scope (project/general)
   - Records count
```

### Resetting Export Key (Super Admin)

```
1. Super Admin: Админка → User Management
2. Select target user
3. Click "🔑 Сбросить кодовое слово"
4. System generates new random key
5. System notifies both admin and user
6. User receives new key
```

## 🔌 Integration Points

### ProjectHandler.php

Before generating export, add:

```php
// Check export access
$exportSecurityService = new ExportSecurityService($this->databaseService);

if (!$exportSecurityService->canUserExport($userId)) {
    $message = $exportSecurityService->getExportPermissionMessage($userId);
    return $this->messageService->sendMessage($chatId, $message);
}

// Check export key is set
if (!$exportSecurityService->hasExportKey($userId)) {
    $text = "⚠️ Сначала установите кодовое слово в профиле.";
    return $this->messageService->sendMessage($chatId, $text);
}

// Prompt for export key
$conversation->notes['awaiting_export_key_verify'] = true;
$conversation->notes['export_pending_type'] = 'notes'; // or 'report'
$conversation->notes['export_pending_params'] = [...];
$conversation->update();

$text = "🔐 Введите ваше кодовое слово для выгрузки:";
return $this->messageService->sendMessage($chatId, $text);
```

### GenericmessageCommand.php

Add export key verification:

```php
if (isset($conversation->notes['awaiting_export_key_verify']) 
    && $conversation->notes['awaiting_export_key_verify']) {
    
    $exportSecurityService = new ExportSecurityService($this->databaseService);
    
    if ($exportSecurityService->verifyExportKey($userId, $userInput)) {
        // Key correct - proceed with export
        $conversation->notes['awaiting_export_key_verify'] = false;
        $exportType = $conversation->notes['export_pending_type'];
        $params = $conversation->notes['export_pending_params'];
        $conversation->update();
        
        // Call appropriate export method
        // ...generate CSV...
        
        // Password-protect CSV
        $protectedPath = $exportSecurityService->protectCsvWithPassword(
            $csvPath, 
            $exportSecurityService->getUserExportKey($userId)
        );
        
        // Log export
        $exportSecurityService->logExport(
            $userId,
            $exportType,
            $scope,
            $recordsCount,
            basename($protectedPath),
            $projectCode,
            $periodStart,
            $periodEnd,
            $statusFilter
        );
        
        // Send file
        $this->sendZipFile($chatId, basename($protectedPath));
        
        $text = "✅ Выгрузка завершена!\n\n";
        $text .= "🔐 Пароль: <code>{$userExportKey}</code>\n\n";
        $text .= "Используйте ваше кодовое слово для открытия файла.";
        
        return $this->messageService->sendMessage($chatId, $text);
        
    } else {
        // Wrong key
        $text = "❌ Неверное кодовое слово.\n\nПопробуйте снова:";
        return $this->messageService->sendMessage($chatId, $text);
    }
}
```

## 📋 Admin Features

### View All Export Logs

```php
$exportSecurityService = new ExportSecurityService($this->databaseService);
$logs = $exportSecurityService->getAllExportLogs(100, 0);

$text = "📊 <b>Журнал выгрузок</b>\n\n";
foreach ($logs as $log) {
    $text .= "👤 {$log['user_fio']}\n";
    $text .= "📅 " . date('d.m.Y H:i', strtotime($log['exported_at'])) . "\n";
    $text .= "📁 {$log['export_type']} - {$log['export_scope']}\n";
    $text .= "📝 Записей: {$log['records_count']}\n";
    $text .= "━━━━━━━━━━━━━━━\n\n";
}
```

### Export Statistics

```php
$stats = $exportSecurityService->getExportStats();

$text = "📈 <b>Статистика выгрузок (30 дней)</b>\n\n";
foreach ($stats as $stat) {
    $text .= "📅 {$stat['export_date']}\n";
    $text .= "📊 {$stat['export_type']}: {$stat['total_exports']} выгрузок\n";
    $text .= "👥 Пользователей: {$stat['unique_users']}\n";
    $text .= "📝 Всего записей: {$stat['total_records']}\n\n";
}
```

## 🔒 Security Best Practices

### Export Key Requirements
- Minimum 4 characters
- Maximum 50 characters
- Any combination of letters/numbers
- Case-sensitive

### Password Protection
- Uses AES-256 encryption for ZIP files
- PHP ZipArchive with password support
- Original CSV deleted after protection

### Audit Trail
- Cannot be deleted by users
- Only super admins can view all logs
- Permanent record of all exports
- Indexed for fast queries

## 📱 User Interface Updates

### Profile Menu
Add new option:

```
👤 Профиль

├─ 📝 Моё ФИО
├─ 🏢 Моё направление  
├─ 🔐 Кодовое слово для выгрузки  ← NEW
└─ ⬅️ Назад
```

### Export Key Menu (No Key Set)

```
🔐 Кодовое слово для выгрузки

⚠️ У вас не установлено кодовое слово.

Кодовое слово необходимо для защиты выгрузок отчётов и заметок.

Без кодового слова вы не сможете выгружать данные.

[✅ Установить кодовое слово]
[⬅️ Назад к профилю]
```

### Export Key Menu (Key Set)

```
🔐 Кодовое слово для выгрузки

У вас установлено кодовое слово.

Это кодовое слово защищает ваши выгрузки отчётов и заметок.

Выберите действие:

[🔄 Изменить кодовое слово]
[📋 История выгрузок]
[⬅️ Назад к профилю]
```

## 🧪 Testing Checklist

- [ ] Database migration runs successfully
- [ ] Users can set export keys
- [ ] Export key validation works (min/max length)
- [ ] CSV password protection creates valid ZIP files
- [ ] Password-protected ZIPs open correctly
- [ ] Export logging captures all operations
- [ ] Users can view their export history
- [ ] Super admins can reset user keys
- [ ] Access level checks prevent guest exports
- [ ] Key verification rejects wrong passwords
- [ ] Export stats calculate correctly

## 📝 Migration Steps

1. **Run Database Migration**
   ```bash
   mysql -h 82.146.42.213 -u WizardJIOCb -p tg_bot < database/migrations/add_export_security.sql
   ```

2. **Deploy New Files**
   - DatabaseService.php (updated)
   - ExportSecurityService.php (new)
   - ExportKeyHandler.php (new)

3. **Update Profile Menu**
   - Add export key management option
   - Integrate ExportKeyHandler

4. **Update Export Flows**
   - ProjectHandler: Add key verification
   - GenericmessageCommand: Handle key input
   - Add password protection to CSV generation

5. **Test All Flows**
   - User setup
   - Export with key
   - Key change
   - Admin reset

## 🎓 User Documentation

Create user guide:
- How to set export key
- How to export data
- How to change key
- What to do if forgotten
- Security tips

## ⚡ Performance Notes

- Export logs indexed for fast queries
- JSON field updates use optimized queries
- ZIP creation minimal overhead
- Audit queries paginated

## 🔐 Security Notes

- Export keys stored as plain text (for ZIP password)
- Consider hashing in future version
- Audit log immutable by design
- Super admin access strictly controlled

---

**Status:** ✅ Ready for Integration
**Version:** 1.0
**Date:** 2025-10-28
