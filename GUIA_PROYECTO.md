# 📘 Guía del Proyecto: Barbería JR

Esta guía documenta la infraestructura del proyecto, cómo ejecutar el entorno de desarrollo y la arquitectura híbrida con el Bot de WhatsApp.

## 🏗️ Arquitectura

El sistema se compone de dos partes principales que se comunican entre sí:

1.  **Backend (Laravel 10 / PHP 8.2):**
    *   Maneja la lógica de negocio, base de datos (MySQL/SQLite), y el panel administrativo.
    *   Ubicado en el servidor remoto (AWS EC2).
2.  **Bot de WhatsApp (Node.js + whatsapp-web.js):**
    *   Maneja la interacción con los clientes vía WhatsApp (mensajes, respuestas automáticas).
    *   Ubicado **localmente** (en tu máquina) o en un servicio que soporte Puppeteer/Chrome.
    *   Se comunica con el Backend mediante túneles SSH.

---

## 🚀 Requisitos Previos

Si deseas ejecutar este proyecto en otro entorno, necesitarás:

*   **PHP 8.2+** y Composer.
*   **Node.js 18+** y NPM.
*   **Git Bash** (o una terminal con cliente SSH).
*   **Archivo de Clave SSH (`pruebas.pem`)**: Necesario para conectar con el servidor AWS.

---

## 🤖 Configuración del Bot de WhatsApp

El bot se encuentra en la carpeta `whatsapp-bot-local`.

### 1. Instalación
Si mueves el proyecto a otra máquina, entra a la carpeta e instala las dependencias:

```bash
cd whatsapp-bot-local
npm install
```

### 2. Ejecución Local (Desarrollo)
Para iniciar el bot y ver el código QR en la terminal:

```bash
npm start
```
*Esto iniciará el bot en el puerto `3000` de tu máquina.*

---

## 🔗 Conexión Servidor ↔️ Bot (Túneles SSH)

Dado que el Bot corre en tu máquina (Local) y el Backend en AWS (Nube), necesitamos **túneles SSH** para que se vean entre sí.

### Tienes que ejecutar estos dos comandos en terminales separadas:

#### 1. Túnel Inverso (Backend -> Bot)
Permite que el servidor envíe notificaciones (confirmaciones, recordatorios) a tu bot local.
*   El servidor envía a `localhost:3000` (en el servidor), y el túnel lo redirige a `localhost:3000` (en tu PC).

```bash
ssh -i "pruebas.pem" -R 3000:localhost:3000 ubuntu@ec2-50-18-72-244.us-west-1.compute.amazonaws.com
```

#### 2. Túnel Directo (Bot -> Backend)
Permite que tu bot local consulte la API del servidor (por ejemplo, para guardar citas o cancelar).
*   Tu bot envía a `localhost:8001` (en tu PC), y el túnel lo redirige a `localhost:8000` (o el puerto interno del backend) en el servidor.

```bash
ssh -i "pruebas.pem" -o StrictHostKeyChecking=no -L 8001:localhost:8000 ubuntu@ec2-50-18-72-244.us-west-1.compute.amazonaws.com
```
*(Nota: El puerto 8000 en el servidor debe estar escuchando la aplicación Laravel, usualmente vía nginx o php artisan serve).*

---

## 🛠️ Comandos de Mantenimiento

### Despliegue Rápido
Para subir cambios de código al servidor:
```powershell
.\deploy.ps1 "mensaje del commit"
```

### Limpieza de Caché (Servidor)
Si haces cambios visuales y no se ven:
```bash
ssh -i "pruebas.pem" ubuntu@50.18.72.244 "cd /var/www/html/app-citas; php artisan view:clear"
```

---

## 📁 Estructura de Archivos Clave

*   `app/Http/Controllers/AppointmentController.php`: Lógica de citas.
*   `routes/web.php`: Rutas web y admin.
*   `routes/api.php`: Rutas para el bot.
*   `whatsapp-bot-local/index.ts`: Código principal del Bot.
*   `resources/views/`: Vistas (Blade) del frontend.
