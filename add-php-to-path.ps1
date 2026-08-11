# Add PHP to Windows PATH
# Run this script as Administrator

$phpPath = "C:\xampp\php"

Write-Host "🔧 Adding PHP to system PATH..." -ForegroundColor Cyan

# Get current PATH
$currentPath = [Environment]::GetEnvironmentVariable("Path", "User")

# Check if PHP is already in PATH
if ($currentPath -like "*$phpPath*") {
    Write-Host "✅ PHP is already in your PATH!" -ForegroundColor Green
} else {
    # Add PHP to PATH
    $newPath = "$currentPath;$phpPath"
    [Environment]::SetEnvironmentVariable("Path", $newPath, "User")
    Write-Host "✅ PHP added to PATH successfully!" -ForegroundColor Green
    Write-Host "⚠️  Please restart your terminal/PowerShell for changes to take effect" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "📝 To verify, close this terminal and open a new one, then run:" -ForegroundColor Cyan
Write-Host "   php -v" -ForegroundColor White
