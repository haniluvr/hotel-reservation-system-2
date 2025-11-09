# PowerShell script to clear Vite cache
Write-Host "Clearing Vite cache..." -ForegroundColor Yellow

if (Test-Path "node_modules\.vite") {
    Remove-Item -Recurse -Force "node_modules\.vite"
    Write-Host "Vite cache cleared!" -ForegroundColor Green
} else {
    Write-Host "No Vite cache found." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Starting Vite dev server..." -ForegroundColor Yellow
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Gray
Write-Host ""
npm run dev

