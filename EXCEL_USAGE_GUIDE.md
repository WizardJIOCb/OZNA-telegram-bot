# Excel Usage Guide for New CSV Reports

## Opening the Report in Excel

### Method 1: Direct Open (Recommended)
1. Open Excel
2. Click **File → Open**
3. Select your CSV file (e.g., `simple_report_all_2025-10-25_16-13-32.csv`)
4. Excel will automatically detect UTF-8 encoding (BOM) and display correctly

### Method 2: Import Data
1. Open Excel
2. Go to **Data → Get Data → From File → From Text/CSV**
3. Select your CSV file
4. Excel will preview the data
5. Click **Load**

## Working with the Data

### The report has these columns:
```
Проект | Дата | Процесс | Вид деятельности | Основная активность | Тип работ | [2] | [3] | Фамилия
```

### Excel Features You Can Use:

#### 1. **Auto-Filters** (Click on header row)
- Filter by Project (Проект)
- Filter by Process (Процесс)
- Filter by Activity Type (Вид деятельности)
- Filter by Employee (Фамилия)

#### 2. **Pivot Tables**
Example: Count activities by employee and process
1. Select all data (Ctrl+A)
2. Insert → PivotTable
3. Drag **Фамилия** to Rows
4. Drag **Процесс** to Columns
5. Drag **Вид деятельности** to Values (Count)

#### 3. **Conditional Formatting**
Highlight idle time:
1. Select **Основная активность** column
2. Home → Conditional Formatting → Highlight Cell Rules → Text that Contains
3. Enter "Простой" → Format with red color

#### 4. **Sorting**
- Sort by Date (Дата) - ascending/descending
- Sort by Employee (Фамилия) - alphabetically
- Multi-level sorting (Date → Employee)

#### 5. **Charts**
Create activity distribution chart:
1. Select data
2. Insert → Recommended Charts
3. Choose Pie Chart or Bar Chart
4. Customize as needed

## Advanced Usage

### Formula Examples

**Count entries per employee:**
```excel
=COUNTIF(I:I, "Employee Name")
```

**Count specific activity type:**
```excel
=COUNTIF(D:D, "Работы по графику")
```

**Filter by date range:**
```excel
=COUNTIFS(B:B, ">=01.10.2025", B:B, "<=31.10.2025")
```

### Pivot Table Analysis

**Example 1: Activities by Process**
- Rows: Процесс
- Columns: Вид деятельности
- Values: Count

**Example 2: Work distribution by employee**
- Rows: Фамилия
- Columns: Процесс
- Values: Count of Дата

**Example 3: Project timeline**
- Rows: Проект
- Columns: Дата (grouped by week)
- Values: Count of activities

## Tips & Tricks

### 1. Freeze Header Row
1. Click on row 2
2. View → Freeze Panes → Freeze Top Row

### 2. Convert to Excel Table
1. Select all data (Ctrl+A)
2. Home → Format as Table
3. Choose a style
4. Check "My table has headers"

Benefits:
- Auto-expands when adding data
- Built-in filters on headers
- Easier to reference in formulas

### 3. Search & Replace
- Find all "Шеф-монтаж" entries: Ctrl+F
- Replace process names if needed: Ctrl+H

### 4. Export Filtered Data
1. Apply filters
2. Select visible cells only
3. Copy (Ctrl+C)
4. Paste to new sheet
5. Save as separate file

## Comparing with Grok's Python Script

### Advantages of Direct Excel Use:
✅ **No coding required** - just open and work
✅ **Interactive filtering** - click and filter instantly
✅ **Visual formatting** - colors, fonts, borders
✅ **Pivot tables** - powerful analysis tool
✅ **Charts** - visual representation
✅ **Formulas** - calculate on the fly

### When to Use Python Script:
- Need automated processing of multiple files
- Want custom formatting not possible in Excel
- Need to integrate with other systems
- Prefer programmatic approach

## Sample Workflow

### Weekly Report Preparation:
1. **Generate Report** (from bot)
   - Select "Простой отчёт"
   - Choose date range
   - Select "Все ФН" or specific department

2. **Open in Excel**
   - Double-click CSV file
   - Verify data loaded correctly

3. **Apply Filters**
   - Filter by specific project if needed
   - Filter out test entries

4. **Create Summary**
   - Insert Pivot Table
   - Group by employee and activity type
   - Add chart for visual representation

5. **Format & Share**
   - Apply conditional formatting for idle time
   - Save as .xlsx file
   - Share with management

## Troubleshooting

### Issue: Russian characters display incorrectly
**Solution**: The CSV file already has UTF-8 BOM. If still having issues:
1. Open Notepad
2. Open the CSV file
3. Save As → Encoding: UTF-8 with BOM
4. Try opening in Excel again

### Issue: Semicolons not recognized as separators
**Solution**: 
1. Open Excel
2. File → Options → Advanced
3. Editing Options → Use system separators (uncheck)
4. Set Decimal separator: `,` 
5. Set Thousands separator: `.`

### Issue: Dates not recognized as dates
**Solution**:
1. Select date column
2. Data → Text to Columns
3. Choose "Delimited" → Next
4. Uncheck all delimiters → Next
5. Column data format: Date (DMY) → Finish

## Need the Python Script Anyway?

If you still want to use Grok's Python script for additional formatting, I can:
1. Modify it to work with the new CSV format (simple change)
2. Add it as an optional post-processing step
3. Create a batch script to automate the conversion

Just let me know! 😊
