# Stop on first error
$ErrorActionPreference = "Stop"

# Function to force delete a directory
function Remove-ForceDirectory {
    param([string]$path)
    if (Test-Path $path) {
        Get-ChildItem -Path $path -Recurse | Remove-Item -Force -Recurse
        Remove-Item $path -Force
    }
}

Write-Host "Cleaning installation directories..."

# Clean vendor directory
Remove-ForceDirectory ".\vendor"

# Clean composer files
Remove-Item "composer.lock" -Force -ErrorAction SilentlyContinue
Remove-Item ".env" -Force -ErrorAction SilentlyContinue

Write-Host "Cleaned successfully"
