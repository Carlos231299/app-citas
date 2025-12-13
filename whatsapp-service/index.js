const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const cors = require('cors');

const app = express();
const port = 3000;

app.use(cors());
app.use(express.json());

// Initialize WhatsApp Client with LocalAuth for persistence
const client = new Client({
    authStrategy: new LocalAuth({
        clientId: "client-one"
    }),
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

let isReady = false;

// Generate QR Code
client.on('qr', (qr) => {
    console.log('QR RECEIVED', qr);
    qrcode.generate(qr, { small: true });
});

// Client Ready
client.on('ready', () => {
    console.log('Client is ready!');
    isReady = true;
});

// Client Authenticated
client.on('authenticated', () => {
    console.log('AUTHENTICATED');
});

// Client Disconnected
client.on('disconnected', (reason) => {
    console.log('Client was logged out', reason);
    isReady = false;
});

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
        // Format phone number: remove non-digits, ensure country code (default 57 for Colombia if missing), append @c.us
        let cleanPhone = phone.replace(/[^0-9]/g, '');

        if (cleanPhone.length === 10) {
            cleanPhone = '57' + cleanPhone;
        }

        const chatId = cleanPhone + "@c.us";

        const response = await client.sendMessage(chatId, message);
        console.log(`Message sent to ${cleanPhone}`);

        res.json({ status: 'success', response });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({ status: 'error', message: error.toString() });
    }
});

// Status Endpoint
app.get('/status', (req, res) => {
    res.json({
        ready: isReady,
        info: client.info
    });
});

// Start Server
app.listen(port, () => {
    console.log(`WhatsApp Service listening on port ${port}`);

    // Start WhatsApp Client
    console.log('Initializing WhatsApp Client...');
    client.initialize();
});
