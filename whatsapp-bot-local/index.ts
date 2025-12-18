import express from "express";
import pkg from "whatsapp-web.js";
import * as qrcode from "qrcode-terminal";

const { Client, LocalAuth } = pkg;

/* =========================
   APP + WHATSAPP
========================= */

const app = express();
app.use(express.json());

// 🔍 LOGGER MIDDLEWARE
app.use((req, res, next) => {
    console.log(`[Recepción] ${req.method} ${req.path}`);
    console.log('📦 Datos:', JSON.stringify(req.body, null, 2));
    next();
});

console.log("⏳ Starting client...");

const client = new Client({
    authStrategy: new LocalAuth(), // 👈 no pide QR cada vez
    puppeteer: {
        headless: false,
        // executablePath: "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe",
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    },
});

/* =========================
   TYPES
========================= */

type Appointment = {
    name: string;
    date: string;
    time: string;
    place: string;
    barber_name: string;
    service_name: string;
    is_request: boolean;
};

type State = "IDLE" | "WAITING_CONFIRMATION";

/* =========================
   STORAGE (MEMORIA)
========================= */

const chatState = new Map<string, State>();
const appointments = new Map<string, Appointment>();

/* =========================
   UTILS
========================= */

function normalize(text: string): string {
    return text
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^\w\s]/g, "")
        .replace(/\s+/g, " ")
        .trim();
}

const CONFIRM = ["1", "si", "confirmar", "confirmo", "ok", "dale"];
const CANCEL = ["2", "no", "cancelar", "cancelo"];

/* =========================
   WHATSAPP EVENTS
========================= */

client.on("qr", (qr) => {
    console.log("📸 QR RECEIVED");
    qrcode.generate(qr, { small: true });
});

client.on("ready", () => {
    console.log("✅ Client ready");
});

client.on("auth_failure", (msg) => {
    console.error("❌ AUTH FAILURE", msg);
});

client.on("disconnected", (reason) => {
    console.log("🔌 DISCONNECTED", reason);
});

/* =========================
   MESSAGE HANDLER (CON CONTEXTO)
========================= */

client.on("message", async (message) => {
    const chatId = message.from;
    const text = normalize(message.body);

    // ⛔ ignorar mensajes fuera de contexto
    if (chatState.get(chatId) !== "WAITING_CONFIRMATION") return;

    // ✅ CONFIRMAR
    if (CONFIRM.some((w) => text === w || text.includes(w))) {
        const appt = appointments.get(chatId);

        chatState.set(chatId, "IDLE");
        appointments.delete(chatId);

        await message.reply(
            `✅ *Cita confirmada*\n\n` +
            `👤 ${appt?.name}\n` +
            `📅 ${appt?.date}\n` +
            `⏰ ${appt?.time}\n` +
            `📍 ${appt?.place}\n\n` +
            `¡Te esperamos!`
        );
        return;
    }

    // ❌ CANCELAR
    if (CANCEL.some((w) => text === w || text.includes(w))) {
        chatState.set(chatId, "IDLE");
        appointments.delete(chatId);

        await message.reply("❌ Tu cita fue cancelada.");
        return;
    }

    // ⚠️ respuesta inválida (solo cuando espera)
    await message.reply(
        "Por favor responde:\n1️⃣ Confirmar\n2️⃣ Cancelar"
    );
});

/* =========================
   SINGLE ENDPOINT
========================= */

app.post("/appointment", async (req, res) => {
    const { phone, name, date, time, place, barber_name, service_name, is_request, display_price } = req.body;

    console.log("🐛 DEBUG BOT: is_request received =", is_request, "Type:", typeof is_request);

    if (!phone || !name || !date || !time) {
        return res.status(400).json({
            error: "Faltan datos obligatorios (phone, name, date, time)",
        });
    }

    const chatId = phone.includes("@c.us")
        ? phone
        : phone.replace(/\D/g, "") + "@c.us";

    // Save Context
    appointments.set(chatId, { name, date, time, place: place || 'Barbería JR', barber_name, service_name, is_request });

    // Logic: Request vs Confirmed
    if (is_request) {
        // Mode: REQUEST (Other Service)
        // User: "un mensaje donde diga que la cita está en espera de confirmación para ser apartada"
        chatState.set(chatId, "IDLE"); // No interactive flow for this one (or maybe yes?)
        // Let's keep it simple: Just notify.

        await client.sendMessage(
            chatId,
            `Hola *${name}* 👋\n\n` +
            `Hemos recibido tu solicitud para:\n` +
            `💇‍♂️ *Servicio:* ${service_name || 'Otro Servicio'}\n` +
            `💈 *Barbero:* ${barber_name}\n` +
            `📅 *Fecha:* ${date} a las ${time}\n\n` +
            `⚠️ *Estado:* Tu cita está en *ESPERA DE CONFIRMACIÓN* para ser apartada.\n` +
            `Nos pondremos en contacto contigo pronto para definir los detalles.`
        );

    } else {
        // Mode: CONFIRMED
        // User: "debe mandar también el barbero en el mensaje"
        chatState.set(chatId, "WAITING_CONFIRMATION"); // Enable interactive confirmation

        // Dynamic Price Line
        const priceLine = display_price ? `💰 *Precio:* ${display_price}\n` : '';

        await client.sendMessage(
            chatId,
            `Hola *${name}* 👋\n\n` +
            `✅ Tu cita ha sido *CONFIRMADA* en Barbería JR.\n\n` +
            `📋 *Detalles:*\n` +
            `💇‍♂️ *Servicio:* ${service_name}\n` +
            `💈 *Barbero:* ${barber_name}\n` +
            `📅 *Fecha:* ${date}\n` +
            `⏰ *Hora:* ${time}\n` +
            `${priceLine}\n` +
            `Por favor confirma tu asistencia respondiendo:\n` +
            `1️⃣ Confirmar\n` +
            `2️⃣ Cancelar`
        );
    }

    res.json({ success: true, status: is_request ? 'request_sent' : 'confirmation_sent' });
});

