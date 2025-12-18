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
