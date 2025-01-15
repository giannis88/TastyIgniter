Write-Host "Starting TastyIgniter installation..."

# Run clean script
.\clean.ps1

# Copy environment file
Copy-Item example.env .env

# Install dependencies with optimizations
composer install --no-dev --prefer-dist --optimize-autoloader

# Generate application key
php artisan key:generate --force

Write-Host "Installation completed"
