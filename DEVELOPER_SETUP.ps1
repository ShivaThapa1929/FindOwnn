# ==============================================================================
# Findownn Sports Tech - Automated Developer Setup Script
# ==============================================================================

Write-Host "`n========================================================" -ForegroundColor Green
Write-Host "   FINDOWNN SPORTS TECH - DEVELOPER ENVIRONMENT SETUP" -ForegroundColor Green
Write-Host "========================================================`n" -ForegroundColor Green

$SETUP_PHP = Join-Path $PSScriptRoot "setup-database.php"

if (-not (Test-Path $SETUP_PHP)) {
    Write-Host "[ERROR] Could not find setup-database.php!" -ForegroundColor Red
    exit 1
}

# Locate PHP CLI Binary
$PHP_CMD = "php"
if (Test-Path "C:\xampp\php\php.exe") {
    $PHP_CMD = "C:\xampp\php\php.exe"
}

try {
    & $PHP_CMD $SETUP_PHP
} catch {
    Write-Host "[ERROR] PHP execution failed." -ForegroundColor Red
}
