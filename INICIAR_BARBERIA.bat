@echo off
setlocal enabledelayedexpansion

echo ===================================================
echo   SISTEMA DE GESTION - BARBERIA JR (AUTO-START)
echo ===================================================

:: 1. INICIAR TUNEL SSH Y SERVIDOR REMOTO
echo [1/3] Conectando al Servidor y Mapeando Puerto 8001...
:: Mapeamos Local 8001 -> Remoto 8000 para que el bot hable con la web
start "TUNEL-SSH-BARBERIA" cmd /k "ssh -t -i pruebas.pem -o StrictHostKeyChecking=no -L 8001:localhost:8000 ubuntu@50.18.72.244 \"fuser -k 8000/tcp; cd /var/www/html/app-citas && php artisan serve --host=127.0.0.1 --port=8000\""

:: 2. INICIAR BOT DE WHATSAPP
echo [2/3] Iniciando Bot de WhatsApp (Local)...
cd whatsapp-bot-local
start "BOT-WHATSAPP" cmd /k "npm start"

:: 3. ABRIR NAVEGADOR (UN SOLO WINDOW, DIFERENTES PESTAÑAS)
echo [3/3] Abriendo Dashboard y Control...
timeout /t 5 /nobreak
:: Abrimos el control del bot (Dashboard local), el panel de citas (vía tunel) y los logs si es necesario
start chrome "http://localhost:3000" "http://localhost:8001" "/var/www/html/app-citas/storage/logs/laravel.log"

echo.
echo ===================================================
echo   SISTEMA INICIADO EXITOSAMENTE
echo   - Bot en: http://localhost:3000
echo   - Web en: http://localhost:8001
echo ===================================================
echo.
pause
