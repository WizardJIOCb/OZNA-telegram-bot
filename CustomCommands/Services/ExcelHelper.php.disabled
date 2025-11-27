<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Excel Helper - isolated PhpSpreadsheet wrapper
 * This file is loaded only when needed to avoid initialization errors
 */
class ExcelHelper
{
    /**
     * Check if PhpSpreadsheet is available
     */
    public static function isAvailable(): bool
    {
        return class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet');
    }
    
    /**
     * Generate Excel template
     */
    public static function generateTemplate(string $outputPath): string
    {
        if (!self::isAvailable()) {
            throw new \Exception('PhpSpreadsheet library not installed');
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Sheet 1: Main Info
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Основная информация');
        
        $headers1 = ['Шифр проекта', 'Наименование проекта', 'Заказчик', 'БН', 'ШМР План (часы)', 'ПНР План (часы)'];
        $sheet1->fromArray($headers1, null, 'A1');
        
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet1->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        foreach (range('A', 'F') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }
        
        $exampleData = [['ОИ999', 'Пример проекта', 'Водоканал СПб', 'АСН', 240, 160]];
        $sheet1->fromArray($exampleData, null, 'A2');
        
        // Sheet 2: SMR Milestones
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Вехи ШМР');
        $headers2 = ['Шифр проекта', 'Наименование вехи', 'Плановые часы'];
        $sheet2->fromArray($headers2, null, 'A1');
        $sheet2->getStyle('A1:C1')->applyFromArray($headerStyle);
        
        foreach (range('A', 'C') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }
        
        $smrMilestones = [
            ['ОИ999', 'Входной контроль', 20],
            ['ОИ999', 'Завоз оборудования на площадку', 40],
            ['ОИ999', 'Расстановка блоков', 80]
        ];
        $sheet2->fromArray($smrMilestones, null, 'A2');
        
        // Sheet 3: PNR Milestones  
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Вехи ПНР');
        $headers3 = ['Шифр проекта', 'Наименование вехи', 'Плановые часы'];
        $sheet3->fromArray($headers3, null, 'A1');
        $sheet3->getStyle('A1:C1')->applyFromArray($headerStyle);
        
        foreach (range('A', 'C') as $col) {
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }
        
        $pnrMilestones = [
            ['ОИ999', 'Электрооборудование', 60],
            ['ОИ999', 'Автоматизация', 50]
        ];
        $sheet3->fromArray($pnrMilestones, null, 'A2');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($outputPath);
        
        return $outputPath;
    }
    
    /**
     * Load Excel file and return data array
     */
    public static function loadExcel(string $filePath): array
    {
        if (!self::isAvailable()) {
            throw new \Exception('PhpSpreadsheet library not installed');
        }
        
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        
        $result = [
            'main' => [],
            'smr_milestones' => [],
            'pnr_milestones' => []
        ];
        
        // Read main info
        if ($spreadsheet->getSheetCount() > 0) {
            $sheet1 = $spreadsheet->getSheet(0);
            $result['main'] = $sheet1->rangeToArray('A2:F2', null, true, false)[0] ?? [];
        }
        
        // Read SMR milestones
        if ($spreadsheet->getSheetCount() > 1) {
            $sheet2 = $spreadsheet->getSheet(1);
            $highestRow = $sheet2->getHighestRow();
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet2->rangeToArray("A{$row}:C{$row}", null, true, false)[0];
                if (!empty($rowData[0])) {
                    $result['smr_milestones'][] = $rowData;
                }
            }
        }
        
        // Read PNR milestones
        if ($spreadsheet->getSheetCount() > 2) {
            $sheet3 = $spreadsheet->getSheet(2);
            $highestRow = $sheet3->getHighestRow();
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet3->rangeToArray("A{$row}:C{$row}", null, true, false)[0];
                if (!empty($rowData[0])) {
                    $result['pnr_milestones'][] = $rowData;
                }
            }
        }
        
        return $result;
    }
}
