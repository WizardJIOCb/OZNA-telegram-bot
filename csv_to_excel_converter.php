<?php
/**
 * CSV to Excel Converter (Standalone Script)
 * Can be called from ProjectHandler to convert CSV reports to Excel
 * 
 * Usage: php csv_to_excel_converter.php <csv_file_path>
 * Returns: Path to generated Excel file or empty string on error
 */

// Check if PhpSpreadsheet is available
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    error_log("CSV to Excel Converter: vendor/autoload.php not found. PhpSpreadsheet not installed.");
    echo "";
    exit(1);
}

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * Convert CSV file to styled Excel file
 */
function convertCsvToExcel(string $csvPath): ?string
{
    if (!file_exists($csvPath)) {
        error_log("CSV to Excel Converter: CSV file not found: $csvPath");
        return null;
    }
    
    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Табель');
        
        $rowNumber = 1;
        $handle = fopen($csvPath, 'r');
        
        if ($handle === false) {
            error_log("CSV to Excel Converter: Failed to open CSV file: $csvPath");
            return null;
        }
        
        $isFirstRow = true;
        
        while (($row = fgetcsv($handle, 10000, ',', '"')) !== false) {
            // Skip empty rows
            if (count(array_filter($row)) == 0) {
                continue;
            }
            
            if ($isFirstRow) {
                // Headers - bold, white text, dark blue background
                foreach ($row as $col => $value) {
                    $cell = $sheet->getCellByColumnAndRow($col + 1, $rowNumber);
                    $cell->setValue($value);
                    
                    // Styling
                    $cell->getStyle()->getFont()
                        ->setBold(true)
                        ->setColor(new Color('FFFFFF'));
                    
                    $cell->getStyle()->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FF1f4e79'); // Dark blue
                    
                    $cell->getStyle()->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }
                
                $isFirstRow = false;
                $rowNumber++;
                continue;
            }
            
            // Data rows
            foreach ($row as $col => $value) {
                $cell = $sheet->getCellByColumnAndRow($col + 1, $rowNumber);
                $cell->setValue($value);
                
                // Red bold text for "Простой" (idle time)
                if (is_string($value) && stripos($value, 'Простой') !== false) {
                    $cell->getStyle()->getFont()
                        ->setColor(new Color('FFFF0000')) // Red
                        ->setBold(true);
                }
            }
            
            $rowNumber++;
        }
        
        fclose($handle);
        
        // Auto-size columns (max 50 characters)
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Add auto-filter
        if ($rowNumber > 1) {
            $sheet->setAutoFilter('A1:' . $sheet->getHighestColumn() . ($rowNumber - 1));
        }
        
        // Save as Excel file
        $excelPath = str_replace('.csv', '.xlsx', $csvPath);
        $writer = new Xlsx($spreadsheet);
        $writer->save($excelPath);
        
        error_log("CSV to Excel Converter: Successfully created Excel file: $excelPath");
        return $excelPath;
        
    } catch (Exception $e) {
        error_log("CSV to Excel Converter: Error converting CSV to Excel: " . $e->getMessage());
        return null;
    }
}

// Command-line usage
if (php_sapi_name() === 'cli') {
    if ($argc < 2) {
        echo "Usage: php csv_to_excel_converter.php <csv_file_path>\n";
        exit(1);
    }
    
    $csvPath = $argv[1];
    $excelPath = convertCsvToExcel($csvPath);
    
    if ($excelPath) {
        echo $excelPath; // Return path to stdout
        exit(0);
    } else {
        echo ""; // Return empty on error
        exit(1);
    }
}

// When included as library
return [
    'convertCsvToExcel' => 'convertCsvToExcel'
];
