Write-Host "Running TastyIgniter setup..."

# Run the installer
php artisan igniter:install

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

Write-Host "Setup completed. Access your site at http://localhost:8000"
