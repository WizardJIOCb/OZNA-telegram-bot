# PowerShell script to check server file via SSH
# Uses Posh-SSH module or falls back to manual SSH

$server = "82.146.42.213"
$username = "root"
$password = "7qQ#28051961"
$remotePath = "/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php"

Write-Host "=== ПРОВЕРКА КОДА НА СЕРВЕРЕ ===" -ForegroundColor Cyan
Write-Host "Сервер: $server"
Write-Host "Файл: $remotePath"
Write-Host ""

# Check if Posh-SSH is available
if (Get-Module -ListAvailable -Name Posh-SSH) {
    Import-Module Posh-SSH
    
    $secPassword = ConvertTo-SecureString $password -AsPlainText -Force
    $credential = New-Object System.Management.Automation.PSCredential($username, $secPassword)
    
    try {
        $session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey
        $result = Invoke-SSHCommand -SessionId $session.SessionId -Command "sed -n '8895,8905p' $remotePath"
        Write-Host "Строки 8895-8905:" -ForegroundColor Yellow
        Write-Host $result.Output
        Remove-SSHSession -SessionId $session.SessionId | Out-Null
    } catch {
        Write-Host "Ошибка SSH: $_" -ForegroundColor Red
    }
} else {
    Write-Host "Posh-SSH не установлен. Устанавливаю..." -ForegroundColor Yellow
    try {
        Install-Module -Name Posh-SSH -Force -Scope CurrentUser -SkipPublisherCheck
        Import-Module Posh-SSH
        
        $secPassword = ConvertTo-SecureString $password -AsPlainText -Force
        $credential = New-Object System.Management.Automation.PSCredential($username, $secPassword)
        
        $session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey
        $result = Invoke-SSHCommand -SessionId $session.SessionId -Command "sed -n '8895,8905p' $remotePath"
        Write-Host "Строки 8895-8905:" -ForegroundColor Yellow
        Write-Host $result.Output
        
        # Check for name_1c
        if ($result.Output -match "name_1c") {
            Write-Host "`n✅ ИСПРАВЛЕНО: Код использует 'name_1c'" -ForegroundColor Green
        } elseif ($result.Output -match "nomenclature_1c") {
            Write-Host "`n❌ НЕ ИСПРАВЛЕНО: Код использует 'nomenclature_1c'" -ForegroundColor Red
        }
        
        Remove-SSHSession -SessionId $session.SessionId | Out-Null
    } catch {
        Write-Host "Не удалось установить Posh-SSH: $_" -ForegroundColor Red
        Write-Host "Попробуйте выполнить вручную: Install-Module -Name Posh-SSH -Force" -ForegroundColor Yellow
    }
}
