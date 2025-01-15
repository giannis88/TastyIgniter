Write-Host "Starting TastyIgniter server..."

# Ensure storage directory permissions
if (-not (Test-Path "storage")) {
    New-Item -ItemType Directory -Path "storage" -Force
}
icacls "storage" /grant "Everyone:(OI)(CI)F" /T

# Start the server
php artisan serve --port=8000 --host=localhost
