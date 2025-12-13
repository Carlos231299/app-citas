const express = require('express');
// Polyfill for Node 18 compatibility with Baileys
if (!global.crypto) {
    global.crypto = require('crypto');
}
const { default: makeWASocket, DisconnectReason, useMultiFileAuthState, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
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
    const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys_v2');
    const { version, isLatest } = await fetchLatestBaileysVersion();
    console.log(`Using WA v${version.join('.')}, isLatest: ${isLatest}`);

    sock = makeWASocket({
        version,
        auth: state,
        printQRInTerminal: false, // QR disabled for pairing code
        logger: pino({ level: 'debug' }),
        browser: ['Ubuntu', 'Chrome', '20.0.04'],
        syncFullHistory: false,
        connectTimeoutMs: 60000,
    });

    sock.ev.on('creds.update', saveCreds);

    if (!sock.authState.creds.me) {
        console.log('Requesting Pairing Code...');
        setTimeout(async () => {
            try {
                const code = await sock.requestPairingCode('573042189080');
                console.log('PAIRING CODE:', code);
            } catch (err) {
                console.error('Failed to request pairing code:', err);
            }
        }, 5000); // Wait 5s for socket init
    }

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            console.log('QR RECEIVED');
            // We can also print it manually if needed, but printQRInTerminal:true does it.
            // But for logging to file to show user, we might want to ensure it's captured.
            // Baileys 'printQRInTerminal' uses basic console.log
        }

        if (connection === 'close') {
            const error = lastDisconnect.error;
            const statusCode = error?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

            console.error('Connection closed due to:', error);
            console.error('Status Code:', statusCode);
            console.log('Reconnecting:', shouldReconnect);

            isReady = false;

            if (shouldReconnect) {
                // Delay reconnection slightly to avoid tight loops
                setTimeout(connectToWhatsApp, 3000);
            } else {
                console.log('Logged out. Delete auth folder to scan again.');
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
