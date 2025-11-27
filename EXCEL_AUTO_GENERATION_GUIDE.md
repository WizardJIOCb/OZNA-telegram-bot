# Excel Auto-Generation Implementation Guide

## Overview

This guide explains how to enable automatic Excel file generation in the bot, so users receive **both CSV and Excel files** when generating reports.

## Three Implementation Options

### Option 1: PhpSpreadsheet (Best Quality) ⭐ Recommended

**Pros:**
- ✅ Native PHP solution
- ✅ Best Excel compatibility
- ✅ Professional styling
- ✅ Fast execution

**Cons:**
- ❌ Requires Composer
- ❌ Additional dependency

**Setup:**
```bash
cd c:\ozna_bot
composer require phpoffice/phpspreadsheet
```

---

### Option 2: Python Script (Easiest Setup)

**Pros:**
- ✅ No PHP dependencies
- ✅ Already tested
- ✅ Works immediately

**Cons:**
- ❌ Requires Python on server
- ❌ External process call
- ❌ Slightly slower

**Setup:**
```bash
# Install Python packages
pip install pandas openpyxl

# Script already created: convert_to_excel.py
```

---

### Option 3: CSV Only (Current)

**Pros:**
- ✅ No dependencies
- ✅ Fast
- ✅ Universal format

**Cons:**
- ❌ Manual conversion needed
- ❌ No styling

---

## Recommended Implementation (Option 1)

### Step 1: Install PhpSpreadsheet

**Method A: Using Composer (if available)**
```bash
cd c:\ozna_bot
composer require phpoffice/phpspreadsheet
```

**Method B: Manual Download**
See [`INSTALL_PHPSPREADSHEET.md`](file://c:\ozna_bot\INSTALL_PHPSPREADSHEET.md) for manual installation.

### Step 2: Integrate into Bot

I'll modify `ProjectHandler.php` to add a helper method:

```php
/**
 * Convert CSV to Excel using PhpSpreadsheet
 */
private function convertCsvToExcelFile(string $csvPath): ?string
{
    $converterScript = __DIR__ . '/../../csv_to_excel_converter.php';
    
    if (!file_exists($converterScript)) {
        error_log("ProjectHandler: Excel converter script not found");
        return null;
    }
    
    // Call converter script
    $command = "php \"$converterScript\" \"$csvPath\"";
    $excelPath = shell_exec($command);
    
    if ($excelPath && file_exists(trim($excelPath))) {
        return trim($excelPath);
    }
    
    return null;
}
```

### Step 3: Update Report Generation

Modify the report sending flow to generate both files:

```php
// After creating CSV
$csvFileName = $this->createSimpleReportCsv($startDate, $endDate, $fn, $entries);
$csvPath = __DIR__ . '/../../Uploads/' . $csvFileName;

// Try to create Excel version
$excelPath = $this->convertCsvToExcelFile($csvPath);

// Send CSV file
$this->sendCsvFile($chatId, $csvFileName);

// Send Excel file if available
if ($excelPath) {
    $excelFileName = basename($excelPath);
    $this->sendExcelFile($chatId, $excelFileName);
}
```

### Step 4: Test

```bash
# Generate a test report
# Check if both files are sent
```

---

## Alternative: Quick Python Integration

If you prefer Python approach:

### Modified `convertCsvToExcelFile` method:

```php
private function convertCsvToExcelFile(string $csvPath): ?string
{
    $pythonScript = __DIR__ . '/../../convert_to_excel.py';
    
    if (!file_exists($pythonScript)) {
        error_log("ProjectHandler: Python converter script not found");
        return null;
    }
    
    // Create temp Python script that processes specific file
    $tempScript = sys_get_temp_dir() . '/convert_temp_' . uniqid() . '.py';
    $scriptContent = file_get_contents($pythonScript);
    
    // Replace csv_path line
    $csvFileName = basename($csvPath);
    $scriptContent = preg_replace(
        '/csv_path = .*/',
        'csv_path = "' . addslashes($csvPath) . '"',
        $scriptContent
    );
    
    file_put_contents($tempScript, $scriptContent);
    
    // Run Python script
    $command = "python \"$tempScript\" 2>&1";
    exec($command, $output, $returnCode);
    
    // Clean up temp script
    unlink($tempScript);
    
    if ($returnCode === 0) {
        // Find generated Excel file
        $excelPath = str_replace('.csv', '.xlsx', $csvPath);
        
        // Move to Uploads folder
        $excelFileName = str_replace('.csv', '.xlsx', basename($csvPath));
        $finalPath = __DIR__ . '/../../Uploads/' . $excelFileName;
        
        if (file_exists($excelPath)) {
            rename($excelPath, $finalPath);
            return $finalPath;
        }
    }
    
    return null;
}
```

---

## Configuration

Add a config option to enable/disable Excel generation:

```php
// In ProjectHandler.php constructor or config
private $enableExcelGeneration = true; // Set to false to disable

// In report generation
if ($this->enableExcelGeneration) {
    $excelPath = $this->convertCsvToExcelFile($csvPath);
    if ($excelPath) {
        $this->sendExcelFile($chatId, basename($excelPath));
    }
}
```

---

## Error Handling

```php
private function sendExcelFile(string $chatId, string $fileName): bool
{
    $filePath = __DIR__ . '/../../Uploads/' . $fileName;
    
    if (!file_exists($filePath)) {
        error_log("ProjectHandler: Excel file not found: {$filePath}");
        return false;
    }
    
    try {
        $result = \Longman\TelegramBot\Request::sendDocument([
            'chat_id' => $chatId,
            'document' => \Longman\TelegramBot\Request::encodeFile($filePath),
            'caption' => '📊 Отчёт Excel'
        ]);
        
        // Clean up file after sending
        if ($result->isOk()) {
            unlink($filePath);
        }
        
        return $result->isOk();
    } catch (\Exception $e) {
        error_log("ProjectHandler: Error sending Excel file: " . $e->getMessage());
        return false;
    }
}
```

---

## Testing Checklist

- [ ] PhpSpreadsheet installed (or Python available)
- [ ] Converter script exists and works
- [ ] CSV file generated correctly
- [ ] Excel file generated with styling
- [ ] Both files sent to user
- [ ] Files cleaned up after sending
- [ ] Error handling works

---

## Troubleshooting

### Excel file not generated

**Check:**
1. PhpSpreadsheet installed: `php -r "require 'vendor/autoload.php'; use PhpOffice\PhpSpreadsheet\Spreadsheet; echo 'OK';"`
2. Converter script exists: `ls csv_to_excel_converter.php`
3. PHP has write permissions in Uploads folder

### Both files not sent

**Check:**
1. `sendExcelFile()` method implemented
2. No errors in bot logs
3. File size within Telegram limits (50MB)

### Styling not applied

**Check:**
1. PhpSpreadsheet version (>= 1.18)
2. Excel file opens correctly in Excel
3. Colors defined correctly (ARGB format)

---

## Performance Considerations

- Excel generation adds ~1-3 seconds per report
- For large reports (1000+ rows), consider async processing
- Monitor server resources

---

## Next Steps

1. Choose implementation option (PhpSpreadsheet or Python)
2. Install dependencies
3. Let me know - I'll integrate into bot code
4. Test with sample report
5. Deploy to production

**Which option would you like to implement?**
