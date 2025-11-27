# Installing PhpSpreadsheet for Excel Generation

## Option 1: Using Composer (Recommended)

### Step 1: Install Composer (if not already installed)

**Download Composer:**
1. Go to https://getcomposer.org/download/
2. Download and run `Composer-Setup.exe`
3. Follow installation wizard
4. Restart command prompt/PowerShell

### Step 2: Install PhpSpreadsheet

```bash
cd c:\ozna_bot
composer require phpoffice/phpspreadsheet
```

This will:
- Download PhpSpreadsheet library
- Install all dependencies
- Update `composer.json`
- Create autoload files

### Step 3: Verify Installation

```bash
cd c:\ozna_bot
php -r "require 'vendor/autoload.php'; use PhpOffice\PhpSpreadsheet\Spreadsheet; echo 'PhpSpreadsheet installed successfully!';"
```

## Option 2: Manual Installation (Without Composer)

If you can't use Composer:

### Step 1: Download PhpSpreadsheet

1. Go to https://github.com/PHPOffice/PhpSpreadsheet/releases
2. Download the latest release ZIP
3. Extract to `c:\ozna_bot\vendor\phpoffice\phpspreadsheet\`

### Step 2: Download Dependencies

PhpSpreadsheet requires these libraries:
- `psr/simple-cache`
- `psr/http-client`
- `psr/http-factory`
- `maennchen/zipstream-php`

Download each from GitHub and place in `vendor` folder.

### Step 3: Create Autoloader

This is complex - **Composer is strongly recommended** instead.

## Option 3: Alternative Solution (No Dependencies)

Use the Python script approach:
1. Bot generates CSV (current implementation)
2. PHP calls Python script via `exec()`
3. Python generates Excel file
4. Bot sends both files

**Advantages:**
- No PHP dependencies
- Simpler installation
- Already tested and working

**Implementation:**

```php
// In ProjectHandler.php
private function convertCsvToExcel(string $csvPath): ?string
{
    $pythonScript = __DIR__ . '/../../convert_to_excel.py';
    $command = "python \"$pythonScript\" \"$csvPath\"";
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0) {
        // Find generated Excel file
        $excelPath = str_replace('.csv', '.xlsx', $csvPath);
        return $excelPath;
    }
    
    return null;
}
```

## Recommendation

**For production use:** Install Composer + PhpSpreadsheet (Option 1)

**For quick testing:** Use Python script (Option 3)

## Next Steps

After installing PhpSpreadsheet:
1. Run: `composer require phpoffice/phpspreadsheet`
2. I'll integrate Excel generation into the bot
3. Bot will send both CSV + Excel files

Let me know which option you prefer!
