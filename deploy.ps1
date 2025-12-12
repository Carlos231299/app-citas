$ErrorActionPreference = "Stop"

# 1. Local Build and Commit
Write-Host "Iniciando despliegue..." -ForegroundColor Cyan

# Build Assets
Write-Host "Compilando assets (npm run build)..." -ForegroundColor Yellow
npm run build
if ($LASTEXITCODE -ne 0) { Write-Error "Fallo en el build."; exit 1 }

# Git Operations
Write-Host "Git: Agregando archivos..." -ForegroundColor Yellow
git add .

# Determine commit message
$commitMessage = if ($args.Count -gt 0) { $args[0] } else { "actualizacion: auto-deploy" }
Write-Host "Git: Commit con mensaje: '$commitMessage'" -ForegroundColor Yellow

# Check if there are changes to commit
$status = git status --porcelain
if ($status) {
    git commit -m "$commitMessage"
    
    Write-Host "Git: Subiendo cambios (push)..." -ForegroundColor Yellow
    git push
    if ($LASTEXITCODE -ne 0) { Write-Error "Fallo en el push."; exit 1 }
}
else {
    Write-Host "Git: No hay cambios para confirmar." -ForegroundColor Gray
}

# 2. Remote Deployment
Write-Host "Conectando al servidor para desplegar..." -ForegroundColor Cyan

$sshKey = "C:\Users\Carlos\.ssh\pruebas.pem"
$sshTarget = "ubuntu@ec2-54-193-203-69.us-west-1.compute.amazonaws.com"
# Using single quotes for the command to prevent PS from interpreting special chars inside
$remoteCmd = 'cd /var/www/html/app-citas && sudo git pull && sudo php artisan migrate --force && sudo php artisan optimize:clear && sudo systemctl restart nginx php8.2-fpm'

# Execute SSH
ssh -i "$sshKey" -o StrictHostKeyChecking=no $sshTarget $remoteCmd

if ($LASTEXITCODE -ne 0) { 
    Write-Error "Error en el despliegue remoto." 
}
else {
    Write-Host "Despliegue completado con exito." -ForegroundColor Green
}
