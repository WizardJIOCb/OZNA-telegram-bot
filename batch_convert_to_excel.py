# -*- coding: utf-8 -*-
"""
Batch CSV to Excel Converter
Processes all CSV files in the current directory
"""
import os
import glob
import pandas as pd
from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill, Alignment
from openpyxl.utils.dataframe import dataframe_to_rows
from openpyxl.worksheet.table import Table, TableStyleInfo
from datetime import datetime

def convert_csv_to_excel(csv_path):
    """Convert a single CSV file to styled Excel"""
    try:
        # Read CSV
        df = pd.read_csv(csv_path, sep=',', quotechar='"', encoding='utf-8-sig',
                         dtype=str, keep_default_na=False)
        
        # Format dates
        if 'Дата' in df.columns:
            df['Дата'] = pd.to_datetime(df['Дата'], format='%d.%m.%Y', errors='coerce') \
                          .dt.strftime('%d.%m.%Y')
        
        # Sort by date and employee
        if {'Дата', 'ФИО'}.issubset(df.columns):
            df['Дата_sort'] = pd.to_datetime(df['Дата'], format='%d.%m.%Y', errors='coerce')
            df = df.sort_values(['Дата_sort', 'ФИО']).drop(columns='Дата_sort')
            df.insert(df.columns.get_loc('Дата') + 1, 'Дата', df.pop('Дата'))
        
        # Create Excel
        wb = Workbook()
        ws = wb.active
        ws.title = "Табель"
        
        # Headers
        headers = list(df.columns)
        for c, h in enumerate(headers, 1):
            cell = ws.cell(row=1, column=c, value=h)
            cell.font = Font(bold=True, color="FFFFFF")
            cell.fill = PatternFill("solid", fgColor="1f4e79")
            cell.alignment = Alignment(horizontal="center", vertical="center")
        
        # Data
        for r, row in enumerate(dataframe_to_rows(df, index=False, header=False), start=2):
            for c, val in enumerate(row, 1):
                cell = ws.cell(row=r, column=c, value=val)
                if isinstance(val, str) and 'Простой' in val:
                    cell.font = Font(color="FF0000", bold=True)
        
        # Table with filters
        tab = Table(displayName="Tab", ref=f"A1:{chr(64+len(headers))}{len(df)+1}")
        style = TableStyleInfo(name="TableStyleMedium9", showRowStripes=True)
        tab.tableStyleInfo = style
        ws.add_table(tab)
        
        # Auto-width
        for col in ws.columns:
            max_len = 0
            column = col[0].column_letter
            for cell in col:
                try:
                    if len(str(cell.value)) > max_len:
                        max_len = len(str(cell.value))
                except:
                    pass
            ws.column_dimensions[column].width = min(max_len + 2, 50)
        
        # Save
        out_name = csv_path.replace('.csv', '.xlsx')
        wb.save(out_name)
        
        return True, out_name
        
    except Exception as e:
        return False, str(e)

# -------------------------------------------------
# Main batch processing
if __name__ == "__main__":
    print("=" * 60)
    print("BATCH CSV TO EXCEL CONVERTER")
    print("=" * 60)
    print()
    
    # Find all CSV files matching pattern
    csv_files = glob.glob("simple_report_*.csv")
    
    if not csv_files:
        print("❌ No CSV files found matching pattern: simple_report_*.csv")
        print("   Make sure CSV files are in the same directory as this script.")
        input("\nPress Enter to exit...")
        exit()
    
    print(f"Found {len(csv_files)} CSV file(s) to process:")
    for f in csv_files:
        print(f"  • {f}")
    print()
    
    # Process each file
    processed = 0
    failed = 0
    
    for csv_file in csv_files:
        print(f"Processing: {csv_file}...", end=" ")
        success, result = convert_csv_to_excel(csv_file)
        
        if success:
            print(f"✅ Created: {result}")
            processed += 1
        else:
            print(f"❌ Error: {result}")
            failed += 1
    
    # Summary
    print()
    print("=" * 60)
    print("BATCH PROCESSING COMPLETE")
    print("=" * 60)
    print(f"✅ Successfully processed: {processed} file(s)")
    if failed > 0:
        print(f"❌ Failed: {failed} file(s)")
    print()
    
    input("Press Enter to exit...")
