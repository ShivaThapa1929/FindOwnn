# Findownn Quick Start Script
# Run this script in PowerShell to set up everything

Write-Host "🚀 Findownn Platform - Quick Start Setup" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green
Write-Host ""

$adminPath = "c:\xampp\htdocs\findownn_website\admin"
$phpPath = "C:\xampp\php\php.exe"

# Check if PHP exists
if (!(Test-Path $phpPath)) {
    Write-Host "❌ PHP not found at $phpPath" -ForegroundColor Red
    Write-Host "Please install XAMPP or update the PHP path in this script" -ForegroundColor Yellow
    exit 1
}

Write-Host "✓ PHP found" -ForegroundColor Green

# Step 1: Run Image Migration
Write-Host ""
Write-Host "Step 1: Running Image Tables Migration..." -ForegroundColor Cyan
Set-Location $adminPath
& $phpPath run-image-migration.php

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Image migration completed" -ForegroundColor Green
} else {
    Write-Host "❌ Image migration failed" -ForegroundColor Red
    Write-Host "Please run manually: cd $adminPath && $phpPath run-image-migration.php" -ForegroundColor Yellow
}

# Step 2: Run API Migration
Write-Host ""
Write-Host "Step 2: Running API Token Migration..." -ForegroundColor Cyan
& $phpPath run-api-migration.php

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ API migration completed" -ForegroundColor Green
} else {
    Write-Host "❌ API migration failed" -ForegroundColor Red
    Write-Host "Please run manually: cd $adminPath && $phpPath run-api-migration.php" -ForegroundColor Yellow
}

# Step 3: Create Upload Directories
Write-Host ""
Write-Host "Step 3: Creating Upload Directories..." -ForegroundColor Cyan

$uploadsPath = "$adminPath\public\uploads"
$venuesPath = "$uploadsPath\venues"
$courtsPath = "$uploadsPath\courts"

# Create directories
if (!(Test-Path $uploadsPath)) {
    New-Item -ItemType Directory -Path $uploadsPath | Out-Null
    Write-Host "✓ Created uploads directory" -ForegroundColor Green
} else {
    Write-Host "✓ Uploads directory already exists" -ForegroundColor Green
}

if (!(Test-Path $venuesPath)) {
    New-Item -ItemType Directory -Path $venuesPath | Out-Null
    Write-Host "✓ Created venues directory" -ForegroundColor Green
} else {
    Write-Host "✓ Venues directory already exists" -ForegroundColor Green
}

if (!(Test-Path $courtsPath)) {
    New-Item -ItemType Directory -Path $courtsPath | Out-Null
    Write-Host "✓ Created courts directory" -ForegroundColor Green
} else {
    Write-Host "✓ Courts directory already exists" -ForegroundColor Green
}

# Step 4: Set Permissions
Write-Host ""
Write-Host "Step 4: Setting Directory Permissions..." -ForegroundColor Cyan

try {
    icacls $uploadsPath /grant Everyone:F /T | Out-Null
    Write-Host "✓ Permissions set successfully" -ForegroundColor Green
} catch {
    Write-Host "⚠ Could not set permissions automatically" -ForegroundColor Yellow
    Write-Host "Please set write permissions manually on: $uploadsPath" -ForegroundColor Yellow
}

# Summary
Write-Host ""
Write-Host "=========================================" -ForegroundColor Green
Write-Host "✅ Setup Complete!" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Restart Apache in XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Open admin panel: http://localhost/admin" -ForegroundColor White
Write-Host "3. Login with: admin@findownn.com / Admin@123" -ForegroundColor White
Write-Host "4. Test image upload in any venue" -ForegroundColor White
Write-Host "5. Test API endpoints (see API_SETUP_AND_TESTING.md)" -ForegroundColor White
Write-Host ""
Write-Host "📚 Documentation:" -ForegroundColor Cyan
Write-Host "  - PROJECT_STATUS_COMPLETE.md - Complete overview" -ForegroundColor White
Write-Host "  - COMPLETE_MOBILE_API.md - API reference" -ForegroundColor White
Write-Host "  - API_SETUP_AND_TESTING.md - API testing guide" -ForegroundColor White
Write-Host ""
Write-Host "🎉 Your platform is ready to launch!" -ForegroundColor Green
Write-Host ""

# Pause to see results
Read-Host "Press Enter to exit"
