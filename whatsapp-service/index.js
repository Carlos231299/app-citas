const express = require('express');
const { default: makeWASocket, DisconnectReason, useMultiFileAuthState } = require('@whiskeysockets/baileys');
const pino = require('pino');
const qrcode = require('qrcode-terminal');
const cors = require('cors');

const app = express();
const port = 3000;

app.use(cors());
app.use(express.json());

let sock;
let isReady = false;

async function connectToWhatsApp() {
    const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys');

    sock = makeWASocket({
        auth: state,
        printQRInTerminal: true, // Baileys prints QR natively nicely
        logger: pino({ level: 'silent' }), // Hide debug logs
        browser: ['BarberiaJR', 'Chrome', '1.0.0'] // Custom browser name
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            console.log('QR RECEIVED');
            // We can also print it manually if needed, but printQRInTerminal:true does it.
            // But for logging to file to show user, we might want to ensure it's captured.
            // Baileys 'printQRInTerminal' uses basic console.log
        }

        if (connection === 'close') {
            const shouldReconnect = (lastDisconnect.error)?.output?.statusCode !== DisconnectReason.loggedOut;
            console.log('Connection closed. Reconnecting:', shouldReconnect);
            isReady = false;

            if (shouldReconnect) {
                connectToWhatsApp();
            } else {
                console.log('Logged out. Delete auth_info_baileys to scan again.');
            }
        } else if (connection === 'open') {
            console.log('opened connection');
            isReady = true;
        }
    });
}

// Start connection
connectToWhatsApp();

// API Endpoint to Send Message
app.post('/send', async (req, res) => {
    if (!isReady) {
        return res.status(503).json({ status: 'error', message: 'WhatsApp client not ready' });
    }

    const { phone, message } = req.body;

    if (!phone || !message) {
        return res.status(400).json({ status: 'error', message: 'Missing phone or message' });
    }

    try {
        // Format phone: 573001234567@s.whatsapp.net
        let cleanPhone = phone.replace(/[^0-9]/g, '');
        if (cleanPhone.length === 10) {
            cleanPhone = '57' + cleanPhone;
        }

        const jid = cleanPhone + "@s.whatsapp.net";

        const sentMsg = await sock.sendMessage(jid, { text: message });
        console.log(`Message sent to ${cleanPhone}`);

        res.json({ status: 'success', response: sentMsg });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({ status: 'error', message: error.toString() });
    }
});

// Status Endpoint
app.get('/status', (req, res) => {
    res.json({
        ready: isReady
    });
});

app.listen(port, () => {
    console.log(`WhatsApp Service (Baileys) listening on port ${port}`);
});
