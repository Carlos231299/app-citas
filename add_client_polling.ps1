$path = 'c:\Users\Carlos\Desktop\PRACTICAS PY\app citas\whatsapp-bot-local\index.ts'
$c = Get-Content $path -Raw

$functionCode = @"
async function checkClientNotifications() {
    if (!isClientReady) return;
    try {
        const { data: notifications } = await axios.get(`${API_BASE}/client-notifications/pending`);
        if (!notifications || notifications.length === 0) return;

        console.log('🔔 Found ' + notifications.length + ' pending CLIENT notifications.');

        for (const notif of notifications) {
            const clientPhone = formatPhone(notif.client_phone);
            let message = '';
            
            if (notif.status === 'scheduled') {
                message = `Hola ${notif.client_name}, tu cita en Barbería JR está confirmada ✅\n\n📅 Fecha: ${notif.date}\n⏰ Hora: ${notif.time}\n💈 Barbero: ${notif.barber_name}\n✂ Servicio: ${notif.service}\n\n¡Te esperamos!`;
            } else if (notif.status === 'request') {
                message = `Hola ${notif.client_name}, hemos recibido tu solicitud de cita para el ${notif.date} a las ${notif.time}. ⏳\n\nEl barbero ${notif.barber_name} confirmará su disponibilidad pronto. Te avisaremos.`;
            }

            if (message) {
                 await client.sendMessage(clientPhone, message);
                 console.log(`✅ Message sent to client ${notif.client_name}`);
                 await axios.post(`${API_BASE}/client-notifications/mark-sent`, { id: notif.id });
                 await new Promise(r => setTimeout(r, 2000));
            }
        }
    } catch (error: any) {
        console.error('❌ Client Polling Error:', error.message);
    }
}

async function checkNotifications() {
"@

$intervalCode = @"
setInterval(checkNotifications, 10000);
setInterval(checkClientNotifications, 10000);
"@

# Inject Function
if (-not $c.Contains("async function checkClientNotifications")) {
    $c = $c.Replace("async function checkNotifications() {", $functionCode)
    Write-Host "Injected checkClientNotifications function."
}
else {
    Write-Host "Function already exists."
}

# Inject Interval
if (-not $c.Contains("setInterval(checkClientNotifications, 10000);")) {
    $c = $c.Replace("setInterval(checkNotifications, 10000);", $intervalCode)
    Write-Host "Injected setInterval."
}
else {
    Write-Host "Interval already exists."
}

Set-Content $path $c
