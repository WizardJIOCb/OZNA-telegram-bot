# Simple file check via PowerShell
$remotePath = "\\82.146.42.213\c$\var\www\api.ladyzenko.pro\tg\ozna\CustomCommands\Handlers\ProjectHandler.php"
$localFile = "c:\ozna_bot\CustomCommands\Handlers\ProjectHandler.php"

Write-Host "=== Checking File Sync Status ===" -ForegroundColor Cyan
Write-Host ""

# Check local file
if (Test-Path $localFile) {
    $localInfo = Get-Item $localFile
    Write-Host "Local File:" -ForegroundColor Green
    Write-Host "  Size: $($localInfo.Length) bytes"
    Write-Host "  Modified: $($localInfo.LastWriteTime)"
    $localHash = Get-FileHash $localFile -Algorithm MD5
    Write-Host "  MD5: $($localHash.Hash)"
} else {
    Write-Host "Local file not found!" -ForegroundColor Red
}

Write-Host ""
Write-Host "To check remote file, please run on server:" -ForegroundColor Yellow
Write-Host "  ssh root@82.146.42.213" -ForegroundColor White
Write-Host "  Password: 7qQ#28051961" -ForegroundColor White
Write-Host ""
Write-Host "  # Check file info:" -ForegroundColor Gray
Write-Host "  stat /var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php" -ForegroundColor White
Write-Host ""
Write-Host "  # Count our fixes:" -ForegroundColor Gray
Write-Host "  grep -c '\`$result->free()' /var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php" -ForegroundColor White
Write-Host ""
Write-Host "  # Should show 15+ if file is updated" -ForegroundColor Gray
Write-Host ""
Write-Host "  # View recent errors:" -ForegroundColor Gray
Write-Host "  tail -100 /var/log/nginx/error.log" -ForegroundColor White
Write-Host ""
Write-Host "  # Restart PHP-FPM to clear cache:" -ForegroundColor Gray
Write-Host "  sudo systemctl restart php-fpm" -ForegroundColor White
