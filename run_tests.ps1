param (
    [string]$Module = ""
)

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "   NETFIX2 QA AUTOMATION TEST RUNNER (CodeceptJS)      " -ForegroundColor Cyan
Write-Host "========================================================" -ForegroundColor Cyan

# 1. Kiểm tra MySQL
$mysqlCheck = Get-NetTCPConnection -LocalPort 3306 -ErrorAction SilentlyContinue
if (-not $mysqlCheck) {
    Write-Host "[!] MySQL chưa chạy, đang khởi động MySQL..." -ForegroundColor Yellow
    Start-Process -FilePath "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini", "--standalone" -WindowStyle Hidden
    Start-Sleep -Seconds 3
} else {
    Write-Host "[OK] MySQL đang hoạt động (Port 3306)." -ForegroundColor Green
}

# 2. Kiểm tra PHP Server
$phpCheck = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
if (-not $phpCheck) {
    Write-Host "[!] PHP Server chưa chạy, đang khởi động PHP 8.2 trên http://127.0.0.1:8000..." -ForegroundColor Yellow
    Start-Process -FilePath "C:\xampp\php\php.exe" -ArgumentList "-S", "127.0.0.1:8000", "-t", "." -WindowStyle Hidden
    Start-Sleep -Seconds 2
} else {
    Write-Host "[OK] PHP Web Server đang hoạt động (Port 8000)." -ForegroundColor Green
}

# 3. Chạy kiểm thử
Write-Host "--------------------------------------------------------" -ForegroundColor Cyan
$bunExe = "C:\Users\PHUONG THAO\.bun\bin\bun.exe"
if ($Module -ne "") {
    Write-Host "Đang thực thi bộ kiểm thử phân hệ: $Module" -ForegroundColor Yellow
    & $bunExe x codeceptjs run "tests/${Module}_test.js" --steps
} else {
    Write-Host "Đang thực thi toàn bộ kịch bản kiểm thử..." -ForegroundColor Yellow
    & $bunExe x codeceptjs run --steps
}

