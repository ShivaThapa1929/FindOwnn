# Fix Nested Folder Structure
# Moves website files up one level

Write-Host "🔧 Fixing folder structure..." -ForegroundColor Cyan
Write-Host ""

$source = "C:\xampp\htdocs\findownn_website\findownn_website"
$dest = "C:\xampp\htdocs\findownn_website"
$tempDir = "C:\xampp\htdocs\findownn_temp_backup"

if (-not (Test-Path $source)) {
    Write-Host "❌ Source folder not found!" -ForegroundColor Red
    exit 1
}

Write-Host "📦 Step 1: Creating backup..." -ForegroundColor Yellow
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}
Copy-Item $source $tempDir -Recurse -Force
Write-Host "   ✅ Backup created at: $tempDir" -ForegroundColor Green

Write-Host ""
Write-Host "📁 Step 2: Moving files..." -ForegroundColor Yellow

# Get all items from nested folder
$items = Get-ChildItem $source -Force

foreach ($item in $items) {
    $destPath = Join-Path $dest $item.Name
    
    # Skip if already exists in parent
    if (Test-Path $destPath) {
        if ($item.PSIsContainer) {
            Write-Host "   ⏭️  Skipping $($item.Name) (already exists)" -ForegroundColor Gray
        } else {
            Write-Host "   ⏭️  Skipping $($item.Name) (already exists)" -ForegroundColor Gray
        }
    } else {
        Move-Item $item.FullName $dest -Force
        Write-Host "   ✅ Moved: $($item.Name)" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "🗑️  Step 3: Cleaning up empty nested folder..." -ForegroundColor Yellow
$remainingItems = Get-ChildItem $source -Force
if ($remainingItems.Count -eq 0) {
    Remove-Item $source -Force
    Write-Host "   ✅ Removed empty folder" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  Folder still contains: $($remainingItems.Count) items" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "✅ Structure fixed!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Now visit: http://localhost/findownn_website/" -ForegroundColor Cyan
Write-Host ""
Write-Host "📦 Backup location: $tempDir" -ForegroundColor Gray
Write-Host "   (You can delete this after verifying everything works)" -ForegroundColor Gray
