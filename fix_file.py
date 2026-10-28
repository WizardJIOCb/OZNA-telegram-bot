# -*- coding: utf-8 -*-
# Script to remove duplicate lines from ProjectHandler.php

file_path = r'C:\ozna_bot\CustomCommands\Handlers\ProjectHandler.php'

# Read all lines
with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Remove lines 7884-8719 (0-indexed: 7883-8718)
# Keep lines 0-7883 and 8719+
new_lines = lines[:7883] + lines[8719:]

# Write back
with open(file_path, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print(f"Fixed! Removed {8719-7883} lines of duplicate code")
print(f"Original: {len(lines)} lines")
print(f"New: {len(new_lines)} lines")
