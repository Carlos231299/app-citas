Write-Host "Iniciando Barberia JR para acceso externo..." -ForegroundColor Cyan
Write-Host "IP Local detectada: 0.0.0.0 (Todas las interfaces)" -ForegroundColor Yellow
Write-Host "Puerto: 8000" -ForegroundColor Yellow
Write-Host "---------------------------------------------------"
Write-Host "Para salir, presiona Ctrl + C"
Write-Host "---------------------------------------------------"

# Run Laravel Server binding to ALL network interfaces
php artisan serve --host=0.0.0.0 --port=8000
