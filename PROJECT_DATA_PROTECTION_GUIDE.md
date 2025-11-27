# Project Data Protection System

## Overview
This system prevents project data loss by ensuring synchronization between file-based and database-based project storage.

## Problem Solved
- **Projects disappearing** after server backups
- **Data inconsistency** between file storage and database
- **Loss of user project settings** during system recovery

## Quick Start

### 1. Initial Setup
```bash
# Run this once to fix any existing issues
php fix_project_data.php

# Verify everything is working
php verify_data_integrity.php

# Create your first comprehensive backup
php backup_all_data.php
```

### 2. Daily Routine (Recommended)
```bash
# Run this daily to maintain data integrity
php daily_maintenance.php
```

### 3. Before Server Backup/Deploy
```bash
# Windows users - run this batch script
sync_all_data.bat

# Or manually for Linux/Mac users:
php backup_all_data.php
# Then upload files to server
```

### 4. After Server Recovery/Restore
```bash
# Restore from backup
php restore_backup.php backups/[timestamp]

# Verify restoration
php verify_data_integrity.php
```

## Files Created

| File | Purpose |
|------|---------|
| `backup_all_data.php` | Creates comprehensive backup of all project data |
| `restore_backup.php` | Restores data from backup |
| `verify_data_integrity.php` | Checks data consistency and health |
| `fix_project_data.php` | Auto-fixes common data issues |
| `daily_maintenance.php` | Daily routine for data protection |
| `sync_all_data.bat` | Windows script for server synchronization |
| `generate_user_data_sync.php` | Creates SQL for user data sync |

## Data Sources Protected

### 1. File-Based Data
- `data/projects.json` - Main project list
- `data/access_requests.json` - Pending access requests

### 2. Database Data
- User personal project lists (`report_projects`)
- User project control settings (`control_projects`)
- User access levels and settings

## Backup Strategy

### Daily Backups (Automatic)
- **Location**: `backups/daily_YYYY-MM-DD/`
- **Retention**: 7 days
- **Content**: Essential project data only

### Full Backups (Manual)
- **Location**: `backups/YYYY-MM-DD_HH-MM-SS/`
- **Retention**: Manual cleanup
- **Content**: Complete project data + summary

### Server Backups
- **Location**: `server_backups/`
- **Content**: Server files + database dumps
- **Created by**: `sync_all_data.bat`

## How It Prevents Data Loss

1. **Dual Storage Monitoring**: Tracks both file and database sources
2. **Automatic Synchronization**: Fixes inconsistencies automatically
3. **Incremental Backups**: Daily snapshots of changes
4. **Cross-Verification**: Ensures data matches between sources
5. **Recovery Scripts**: Easy restoration from any backup point

## Usage Examples

### Scenario 1: Daily Use
```bash
# Morning routine (optional - can be automated)
php daily_maintenance.php
```

### Scenario 2: Before Major Changes
```bash
# Create safety backup
php backup_all_data.php
# Make your changes
# Verify integrity
php verify_data_integrity.php
```

### Scenario 3: Server Issues Detected
```bash
# Check what's wrong
php verify_data_integrity.php

# Auto-fix if possible
php fix_project_data.php

# Manual backup if needed
php backup_all_data.php
```

### Scenario 4: Complete Server Restore
```bash
# After restoring server files
php restore_backup.php backups/2025-01-01_12-30-45

# Verify everything is working
php verify_data_integrity.php

# Fix any remaining issues
php fix_project_data.php
```

## Automation Options

### Windows Task Scheduler
Create a daily task that runs:
```
php C:\path\to\ozna_bot\daily_maintenance.php
```

### Linux Cron Job
Add to crontab:
```
0 2 * * * cd /path/to/ozna_bot && php daily_maintenance.php
```

## Monitoring

### Check System Health
```bash
php verify_data_integrity.php
```

### View Recent Logs
- Daily logs: `logs/daily_maintenance_YYYY-MM-DD.log`
- Statistics: `logs/daily_stats.json`

### Backup Status
- Recent backups: `backups/` directory
- Server backups: `server_backups/` directory

## Troubleshooting

### Projects Still Disappearing?
1. Run `php verify_data_integrity.php` to see what's wrong
2. Run `php fix_project_data.php` to auto-fix issues
3. Check if both file and database are being backed up
4. Ensure server sync includes both sources

### Backup Restore Not Working?
1. Check backup directory exists and has all files
2. Verify database connection in restore script
3. Run with verbose output to see specific errors
4. Try manual file copy + database import

### Performance Issues?
1. Clean old backups: Remove old `backups/daily_*` folders
2. Check log file sizes in `logs/` directory
3. Run maintenance less frequently if needed

## Support

This system is designed to be:
- **Self-diagnosing**: Scripts tell you what's wrong
- **Self-healing**: Auto-fixes common issues
- **Self-documenting**: Creates logs and reports
- **Self-maintaining**: Daily routine handles most issues

If issues persist after running the fix scripts, check the generated log files for specific error messages.