/* =========================
   START
========================= */

client.initialize();

// --- CANCELLATION STATE MACHINE ---
interface CancellationState {
    step: 'WAITING_REASON';
    timestamp: number;
}
const cancellationStates = new Map<string, CancellationState>();

client.on("message", async (msg) => {
    // Basic Logging
    // console.log("Received:", msg.body, "From:", msg.from);

    const chatId = msg.from;
    const body = msg.body.trim();
    const lowerBody = body.toLowerCase();

    // 1. Check if User is in Cancellation State (Waiting for Reason)
    if (cancellationStates.has(chatId)) {
        const state = cancellationStates.get(chatId)!;

        // Timeout check (e.g., 10 mins)
        if (Date.now() - state.timestamp > 10 * 60 * 1000) {
            cancellationStates.delete(chatId);
            await client.sendMessage(chatId, "⏳ Se ha agotado el tiempo para la cancelación. Por favor inicia el proceso nuevamente si lo deseas.");
            return;
        }

        // Process Reason
        console.log(`Processing Cancellation Reason from ${chatId}: ${body}`);

        try {
            // Call Server API
            // Use 'localhost' or '127.0.0.1' -> In Local Bot, localhost is the machine.
            // But we used 'http://localhost:3000' for Laravel -> Bot.
            // Bot -> Laravel is usually 'http://127.0.0.1:8000' (if using `php artisan serve`) 
            // OR the Server URL if Laravel is remote.
            // WAIT! The User said "Colocar cron... sabiendo que el servidor tiene 1GB".
            // The Bot is LOCAL. The Laravel is REMOTE (EC2).
            // So we must hit the EC2 URL: http://ec2-50-18-72-244...
            // Or better, use a config variable.

            // FIXME: Hardcoding EC2 URL for now based on context 
            const serverUrl = 'http://ec2-50-18-72-244.us-west-1.compute.amazonaws.com/api/bot/cancel';

            const response = await fetch(serverUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    phone: chatId.replace('@c.us', ''),
                    reason: body
                })
            });

            const data = await response.json();

            if (data.success) {
                await client.sendMessage(chatId, "✅ Tu cita ha sido cancelada correctamente.");
            } else {
                await client.sendMessage(chatId, "❌ No encontramos una cita próxima para cancelar o ya ocurrió un error.");
            }
        } catch (error) {
            console.error('API Error:', error);
            await client.sendMessage(chatId, "❌ Error de conexión al procesar tu solicitud.");
        }

        // Clear State
        cancellationStates.delete(chatId);
        return;
    }

    // 2. Normal Logic (Cancel Trigger)
    // Triggers: "2", "no", "cancelar", "cancel"
    const cancelKeywords = ["2", "no", "cancelar", "cancel"];
    if (cancelKeywords.includes(lowerBody)) {
        // Start Cancellation Flow
        cancellationStates.set(chatId, { step: 'WAITING_REASON', timestamp: Date.now() });

        await client.sendMessage(chatId, "Lamentamos esto. 😟\n\nPor favor indícanos brevemente el **motivo de la cancelación** para procesarla:");
        return;
    }

    // 3. Confirmation Trigger (Existing Logic)
    const confirmKeywords = ["1", "si", "sí", "confirmar", "confirm", "ok"];
    if (confirmKeywords.includes(lowerBody)) {
        // Just visual confirmation as per previous logic
        await client.sendMessage(chatId, "✅ **Cita Confirmada**\n\n¡Gracias! Te esperamos en Barbería JR.");
        return;
    }

    // Default: Ignore other messages or send "I don't understand" (Better to ignore)
});

// --- NEW ENDPOINT: REMINDER ---
app.post('/reminder', async (req, res) => {
    const { phone, name, time, barber_name, service_name } = req.body;
    console.log(`⏰ Sending Reminder to ${name} (${phone}) for ${time}`);

    try {
        if (!client) {
            console.error('❌ Client not ready');
            return res.status(503).json({ error: 'WhatsApp client not ready' });
        }

        const chatId = phone.includes("@c.us")
            ? phone
            : phone.replace(/\D/g, "") + "@c.us";

        const reminderMsg = `⏳ *RECORDATORIO DE CITA* ⏳\n\n` +
            `Hola *${name}*, te recordamos tu cita hoy:\n\n` +
            `⏰ *Hora:* ${time}\n` +
            `💈 *Barbero:* ${barber_name}\n` +
            `💇‍♂️ *Servicio:* ${service_name}\n\n` +
            `Estamos esperándote. ¿Confirmas tu llegada?`;

        await client.sendMessage(chatId, reminderMsg);

        res.json({ success: true });
    } catch (error) {
        console.error('❌ Error sending reminder:', error);
        res.status(500).json({ error: 'Failed' });
    }
});

app.listen(3000, () => {
    console.log("🚀 Server running on http://localhost:3000");
});
