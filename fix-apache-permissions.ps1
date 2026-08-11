# Fix Apache Forbidden Error
# Run this script to update Apache configuration

Write-Host "🔧 Fixing Apache Permissions..." -ForegroundColor Cyan
Write-Host ""

$httpdConf = "C:\xampp\apache\conf\httpd.conf"
$backupPath = "C:\xampp\apache\conf\httpd.conf.backup"

if (-not (Test-Path $httpdConf)) {
    Write-Host "❌ Apache config not found at: $httpdConf" -ForegroundColor Red
    Write-Host "   Please check your XAMPP installation" -ForegroundColor Yellow
    exit 1
}

# Create backup
Write-Host "📦 Creating backup..." -ForegroundColor Yellow
Copy-Item $httpdConf $backupPath -Force
Write-Host "   Backup saved: $backupPath" -ForegroundColor White

# Read config
$config = Get-Content $httpdConf -Raw

# Fix: Change DocumentRoot access
$oldPattern = '(?s)<Directory "C:/xampp/htdocs">.*?Require all denied.*?</Directory>'
$newContent = @'
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>
'@

if ($config -match $oldPattern) {
    $config = $config -replace $oldPattern, $newContent
    Write-Host "✅ Updated DocumentRoot permissions" -ForegroundColor Green
} else {
    Write-Host "⚠️  Pattern not found, trying alternative..." -ForegroundColor Yellow
    
    # Alternative: Just replace "Require all denied" with "Require all granted"
    $config = $config -replace 'Require all denied', 'Require all granted'
    Write-Host "✅ Changed 'denied' to 'granted'" -ForegroundColor Green
}

# Save changes
Set-Content $httpdConf $config -NoNewline

Write-Host ""
Write-Host "✅ Configuration updated!" -ForegroundColor Green
Write-Host ""
Write-Host "⚠️  IMPORTANT: Restart Apache in XAMPP Control Panel" -ForegroundColor Yellow
Write-Host ""
Write-Host "📝 If issues persist, manually edit:" -ForegroundColor Cyan
Write-Host "   $httpdConf" -ForegroundColor White
Write-Host ""
Write-Host "   Find: <Directory ""C:/xampp/htdocs"">" -ForegroundColor White
Write-Host "   Change: Require all denied" -ForegroundColor Red
Write-Host "   To:     Require all granted" -ForegroundColor Green
