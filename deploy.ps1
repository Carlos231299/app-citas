
# Script de Despliegue Automático (Deploy)
# Uso: .\deploy.ps1 "Mensaje del commit"

param(
    [string]$msg = "update: mejoras generales"
)

Write-Host "🚀 Iniciando Despliegue..." -ForegroundColor Cyan

# 1. Git Local
Write-Host "📦 Guardando cambios locales..." -ForegroundColor Yellow
git add .
git commit -m "$msg"

# 2. Push a GitHub
Write-Host "☁️ Subiendo a GitHub..." -ForegroundColor Yellow
git push origin main

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al subir a GitHub. Verifica tu conexión." -ForegroundColor Red
    exit
}

# 3. Despliegue en Servidor (SSH)
Write-Host "🔥 Desplegando en Servidor AWS..." -ForegroundColor Yellow

$commands = "cd /var/www/html/app-citas ; sudo chown -R ubuntu:www-data . ; sudo chmod -R 777 storage bootstrap/cache database ; php artisan storage:link ; git fetch origin main ; git reset --hard origin/main ; composer install --no-dev --optimize-autoloader ; php artisan optimize:clear ; php artisan view:clear ; php artisan view:cache ; php artisan config:cache ; sudo service php8.2-fpm restart ; sudo systemctl restart nginx"

# Ejecutar comando SSH (asume que pruebas.pem está en la misma carpeta)
ssh -i "pruebas.pem" -o StrictHostKeyChecking=no ubuntu@50.18.72.244 $commands

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ ¡Despliegue Exitoso!" -ForegroundColor Green
}
else {
    Write-Host "⚠️ Hubo un problema conectando al servidor." -ForegroundColor Red
}
