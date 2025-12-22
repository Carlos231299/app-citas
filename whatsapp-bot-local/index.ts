import express from "express";
import pkg from "whatsapp-web.js";
import * as qrcode from "qrcode-terminal";
import axios from "axios";

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
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu'],
        timeout: 60000,
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

// --- CANCELLATION STATE MACHINE ---
interface CancellationState {
    step: 'WAITING_REASON';
    timestamp: number;
    timeoutId: NodeJS.Timeout;
}
const cancellationStates = new Map<string, CancellationState>();

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

const CONFIRM = ["1", "si", "confirmar", "Confirmar", "confirmo", "ok", "dale"];
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
    try {
        const chatId = message.from;
        const body = message.body.trim();
        const lowerBody = body.toLowerCase();

        // 1. PRIORITY: Check if User is in Cancellation State
        if (cancellationStates.has(chatId)) {
            const state = cancellationStates.get(chatId)!;
            clearTimeout(state.timeoutId);
            cancellationStates.delete(chatId);

            try {
                const serverUrl = 'http://localhost:8001/api/bot/cancel';
                const response = await axios.post(serverUrl, {
                    phone: chatId.replace('@c.us', ''),
                    reason: body
                });

                if (response.data.success) {
                    await client.sendMessage(chatId, "✅ Tu cita ha sido cancelada correctamente.");
                } else {
                    await client.sendMessage(chatId, "❌ No encontramos una cita próxima para cancelar o ocurrió un error.");
                }
            } catch (error: any) {
                console.error('API Error:', error);
                await client.sendMessage(chatId, "❌ Error de conexión al procesar tu solicitud.");
            }
            return;
        }

        // 2. Cancellation Trigger
        const cancelKeywords = ["2", "no", "cancelar", "cancel"];
        if (cancelKeywords.includes(lowerBody)) {
            const timeoutId = setTimeout(async () => {
                if (cancellationStates.has(chatId)) {
                    try {
                        const serverUrl = 'http://localhost:8001/api/bot/cancel';
                        const response = await axios.post(serverUrl, {
                            phone: chatId.replace('@c.us', ''),
                            reason: "No dió motivos (Timeout 1min)"
                        });
                        if (response.data.success) {
                            await client.sendMessage(chatId, `Hola, tu cita ha sido cancelada por falta de respuesta.`);
                        }
                    } catch (err) {
                        console.error("Auto-cancel failed", err);
                    }
                    cancellationStates.delete(chatId);
                }
            }, 60000);

            cancellationStates.set(chatId, { step: 'WAITING_REASON', timestamp: Date.now(), timeoutId: timeoutId });
            await client.sendMessage(chatId, "Lamentamos esto. 😟\n\nPor favor indícanos brevemente el *motivo de la cancelación* para procesarla:");
            return;
        }

        const currentState = chatState.get(chatId);
        if (!currentState || currentState !== "WAITING_CONFIRMATION") return;

        const confirmKeywords = ["1", "si", "sí", "confirmar", "confirm", "ok"];
        const text = normalize(message.body);

        if (confirmKeywords.some((w) => text === w || text.includes(w))) {
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

        await message.reply("Por favor responde:\n1️⃣ Confirmar\n2️⃣ Cancelar");

    } catch (handlerError) {
        console.error("Error in message handler:", handlerError);
    }
});

/* =========================
   ENDPOINTS
 ========================= */

// 🏠 DASHBOARD
app.get('/', (req, res) => {
    res.send(`
        <html>
            <head>
                <meta charset="UTF-8">
                <title>Control Bot - Barbería JR</title>
                <style>
                    body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                    .card { background: #1e293b; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3); text-align: center; max-width: 400px; width: 90%; }
                    h1 { color: #38bdf8; margin-bottom: 0.5rem; }
                    .status { display: inline-block; padding: 0.5rem 1rem; border-radius: 2rem; background: #059669; font-weight: bold; margin: 1rem 0; }
                    p { color: #94a3b8; line-height: 1.6; }
                    .btn { display: inline-block; margin-top: 1.5rem; background: #38bdf8; color: #0f172a; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: bold; transition: opacity 0.2s; }
                    .btn:hover { opacity: 0.9; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h1>✂️ Barbería JR</h1>
                    <p>Sistema Automatico de WhatsApp</p>
                    <div class="status">● BOT ONLINE</div>
                    <p>El bot está escuchando solicitudes y procesando confirmaciones en tiempo real.</p>
                    <a href="http://localhost:8001" target="_blank" class="btn">Abrir Panel de Citas</a>
                    <p style="font-size: 10px; margin-top: 2rem;">© 2025 Barbería JR - Tu estilo, nuestra pasión.</p>
                </div>
            </body>
        </html>
    `);
});

