@echo off
TITLE LAUNCHER

echo ==========================================
echo    INICIANDO SISTEMA BARBERIA JR
echo ==========================================

:: 1. Iniciar Bot (Node.js)
echo [1/3] Lanzando Bot Local...
start "BOT WHATSAPP" cmd /k "npm start"
timeout /t 2 >nul

:: 2. Tunnel Inverso
echo [2/3] Abriendo Tunel Inverso...
start "TUNEL SERVIDOR -> BOT (NO CERRAR)" cmd /k "echo Conectando tunel inverso... & ssh -i pruebas.pem -R 3000:localhost:3000 ubuntu@50.18.72.244 -N"

:: 3. Servidor Remoto + Tunel Directo
echo [3/3] Iniciando Servidor y Tunel...
start "SERVIDOR REMOTO + TUNEL (NO CERRAR)" cmd /k ssh -i pruebas.pem -o StrictHostKeyChecking=no -L 8001:localhost:8000 ubuntu@50.18.72.244 "cd /var/www/html/app-citas && php artisan serve --host=127.0.0.1 --port=8000"

echo.
echo ==========================================
echo    TRES VENTANAS ABIERTAS
echo ==========================================
echo 1. Bot Local (Node.js)
echo 2. Tunel Inverso
echo 3. Servidor Remoto (Artisan) + Tunel (8001->8000)
echo.
echo NO CIERRES LAS VENTANAS NEGRAS. MINIMIZALAS.
echo Cerrando lanzador en 3 segundos...
timeout /t 3 >nul
exit
