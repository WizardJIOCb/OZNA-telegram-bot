Write-Host "=== ПРОСМОТР ЛОГОВ CALLBACK ЗАПРОСОВ ===" -ForegroundColor Green
Write-Host ""

$logFile = "c:\ozna_bot\telegram_bot_error.log"

if (Test-Path $logFile) {
    Write-Host "Последние callback запросы:" -ForegroundColor Yellow
    Write-Host ""
    
    Get-Content $logFile -Tail 200 | Select-String -Pattern "CALLBACK|acc_lvl|AccessLevel|manage_users" | ForEach-Object {
        if ($_ -match "🔥") {
            Write-Host $_ -ForegroundColor Red
        } elseif ($_ -match "CALLBACK QUERY") {
            Write-Host $_ -ForegroundColor Cyan
        } else {
            Write-Host $_
        }
    }
    
    Write-Host ""
    Write-Host "=== КОНЕЦ ЛОГОВ ===" -ForegroundColor Green
} else {
    Write-Host "❌ Файл лога не найден: $logFile" -ForegroundColor Red
}

Write-Host ""
Write-Host "Нажмите Enter для выхода..."
Read-Host