app.post("/appointment", async (req, res) => {
    try {
        const { phone, name, date, time, place, barber_name, service_name, is_request, display_price } = req.body;

        if (!phone || !name || !date || !time) {
            return res.status(400).json({ error: "Faltan datos obligatorios" });
        }

        const chatId = phone.includes("@c.us") ? phone : phone.replace(/\D/g, "") + "@c.us";
        appointments.set(chatId, { name, date, time, place: place || 'Barbería JR', barber_name, service_name, is_request });

        if (is_request) {
            chatState.set(chatId, "IDLE");
            await client.sendMessage(chatId,
                `Hola *${name}* 👋\n\n Hemos recibido tu solicitud para:\n 💇‍♂️ *Servicio:* ${service_name || 'Otro Servicio'}\n 💈 *Barbero:* ${barber_name}\n 📅 *Fecha:* ${date} a las ${time}\n\n ⚠️ *Estado:* Tu cita está en *ESPERA DE CONFIRMACIÓN* para ser apartada.\n Nos pondremos en contacto contigo pronto.`
            );
        } else {
            chatState.set(chatId, "WAITING_CONFIRMATION");
            const priceText = display_price || 'Por confirmar';
            await client.sendMessage(chatId,
                `Hola *${name}* 👋\n\n ✅ Tu cita ha sido *CONFIRMADA* en Barbería JR.\n\n 📋 *Detalles:*\n 💇‍♂️ *Servicio:* ${service_name}\n 💈 *Barbero:* ${barber_name}\n 📅 *Fecha:* ${date}\n ⏰ *Hora:* ${time}\n 💰 *Precio:* ${priceText}\n\n Por favor confirma tu asistencia respondiendo:\n 1️⃣ Confirmar\n 2️⃣ Cancelar`
            );
        }
        res.json({ success: true });
    } catch (error: any) {
        console.error('❌ Error in /appointment endpoint:', error.message);
        res.status(500).json({ error: 'Failed', details: error.message });
    }
});

app.post('/send-message', async (req, res) => {
    try {
        const { phone, message } = req.body;
        const chatId = phone.includes("@c.us") ? phone : phone.replace(/\D/g, "") + "@c.us";
        await client.sendMessage(chatId, message);
        res.json({ success: true });
    } catch (error: any) {
        console.error('❌ Error sending message:', error.message);
        res.status(500).json({ error: 'Failed', details: error.message });
    }
});

app.post('/send-pdf', async (req, res) => {
    try {
        const { phone, pdf_url, filename } = req.body;
        const chatId = phone.includes("@c.us") ? phone : phone.replace(/\D/g, "") + "@c.us";
        const MessageMedia = pkg.MessageMedia;
        const media = await MessageMedia.fromUrl(pdf_url, { unsafeMime: true });
        if (filename) media.filename = filename;
        await client.sendMessage(chatId, media, { sendMediaAsDocument: true });
        res.json({ success: true });
    } catch (error: any) {
        console.error('❌ Error sending PDF:', error.message);
        res.status(500).json({ error: 'Failed', details: error.message });
    }
});

/* =========================
   START + ERROR PROTECTION
 ========================= */

process.on('unhandledRejection', (reason, promise) => {
    console.error('⚠️ Unhandled Rejection at:', promise, 'reason:', reason);
});

process.on('uncaughtException', (err) => {
    console.error('⚠️ Uncaught Exception:', err);
});

client.initialize().catch(err => console.error("Initialization error:", err));

app.listen(3000, () => {
    console.log("🚀 Server running on http://localhost:3000");
});
