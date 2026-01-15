import express from "express";
import pkg from "whatsapp-web.js";
import * as qrcode from "qrcode-terminal";
import axios from "axios";

const { Client, LocalAuth, MessageMedia } = pkg;
const app = express();
app.use(express.json());

// Initialize Client
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: false, // Visible browser
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

// --- USER STATE MANAGEMENT ---
const userState = new Map<string, string>();
const API_BASE = 'https://citasbarberiajr.online/api'; // Domain Updated

client.on('qr', (qr) => {
    qrcode.generate(qr, { small: true });
    console.log('SCAN QR CODE ABOVE 👆');
});

client.on('ready', () => {
    console.log('✅ Client is ready!');
});

// --- UNIVERSAL MESSAGE HANDLER ---
client.on('message_create', async msg => {
    try {
        // CRITICAL FIX: IGNORE MESSAGES FROM SELF (THE BOT)
        if (msg.fromMe) return;

        const chat = await msg.getChat();

        // Fix: Bypass broken msg.getContact()
        const rawId = msg.from;
        const phone = rawId.replace(/\D/g, '');
        const body = msg.body.trim();

        if (body === '!ping') {
            msg.reply('pong');
            return;
        }

        // --- STATE HANDLING: WAITING FOR REASON ---
        if (userState.get(phone) === 'WAITING_FOR_REASON') {

            console.log(`🔹 Reason received from ${phone}: ${body}`);
            await chat.sendMessage("⏳ Procesando cancelación...");

            try {
                const response = await axios.post(`${API_BASE}/bot/cancel`, {
                    phone: phone,
                    reason: `Cancelado via WhatsApp (Opción 2): ${body}`
                });

                if (response.data.success) {
                    await chat.sendMessage("❌ Tu cita ha sido cancelada exitosamente.");
                    console.log(`❌ [Bot Reply] Cancelled appointment for ${phone}`);
                } else {
                    await chat.sendMessage("⚠️ No encontramos una cita activa para cancelar.");
                }
            } catch (apiErr: any) {
                console.error("❌ API Cancel Error:", apiErr.message);
                await chat.sendMessage("⚠️ Error al cancelar. Intenta más tarde.");
            }

            // Clear state
            userState.delete(phone);
            return; // Stop
        }

        // --- STATE HANDLING: WAITING FOR RATING ---
        if (userState.get(phone) === 'WAITING_FOR_RATING') {
            // Validate 1-5
            const score = parseInt(body);
            if (isNaN(score) || score < 1 || score > 5) {
                await chat.sendMessage("⚠️ Por favor envía un número válido entre 1 y 5.");
                return;
            }

            await chat.sendMessage("📝 Gracias, estamos guardando tu calificación...");

            try {
                const response = await axios.post(`${API_BASE}/bot/rate`, {
                    phone: phone,
                    score: score
                });

                if (response.data.success) {
                    await chat.sendMessage("🌟 ¡Gracias por tu opinión! Nos ayuda a mejorar.");
                } else {
                    await chat.sendMessage("⚠️ Hubo un problema guardando tu calificación.");
                }
            } catch (apiErr: any) {
                console.error("❌ API Rate Error:", apiErr.message);
                await chat.sendMessage("⚠️ Error de conexión.");
            }

            userState.delete(phone);
            return;
        }

        // --- INTERACTIVE COMMANDS ---

        // Option 1: Confirm
        if (body === '1' || body.toLowerCase() === 'confirmar') {
            await chat.sendMessage("⏳ Confirmando asistencia...");

            try {
                // Call Backend to Log Confirmation and Get Barber Notification Info
                const response = await axios.post(`${API_BASE}/bot/confirm`, { phone: phone });

                if (response.data.success) {
                    await chat.sendMessage("✅ *Excelente, confirmada tu asistencia.* ¡Te esperamos! 💈");

                    // NOTIFY BARBER IF REQUIRED
                    if (response.data.action === 'notify_barber' && response.data.barber_phone) {
                        const barberChatId = formatPhone(response.data.barber_phone);
                        await client.sendMessage(barberChatId, response.data.message);
                        console.log(`📤 Confirmation sent to Barber: ${response.data.barber_phone}`);
                    }
                } else {
                    await chat.sendMessage("⚠️ No encontramos una cita agendada próxima para confirmar.");
                }
            } catch (err: any) {
                console.error("❌ API Confirm Error:", err.message);
                await chat.sendMessage("⚠️ Error al confirmar. Intenta más tarde.");
            }

            userState.delete(phone);
        }

        // Option 2: Cancel -> PROMPT FOR REASON
        else if (body === '2' || body.toLowerCase() === 'cancelar') {
            await chat.sendMessage("📝 Por favor escribe el *motivo de la cancelación*:");
            userState.set(phone, 'WAITING_FOR_REASON');
        }

    } catch (err: any) {
        console.error("ERROR in message handler:", err.message);
    }
});

