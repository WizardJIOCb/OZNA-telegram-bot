# 🚀 Export Security System - Integration Status

## ✅ Completed Steps

### 1. Database Schema ✅
**File:** `database/migrations/add_export_security.sql`
- Created `export_logs` table for audit trail
- Documented new JSON fields in `ozna_data`

**Next:** Run migration to create table

### 2. Core Services ✅
**Files Created:**
- `CustomCommands/Services/ExportSecurityService.php` - Main security logic
- `CustomCommands/Handlers/ExportKeyHandler.php` - UI handler

**Files Updated:**
- `CustomCommands/Services/DatabaseService.php` - Added 9 export security methods

### 3. Profile Integration ✅
**File:** `CustomCommands/Handlers/ProfileHandler.php`

**Changes:**
- ✅ Added imports for ExportSecurityService and ExportKeyHandler
- ✅ Initialize services in constructor
- ✅ Created `showProfileMenu()` with export key status
- ✅ Added callback handlers for:
  - `export_key_menu` - Show export key management menu
  - `export_key_setup` / `export_key_change` - Start key setup
  - `export_key_history` - Show export history

**Result:** Users can now access export key management from Profile menu!

### 4. Message Handler Integration ✅  
**File:** `CustomCommands/GenericmessageCommand.php`

**Changes:**
- ✅ Added imports for ExportSecurityService and ExportKeyHandler
- ✅ Initialize services in `initializeServices()`
- ✅ Added `isAwaitingExportKey()` check method
- ✅ Added handler call: `exportKeyHandler->handleExportKeyInput()`

**Result:** Users can input export keys and system validates them!

## 🔧 Remaining Integration Steps

### Step 5: Run Database Migration
```bash
mysql -h 82.146.42.213 -u WizardJIOCb -p tg_bot < database/migrations/add_export_security.sql
```

**What it does:**
- Creates `export_logs` table
- Sets up indexes for fast queries
- Ready to log exports

**Estimated time:** 1 minute

### Step 6: Add Export Security to ProjectHandler
**File:** `CustomCommands/Handlers/ProjectHandler.php`

**What to add:**

1. **Import statements:**
```php
use Longman\TelegramBot\Commands\SystemCommands\Services\ExportSecurityService;
```

2. **Initialize service in constructor:**
```php
private ExportSecurityService $exportSecurityService;

public function __construct(...) {
    $this->exportSecurityService = new ExportSecurityService($this->databaseService);
}
```

3. **Before EVERY export method, add security check:**

```php
// Example for notes export
private function generateNotesExport(...) {
    // CHECK 1: Can user export?
    if (!$this->exportSecurityService->canUserExport($userId)) {
        $message = $this->exportSecurityService->getExportPermissionMessage($userId);
        return $this->messageService->sendMessage($chatId, $message);
    }
    
    // CHECK 2: Does user have export key?
    if (!$this->exportSecurityService->hasExportKey($userId)) {
        $text = "⚠️ <b>Кодовое слово не установлено</b>\n\n";
        $text .= "Установите кодовое слово в профиле для выгрузки данных.\n\n";
        $text .= "Профиль → 🔐 Кодовое слово";
        
        $keyboard = [
            [['text' => '🔐 Установить сейчас', 'callback_data' => 'export_key_setup']],
            [['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты']]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    // CHECK 3: Prompt for export key verification
    $conversation->notes['awaiting_export_key_verify'] = true;
    $conversation->notes['export_pending_type'] = 'notes'; // or 'report'
    $conversation->notes['export_pending_params'] = [
        'projectCode' => $projectCode,
        'statusFilter' => $statusFilter,
        'period' => $period
    ];
    $conversation->update();
    
    $text = "🔐 <b>Введите кодовое слово</b>\n\nДля безопасности введите ваше кодовое слово:";
    return $this->messageService->sendMessage($chatId, $text);
}
```

4. **Methods to protect:**
- `generateNotesExport()` - Notes export
- `generateNotesExportAll()` - All notes export
- `generateNotesExportCustom()` - Custom period notes
- `generateProjectReport()` - Project reports
- `generateSimpleReport()` - Simple reports
- Any other CSV export methods

**Estimated time:** 30 minutes

### Step 7: Add Export Key Verification to GenericmessageCommand
**File:** `CustomCommands/GenericmessageCommand.php`

**What to add:**

