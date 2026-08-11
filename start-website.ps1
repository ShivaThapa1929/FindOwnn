# Quick Website Launcher
# Opens your website in the default browser

Write-Host "🚀 Launching Findownn Website..." -ForegroundColor Cyan
Write-Host ""

$url = "http://localhost/findownn_website/"

# Check if Apache is running
$apacheProcess = Get-Process -Name "httpd" -ErrorAction SilentlyContinue

if ($apacheProcess) {
    Write-Host "✅ Apache is running" -ForegroundColor Green
    Write-Host "🌐 Opening: $url" -ForegroundColor Cyan
    Start-Process $url
} else {
    Write-Host "⚠️  Apache is not running!" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Please start Apache from XAMPP Control Panel first" -ForegroundColor White
    Write-Host ""
    Write-Host "Then run this script again, or manually visit:" -ForegroundColor White
    Write-Host "   $url" -ForegroundColor Cyan
}