client.initialize();

// --- HELPER: Format Phone ---
function formatPhone(phone: string): string {
    let chatId = phone.replace(/[^0-9]/g, "");
    if (!chatId.startsWith("57")) chatId = "57" + chatId;
    return chatId + "@c.us";
}

// --- ENDPOINT: NEW APPOINTMENT (Client Notification) ---
app.post('/appointment', async (req, res) => {
    try {
        const { phone, name, date, time, barber_name, service_name, display_price, is_request } = req.body;
        const chatId = formatPhone(phone);

        // USER REQUESTED TEMPLATE
        let message = `Hola ${name} 👋\n\n` +
            `✅ Tu cita ha sido ${is_request ? 'SOLICITADA' : 'CONFIRMADA'} en Barbería JR.\n\n` +
            `📋 *Detalles:*\n` +
            `💇‍♂️ *Servicio:* ${service_name}\n` +
            `💈 *Barbero:* ${barber_name}\n` +
            `📅 *Fecha:* ${date}\n` +
            `⏰ *Hora:* ${time}\n` +
            `💰 *Precio:* ${display_price}\n\n` +
            `Por favor confirma tu asistencia respondiendo:\n` +
            `1️⃣ Confirmar\n` +
            `2️⃣ Cancelar`;

        if (is_request) {
            message += `\n\n⚠️ *Nota:* Espera la confirmación final por parte del barbero.`;
        }

        await client.sendMessage(chatId, message);
        console.log(`✅ Appointment msg sent to Client: ${name} (${phone})`);
        res.json({ success: true });

    } catch (err: any) {
        console.error("❌ Error /appointment:", err.message);
        res.status(500).json({ error: "Failed" });
    }
});

// --- ENDPOINT: REMINDER (Manual/Legacy Call) ---
// Note: This endpoint is less used now that we have polling, but kept for compatibility
app.post('/reminder', async (req, res) => {
    // ... logic matches polling but manual payload ...
    res.json({ success: true, note: "Use polling logic for automatic reminders" });
});

// --- ENDPOINT: SEND GENERIC MESSAGE ---
app.post('/send-message', async (req, res) => {
    try {
        const { phone, message } = req.body;
        const chatId = formatPhone(phone);
        await client.sendMessage(chatId, message);
        console.log(`✅ Generic message sent to ${phone}`);
        res.json({ success: true });
    } catch (err: any) {
        console.error("❌ Error /send-message:", err.message);
        res.status(500).json({ error: "Failed" });
    }
});

// --- ENDPOINT: SEND PDF RECEIPT ---
app.post('/send-pdf', async (req, res) => {
    try {
        const { phone, pdf_url, filename } = req.body;
        const chatId = formatPhone(phone);

        console.log(`📄 Receipt Request: ${filename} for ${phone}`);
        console.log(`🔗 URL: ${pdf_url}`);

        // 1. Fetch PDF Data
        const response = await axios.get(pdf_url, {
            responseType: 'arraybuffer' // Crucial for binary data
        });

        // 2. Convert to Base64
        const pdfBase64 = Buffer.from(response.data, 'binary').toString('base64');

        // 3. Create MessageMedia
        const media = new MessageMedia('application/pdf', pdfBase64, filename);

        // 4. Send
        await client.sendMessage(chatId, media, {
            caption: "Aquí tienes tu recibo digital. 🧾"
        });

        console.log(`✅ PDF Receipt sent to ${phone}`);
        res.json({ success: true });

    } catch (err: any) {
        console.error("❌ Error /send-pdf:", err.message);
        res.status(500).json({ error: "Failed to send PDF" });
    }
});