```php
// After the existing export key input check, add verification handler
if (isset($conversation->notes['awaiting_export_key_verify']) 
    && $conversation->notes['awaiting_export_key_verify']) {
    
    // Verify the key
    if ($this->exportSecurityService->verifyExportKey($userId, $text)) {
        // Key is correct! Proceed with export
        
        $conversation->notes['awaiting_export_key_verify'] = false;
        $exportType = $conversation->notes['export_pending_type'];
        $params = $conversation->notes['export_pending_params'];
        $conversation->update();
        
        // Route to appropriate export handler based on type
        if ($exportType === 'notes') {
            // Call notes export with params
            $projectHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler(
                $this->messageService,
                $this->databaseService
            );
            
            // Generate CSV
            $notes = $projectHandler->getProjectNotes(
                $params['projectCode'] ?? null,
                $params['startDate'] ?? null,
                $params['endDate'] ?? date('Y-m-d'),
                $params['statusFilter'] ?? 'all'
            );
            
            $csvPath = $projectHandler->createNotesExportCsv(
                $params['projectCode'] ?? null,
                $notes,
                $params['period'] ?? 'custom',
                $params['statusFilter'] ?? 'all'
            );
            
            // Protect with password
            $exportKey = $this->exportSecurityService->getUserExportKey($userId);
            $protectedPath = $this->exportSecurityService->protectCsvWithPassword(
                __DIR__ . '/../../Uploads/' . $csvPath,
                $exportKey
            );
            
            // Log export
            $this->exportSecurityService->logExport(
                $userId,
                'notes',
                $params['projectCode'] ?? 'general',
                count($notes),
                basename($protectedPath),
                $params['projectCode'] ?? null,
                $params['startDate'] ?? null,
                $params['endDate'] ?? null,
                $params['statusFilter'] ?? null
            );
            
            // Send protected file
            $projectHandler->sendZipFile($chatId, basename($protectedPath));
            
            $text = "✅ <b>Выгрузка завершена</b>\n\n";
            $text .= "🔐 Пароль для открытия: <code>{$exportKey}</code>\n\n";
            $text .= "Используйте ваше кодовое слово для открытия ZIP-архива.";
            
            return $this->messageService->sendMessage($chatId, $text);
        }
        // Similar for 'report' type...
        
    } else {
        // Wrong key
        $text = "❌ <b>Неверное кодовое слово</b>\n\n";
        $text .= "Попробуйте снова или обратитесь к суперадминистратору для сброса.";
        
        $keyboard = [
            [['text' => '🔄 Попробовать снова', 'callback_data' => 'retry_export']],
            [['text' => '⬅️ Отмена', 'callback_data' => 'category_Проекты']]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
}
```

**Estimated time:** 45 minutes

### Step 8: Add sendZipFile Method to ProjectHandler
**File:** `CustomCommands/Handlers/ProjectHandler.php`

```php
/**
 * Send ZIP file to user (for password-protected exports)
 */
private function sendZipFile(string $chatId, string $fileName, string $caption = '📦 Защищённая выгрузка'): bool
{
    $filePath = __DIR__ . '/../../Uploads/' . $fileName;
    
    if (!file_exists($filePath)) {
        error_log("ProjectHandler: ZIP file not found: {$filePath}");
        return false;
    }
    
    try {
        $result = \Longman\TelegramBot\Request::sendDocument([
            'chat_id' => $chatId,
            'document' => \Longman\TelegramBot\Request::encodeFile($filePath),
            'caption' => $caption
        ]);
        
        // Clean up file after sending
        if ($result->isOk()) {
            unlink($filePath);
        }
        
        return $result->isOk();
    } catch (\Exception $e) {
        error_log("ProjectHandler: Error sending ZIP file: " . $e->getMessage());
        return false;
    }
}
```

**Estimated time:** 5 minutes

## 📋 Testing Checklist

After integration, test these scenarios:

### User Flow Tests
- [ ] User opens Profile → sees export key status
- [ ] User clicks "🔐 Кодовое слово для выгрузки"
- [ ] System shows "No key set" message
- [ ] User clicks "✅ Установить кодовое слово"
- [ ] System suggests random secure key
- [ ] User enters key (min 4 chars)
- [ ] System confirms: "✅ Кодовое слово установлено"
- [ ] User returns to Profile → sees "✅ Установлено"

### Export Flow Tests
- [ ] User requests notes export
- [ ] System checks: Has export key?
- [ ] System prompts: "Введите кодовое слово"
- [ ] User enters correct key
- [ ] System generates CSV
- [ ] System creates password-protected ZIP
- [ ] System logs to export_logs table
- [ ] User receives ZIP file + password reminder
- [ ] User can open ZIP with their key

### Security Tests
- [ ] Guest (level 0) cannot export
- [ ] User without key cannot export
- [ ] Wrong key is rejected
- [ ] Correct key allows export
- [ ] Export logged in database

### Admin Tests
- [ ] Super admin can view all export logs
- [ ] Super admin can reset user's export key
- [ ] User receives notification of reset
- [ ] User can export with new key

## 📊 Current Status

| Component | Status | Time Estimate |
|-----------|--------|---------------|
| Database Schema | ✅ Created | - |
| Core Services | ✅ Complete | - |
| Profile Integration | ✅ Complete | - |
| Message Handler | ✅ Complete | - |
| Database Migration | ⏳ Pending | 1 min |
| ProjectHandler Security | ⏳ Pending | 30 min |
| Export Verification | ⏳ Pending | 45 min |
| Send ZIP Method | ⏳ Pending | 5 min |
| Testing | ⏳ Pending | 30 min |

**Total Remaining:** ~2 hours

## 🎯 Next Action

**Run the database migration:**
```bash
mysql -h 82.146.42.213 -u WizardJIOCb -p tg_bot < database/migrations/add_export_security.sql
```

Then continue with Step 6 (ProjectHandler integration).

---

**Created:** 2025-10-28
**Status:** 50% Complete - Core infrastructure ready, integration in progress
