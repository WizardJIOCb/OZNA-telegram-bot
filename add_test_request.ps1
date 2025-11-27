# PowerShell скрипт для добавления тестовой заявки на доступ

Write-Host "Создание тестовой заявки на доступ..." -ForegroundColor Green

# Создаем тестовую заявку
$testRequest = @(
    @{
        user_id = 123456789
        user_name = "Тестовый пользователь"
        user_telegram_name = "Test User"
        user_fio = "Иванов Иван Иванович"
        chat_id = "987654321"
        timestamp = [int](Get-Date -UFormat %s)
    }
)

# Преобразуем в JSON
$jsonData = $testRequest | ConvertTo-Json -Depth 10

# Путь к файлу
$filePath = Join-Path $PSScriptRoot "data\access_requests.json"

# Выводим информацию
Write-Host "Путь к файлу: $filePath" -ForegroundColor Yellow

# Записываем в файл
try {
    $jsonData | Out-File -FilePath $filePath -Encoding UTF8
    Write-Host "Тестовая заявка успешно создана!" -ForegroundColor Green
    Write-Host "Содержимое файла:" -ForegroundColor Yellow
    Write-Host $jsonData
} catch {
    Write-Host "Ошибка при создании файла: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "Готово!" -ForegroundColor Green