// --- ENDPOINT: ASK FOR RATING ---
app.post('/ask-rating', async (req, res) => {
    try {
        const { phone } = req.body;
        const chatId = formatPhone(phone);

        console.log(`⭐ Asking for rating: ${phone}`);

        const message = `🌟 *Califica tu servicio* 🌟\n\n` +
            `Nos encantaría saber qué tal te pareció tu experiencia.\n` +
            `Por favor responde con un número del *1 al 5* para calificar a tu barbero.\n\n` +
            `1️⃣ ⭐ Muy Malo\n` +
            `2️⃣ ⭐⭐ Regular\n` +
            `3️⃣ ⭐⭐⭐ Bueno\n` +
            `4️⃣ ⭐⭐⭐⭐ Muy Bueno\n` +
            `5️⃣ ⭐⭐⭐⭐⭐ Excelente`;

        await client.sendMessage(chatId, message);

        // Set State
        const rawPhone = phone.replace(/\D/g, '');
        userState.set(rawPhone, 'WAITING_FOR_RATING');

        console.log(`✅ Rating prompt sent to ${phone}`);
        res.json({ success: true });

    } catch (err: any) {
        console.error("❌ Error /ask-rating:", err.message);
        res.status(500).json({ error: "Failed" });
    }
});

// --- POLLING: BARBER NOTIFICATIONS (NEW APPOINTMENTS) --- //
async function checkNotifications() {
    try {
        const response = await axios.get(`${API_BASE}/notifications/pending`);
        const appointments = response.data;

        if (appointments && appointments.length > 0) {
            console.log(`🔔 Found ${appointments.length} pending NEW APPOINTMENT notifications.`);

            for (const appt of appointments) {
                const chatId = formatPhone(appt.barber_phone);
                const type = appt.is_request ? "📝 SOLICITUD DE CITA" : "📅 NUEVA CITA";

                const message = `🔔 *${type}*\n\n` +
                    `👤 *Cliente:* ${appt.client_name}\n` +
                    `📅 *Fecha:* ${appt.date}\n` +
                    `⏰ *Hora:* ${appt.time}\n` +
                    `✂️ *Servicio:* ${appt.service}\n\n` +
                    `Por favor revisa el panel para más detalles.`;

                await client.sendMessage(chatId, message);
                console.log(`📤 Notification sent to Barber: ${appt.barber_name}`);
                await axios.post(`${API_BASE}/notifications/mark-sent`, { id: appt.id });
            }
        }
    } catch (error: any) {
        // Silent fail 
    }
}

// --- POLLING: CLIENT REMINDERS (15 MIN BEFORE) --- //
async function checkReminders() {
    try {
        const response = await axios.get(`${API_BASE}/reminders/pending`);
        const reminders = response.data;

        if (reminders && reminders.length > 0) {
            console.log(`⏰ Found ${reminders.length} pending REMINDERS.`);

            for (const rem of reminders) {
                const chatId = formatPhone(rem.phone);

                let message = `⏳ *Recordatorio de Cita*\n\n` +
                    `Hola ${rem.client_name} 👋, tu cita es PRONTO.\n\n` +
                    `📋 *Detalles:*\n` +
                    `💇‍♂️ *Servicio:* ${rem.service_name}\n` +
                    `💈 *Barbero:* ${rem.barber_name}\n` +
                    `📅 *Fecha:* ${rem.date}\n` +
                    `⏰ *Hora:* ${rem.time}\n` +
                    `💰 *Precio:* ${rem.display_price}\n\n` +
                    `Por favor confirma tu asistencia:\n` +
                    `1️⃣ Confirmar\n` +
                    `2️⃣ Cancelar`;

                await client.sendMessage(chatId, message);
                console.log(`⏰ Reminder sent to Client: ${rem.client_name}`);

                // Mark as sent
                await axios.post(`${API_BASE}/reminders/mark-sent`, { id: rem.id });
            }
        }
    } catch (error: any) {
        // Silent fail
    }
}

// Start Loops
setInterval(checkNotifications, 10000); // Every 10s
setInterval(checkReminders, 15000);     // Every 15s

// Global Error Handlers
process.on('uncaughtException', (err) => {
    console.error('💥 Uncaught Exception:', err);
});
process.on('unhandledRejection', (reason, promise) => {
    console.error('⚠️ Unhandled Rejection at:', promise, 'reason:', reason);
});

app.listen(3000, () => {
    console.log('🚀 Bot Server running on port 3000');
    console.log('Endpoints: /appointment, /send-message, /send-pdf');
    console.log('📡 Polling started (Notifications & Reminders)...');
});
