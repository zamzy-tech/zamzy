const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const pino = require('pino');
const fs = require('fs');
const path = require('path');
const https = require('https');

// Helper function to send POST requests without relying on global fetch (fully compatible with older Node versions)
function postToWebhook(url, payload) {
    return new Promise((resolve, reject) => {
        try {
            const parsedUrl = new URL(url);
            const postData = JSON.stringify(payload);
            
            const options = {
                hostname: parsedUrl.hostname,
                port: parsedUrl.port || (parsedUrl.protocol === 'https:' ? 443 : 80),
                path: parsedUrl.pathname + parsedUrl.search,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Content-Length': Buffer.byteLength(postData)
                },
                timeout: 5000
            };
            
            const req = https.request(options, (res) => {
                let data = '';
                res.on('data', (chunk) => { data += chunk; });
                res.on('end', () => {
                    try {
                        resolve(JSON.parse(data));
                    } catch (e) {
                        resolve(null);
                    }
                });
            });
            
            req.on('error', (e) => {
                reject(e);
            });
            
            req.on('timeout', () => {
                req.destroy();
                reject(new Error('Request Timeout'));
            });
            
            req.write(postData);
            req.end();
        } catch (err) {
            reject(err);
        }
    });
}

// Helper function to download file buffers from a URL (supports HTTP and HTTPS)
function downloadBuffer(url) {
    return new Promise((resolve, reject) => {
        try {
            const parsedUrl = new URL(url);
            const client = parsedUrl.protocol === 'https:' ? https : require('http');
            const req = client.get(url, (res) => {
                if (res.statusCode !== 200) {
                    reject(new Error(`Failed to download file, HTTP status: ${res.statusCode}`));
                    return;
                }
                const chunks = [];
                res.on('data', (chunk) => chunks.push(chunk));
                res.on('end', () => resolve(Buffer.concat(chunks)));
            });
            req.on('error', (e) => reject(e));
            req.on('timeout', () => {
                req.destroy();
                reject(new Error('Download timeout'));
            });
            req.setTimeout(8000);
        } catch (err) {
            reject(err);
        }
    });
}

// Helper function to send interactive nativeFlowMessage buttons
async function sendInteractiveButtons(sock, jid, text, buttons, headerText = '', footerText = '') {
    if (!generateWAMessageFromContent || !proto) {
        // Fallback to text message if imports not available
        await sock.sendMessage(jid, { text: text });
        return null;
    }
    
    try {
        const formattedButtons = [];
        buttons.forEach((btn) => {
            if (btn.type === 'url') {
                formattedButtons.push({
                    name: 'cta_url',
                    buttonParamsJson: JSON.stringify({
                        display_text: btn.text,
                        url: btn.url,
                        merchant_url: btn.url
                    })
                });
            } else {
                formattedButtons.push({
                    name: 'quick_reply',
                    buttonParamsJson: JSON.stringify({
                        display_text: btn.text,
                        id: btn.id || btn.text.toLowerCase()
                    })
                });
            }
        });

        const msg = generateWAMessageFromContent(jid, {
            viewOnceMessage: {
                message: {
                    messageContextInfo: {
                        deviceListMetadata: {},
                        deviceListMetadataVersion: 2
                    },
                    interactiveMessage: proto.Message.InteractiveMessage.create({
                        body: proto.Message.InteractiveMessage.Body.create({
                            text: text
                        }),
                        footer: proto.Message.InteractiveMessage.Footer.create({
                            text: footerText || "THE EXPERT HUB"
                        }),
                        header: proto.Message.InteractiveMessage.Header.create({
                            title: headerText || "",
                            hasMediaAttachment: false
                        }),
                        nativeFlowMessage: proto.Message.InteractiveMessage.NativeFlowMessage.create({
                            buttons: formattedButtons
                        })
                    })
                }
            }
        }, {});

        await sock.relayMessage(jid, msg.message, { messageId: msg.key.id });
        return msg.key.id;
    } catch (err) {
        console.error(`[WhatsApp Service] Error in sendInteractiveButtons:`, err.message);
        // Final fallback to text
        const fallbackText = text + "\n\n" + buttons.map(b => `👉 ${b.text}${b.url ? ': ' + b.url : ''}`).join("\n");
        const sent = await sock.sendMessage(jid, { text: fallbackText });
        return sent?.key?.id || null;
    }
}

let makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion, Browsers, generateWAMessageFromContent, proto;

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ limit: '50mb', extended: true }));

// Subdirectory routing fix for cPanel / Passenger
app.use((req, res, next) => {
    if (req.url.startsWith('/whatsapp')) {
        req.url = req.url.substring(9);
        if (req.url === '') req.url = '/';
    }
    next();
});

// Session Registry
const sessions = {};

// Helper to get session ID from request
function getSessionId(req) {
    const session = req.query.session || req.query.id || req.body.session || req.body.id || req.headers['x-session-id'] || 'default';
    return session.toString().replace(/[^a-zA-Z0-9_-]/g, '');
}

// Function to initialize or retrieve a session
async function getOrInitSession(sessionId) {
    if (sessions[sessionId]) {
        return sessions[sessionId];
    }

    console.log(`[WhatsApp Service] Creating new isolated session: ${sessionId}`);
    
    const authDir = path.join(__dirname, 'auth_info_baileys', `session_${sessionId}`);
    
    const sessionObj = {
        sock: null,
        connectionStatus: 'DISCONNECTED',
        latestQr: null,
        latestQrBase64: null,
        linkedNumber: null,
        authDir: authDir
    };
    
    sessions[sessionId] = sessionObj;

    // Start initialization in background
    initializeSession(sessionId);
    
    return sessionObj;
}

async function initializeSession(sessionId) {
    const sessionObj = sessions[sessionId];
    if (!sessionObj) return;

    sessionObj.connectionStatus = 'INITIALIZING';
    console.log(`[WhatsApp Service] Initializing WhatsApp session: ${sessionId}...`);

    try {
        if (!makeWASocket) {
            console.log('[WhatsApp Service] Dynamically importing Baileys...');
            const pkgBaileys = await import('@whiskeysockets/baileys');
            makeWASocket = pkgBaileys.makeWASocket;
            useMultiFileAuthState = pkgBaileys.useMultiFileAuthState;
            DisconnectReason = pkgBaileys.DisconnectReason;
            fetchLatestBaileysVersion = pkgBaileys.fetchLatestBaileysVersion;
            Browsers = pkgBaileys.Browsers;
            generateWAMessageFromContent = pkgBaileys.generateWAMessageFromContent;
            proto = pkgBaileys.proto;
        }

        const { version, isLatest } = await fetchLatestBaileysVersion();
        console.log(`[WhatsApp Service] [${sessionId}] Baileys version: ${version.join('.')}, isLatest: ${isLatest}`);

        // Ensure credentials folder exists
        if (!fs.existsSync(path.dirname(sessionObj.authDir))) {
            fs.mkdirSync(path.dirname(sessionObj.authDir), { recursive: true });
        }

        const { state, saveCreds } = await useMultiFileAuthState(sessionObj.authDir);

        const sock = makeWASocket({
            version,
            auth: state,
            printQRInTerminal: false,
            logger: pino({ level: 'error' }),
            browser: Browsers.ubuntu('Chrome')
        });

        sessionObj.sock = sock;

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                sessionObj.latestQr = qr;
                sessionObj.connectionStatus = 'QR_READY';
                try {
                    sessionObj.latestQrBase64 = await qrcode.toDataURL(qr);
                } catch (err) {
                    console.error(`[WhatsApp Service] Error generating QR for ${sessionId}:`, err);
                }
                console.log(`[WhatsApp Service] QR Code ready for session: ${sessionId}.`);
            }

            if (connection === 'open') {
                sessionObj.connectionStatus = 'CONNECTED';
                sessionObj.latestQr = null;
                sessionObj.latestQrBase64 = null;

                const rawUser = sock.user.id;
                sessionObj.linkedNumber = rawUser.split(':')[0] || rawUser;
                console.log(`[WhatsApp Service] Session ${sessionId} connected to +${sessionObj.linkedNumber}!`);
            }

            if (connection === 'close') {
                const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
                console.log(`[WhatsApp Service] Connection closed for ${sessionId}. Reconnecting: ${shouldReconnect}`);

                sessionObj.connectionStatus = 'DISCONNECTED';
                sessionObj.linkedNumber = null;
                sessionObj.latestQr = null;
                sessionObj.latestQrBase64 = null;

                if (shouldReconnect) {
                    setTimeout(() => {
                        initializeSession(sessionId);
                    }, 5000);
                } else {
                    console.log(`[WhatsApp Service] Session ${sessionId} logged out. Clearing auth files...`);
                    cleanupSessionDir(sessionId);
                    delete sessions[sessionId];
                }
            }
        });

        // ── Chatbot Incoming Messages Hook ───────────────────────────────────────
        sock.ev.on('messages.upsert', async (m) => {
            if (m.type !== 'notify') return;
            
            for (const msg of m.messages) {
                // Ignore messages sent by ourselves
                if (msg.key.fromMe) continue;
                
                // Ignore group messages (only respond in DMs)
                const senderJid = msg.key.remoteJid;
                if (!senderJid || senderJid.endsWith('@g.us')) continue;
                
                // Get message text content
                const messageText = msg.message?.conversation || 
                                    msg.message?.extendedTextMessage?.text || 
                                    '';
                if (!messageText.trim()) continue;
                
                const cleanSender = senderJid.split('@')[0];
                
                console.log(`[WhatsApp Service] [${sessionId}] Received message from ${cleanSender}: "${messageText}"`);
                
                // Forward query to the PHP Webhook Receiver
                try {
                    const webhookUrl = 'https://2fa.tehub.in/api/chatbot.php';
                    const resJson = await postToWebhook(webhookUrl, {
                        sender: cleanSender,
                        message: messageText,
                        session_id: sessionId
                    });
                    
                    if (resJson && resJson.reply) {
                        console.log(`[WhatsApp Service] [${sessionId}] Chatbot Auto-Replying to ${cleanSender}: "${resJson.reply.substring(0, 100)}..."`);
                        if (resJson.image_url) {
                            try {
                                console.log(`[WhatsApp Service] [${sessionId}] Downloading chatbot image: ${resJson.image_url}`);
                                const imageBuffer = await downloadBuffer(resJson.image_url);
                                await sock.sendMessage(senderJid, { 
                                    image: imageBuffer, 
                                    caption: resJson.reply 
                                });
                                // Send buttons separately below the image if present
                                if (resJson.buttons && Array.isArray(resJson.buttons) && resJson.buttons.length > 0) {
                                    await sendInteractiveButtons(sock, senderJid, "Select an option:", resJson.buttons);
                                }
                            } catch (imgErr) {
                                console.error(`[WhatsApp Service] [${sessionId}] Image download failed, falling back to text:`, imgErr.message);
                                if (resJson.buttons && Array.isArray(resJson.buttons) && resJson.buttons.length > 0) {
                                    await sendInteractiveButtons(sock, senderJid, resJson.reply, resJson.buttons);
                                } else {
                                    await sock.sendMessage(senderJid, { text: resJson.reply });
                                }
                            }
                        } else {
                            if (resJson.buttons && Array.isArray(resJson.buttons) && resJson.buttons.length > 0) {
                                await sendInteractiveButtons(sock, senderJid, resJson.reply, resJson.buttons);
                            } else {
                                await sock.sendMessage(senderJid, { text: resJson.reply });
                            }
                        }
                    }
                } catch (err) {
                    console.error(`[WhatsApp Service] [${sessionId}] Webhook query failed:`, err.message);
                }
            }
        });

    } catch (error) {
        console.error(`[WhatsApp Service] Session ${sessionId} failed to initialize:`, error);
        sessionObj.connectionStatus = 'DISCONNECTED';
    }
}

function cleanupSessionDir(sessionId) {
    const sessionObj = sessions[sessionId];
    if (!sessionObj) return;
    try {
        if (fs.existsSync(sessionObj.authDir)) {
            fs.rmSync(sessionObj.authDir, { recursive: true, force: true });
            console.log(`[WhatsApp Service] Directory cleared for session: ${sessionId}`);
        }
    } catch (err) {
        console.error(`[WhatsApp Service] Error cleaning directory for ${sessionId}:`, err);
    }
}

// API Endpoints
app.get('/status', async (req, res) => {
    const sessionId = getSessionId(req);
    const session = await getOrInitSession(sessionId);
    res.json({
        success: true,
        status: session.connectionStatus,
        number: session.linkedNumber ? `+${session.linkedNumber}` : null,
        qr: session.latestQrBase64
    });
});

app.get('/qr', async (req, res) => {
    const sessionId = getSessionId(req);
    const session = await getOrInitSession(sessionId);
    
    if (session.connectionStatus === 'CONNECTED') {
        return res.status(400).send('WhatsApp is already connected!');
    }
    if (!session.latestQr) {
        return res.status(404).send('QR code is not ready yet. Please wait...');
    }

    try {
        const qrImageBuffer = await qrcode.toBuffer(session.latestQr);
        res.setHeader('Content-Type', 'image/png');
        res.send(qrImageBuffer);
    } catch (err) {
        res.status(500).send('Error generating QR image.');
    }
});

app.post('/send', async (req, res) => {
    const sessionId = getSessionId(req);
    const session = await getOrInitSession(sessionId);

    const target = req.body.to || req.body.phone || req.body.number;
    const message = req.body.body || req.body.message;

    if (!target || !message) {
        return res.status(400).json({ success: false, error: 'Recipient number ("to") and message content ("body") are required.' });
    }

    if (session.connectionStatus !== 'CONNECTED' || !session.sock) {
        return res.status(503).json({ success: false, error: `WhatsApp session "${sessionId}" is not connected. Please scan QR first.` });
    }

    try {
        let cleanTarget = target.replace(/[^0-9]/g, '');
        if (cleanTarget.length === 10) {
            cleanTarget = '91' + cleanTarget;
        }
        if (!cleanTarget.endsWith('@s.whatsapp.net')) {
            cleanTarget = `${cleanTarget}@s.whatsapp.net`;
        }

        console.log(`[WhatsApp Service] Sending message via session: ${sessionId} to ${cleanTarget}`);

        const pdfBase64 = req.body.pdf || req.body.pdfBase64;
        const filename = req.body.filename || 'document';

        if (pdfBase64) {
            let cleanBase64 = pdfBase64;
            if (cleanBase64.includes(';base64,')) {
                cleanBase64 = cleanBase64.split(';base64,')[1];
            }
            const fileBuffer = Buffer.from(cleanBase64, 'base64');

            // Detect file type by extension
            const ext = filename.split('.').pop().toLowerCase();

            const imageExts = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
            const videoExts = ['mp4', 'mkv', 'avi', 'mov', '3gp', 'webm'];

            if (imageExts.includes(ext)) {
                // Send as inline image with caption
                const mimeMap = { png: 'image/png', jpg: 'image/jpeg', jpeg: 'image/jpeg', gif: 'image/gif', webp: 'image/webp' };
                const imgMsg = await session.sock.sendMessage(cleanTarget, {
                    image: fileBuffer,
                    mimetype: mimeMap[ext] || 'image/jpeg',
                    caption: message
                });
                return res.json({
                    success: true,
                    message: 'Image sent successfully.',
                    messageId: imgMsg?.key?.id
                });

            } else if (videoExts.includes(ext)) {
                // Send as inline video with caption
                const mimeMap = { mp4: 'video/mp4', mkv: 'video/x-matroska', avi: 'video/x-msvideo', mov: 'video/quicktime', '3gp': 'video/3gpp', webm: 'video/webm' };
                const vidMsg = await session.sock.sendMessage(cleanTarget, {
                    video: fileBuffer,
                    mimetype: mimeMap[ext] || 'video/mp4',
                    caption: message
                });
                return res.json({
                    success: true,
                    message: 'Video sent successfully.',
                    messageId: vidMsg?.key?.id
                });

            } else {
                // Send as document (PDF, DOCX, etc.) + separate text message
                const mimeMap = { pdf: 'application/pdf', docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', zip: 'application/zip' };
                const docMsg = await session.sock.sendMessage(cleanTarget, {
                    document: fileBuffer,
                    mimetype: mimeMap[ext] || 'application/octet-stream',
                    fileName: filename
                });
                await session.sock.sendMessage(cleanTarget, { text: message });
                return res.json({
                    success: true,
                    message: 'Document and summary message sent.',
                    messageId: docMsg?.key?.id
                });
            }
        }

        const buttons = req.body.buttons;
        if (buttons && Array.isArray(buttons) && buttons.length > 0) {
            const footer = req.body.footer || '';
            const header = req.body.header || '';
            const msgId = await sendInteractiveButtons(session.sock, cleanTarget, message, buttons, header, footer);
            return res.json({
                success: true,
                message: 'Interactive button message sent successfully.',
                messageId: msgId
            });
        }

        const sentMsg = await session.sock.sendMessage(cleanTarget, { text: message });
        
        res.json({ 
            success: true, 
            message: 'Message sent successfully.', 
            messageId: sentMsg?.key?.id 
        });
    } catch (err) {
        console.error(`[WhatsApp Service] Failed to send in session ${sessionId}:`, err);
        res.status(500).json({ success: false, error: 'Failed to send message: ' + err.message });
    }
});

app.post('/payment-notification', async (req, res) => {
    console.log('[WhatsApp Service] Received payment notification webhook:', JSON.stringify(req.body));
    
    const event = req.body.event;
    const payload = req.body.payload;
    
    if (!payload || !payload.payment || !payload.payment.entity) {
        return res.status(400).send('Invalid payload');
    }
    
    const payment = payload.payment.entity;
    let contact = payment.contact || '';
    // Strip non-numeric characters from contact
    contact = contact.replace(/[^0-9]/g, '');
    if (contact.length === 10) {
        contact = '91' + contact;
    }
    
    if (!contact) {
        return res.status(400).send('No contact number found in payment entity');
    }
    
    const customerName = payment.notes?.name || payment.notes?.client_name || 'Customer';
    const amount = (payment.amount / 100).toFixed(2); // Razorpay amount is in paise
    const currency = payment.currency || 'INR';
    const paymentId = payment.id;
    
    let message = '';
    
    if (event === 'payment.failed') {
        message = `⚠️ *Payment Failed - THE EXPERT HUB*\n\nHi ${customerName},\nYour payment attempt of *${currency} ${amount}* on sale.theexperthub.in has failed.\n\n*Transaction ID:* ${paymentId}\n*Reason:* ${payment.error_description || 'Declined'}\n\nPlease log in to your dashboard to try again. If the amount was debited, it will be refunded automatically.`;
    } else if (event === 'payment.captured' || event === 'order.paid') {
        message = `✅ *Payment Successful - THE EXPERT HUB*\n\nHi ${customerName},\nThank you for your payment! We have successfully received your payment of *${currency} ${amount}*.\n\n*Transaction ID:* ${paymentId}\nYour services have been automatically updated/renewed.\n\nThank you for choosing THE EXPERT HUB!`;
    } else {
        return res.status(200).send('Event not handled: ' + event);
    }
    
    try {
        // Send using the default (admin) session socket if available
        const defaultSession = sessions['default'];
        if (defaultSession && defaultSession.sock && defaultSession.connectionStatus === 'CONNECTED') {
            const cleanTarget = contact.endsWith('@s.whatsapp.net') ? contact : `${contact}@s.whatsapp.net`;
            await defaultSession.sock.sendMessage(cleanTarget, { text: message });
            console.log(`[WhatsApp Service] Payment notification message sent successfully to ${contact}`);
            return res.status(200).send('Notification sent via default session');
        } else {
            // Find the first connected session if default is offline
            const activeSessionId = Object.keys(sessions).find(id => sessions[id].sock && sessions[id].connectionStatus === 'CONNECTED');
            if (activeSessionId) {
                const activeSession = sessions[activeSessionId];
                const cleanTarget = contact.endsWith('@s.whatsapp.net') ? contact : `${contact}@s.whatsapp.net`;
                await activeSession.sock.sendMessage(cleanTarget, { text: message });
                console.log(`[WhatsApp Service] Payment notification message sent successfully via session ${activeSessionId} to ${contact}`);
                return res.status(200).send('Notification sent via session: ' + activeSessionId);
            }
        }
        
        console.error('[WhatsApp Service] No connected WhatsApp sessions available to send notification');
        return res.status(500).send('No connected WhatsApp sessions available');
    } catch (err) {
        console.error('[WhatsApp Service] Failed to send payment notification:', err.message);
        return res.status(500).send('Failed to send WhatsApp message: ' + err.message);
    }
});

app.post('/disconnect', async (req, res) => {
    const sessionId = getSessionId(req);
    const session = sessions[sessionId];
    
    if (!session) {
        return res.json({ success: true, message: 'Session is not active.' });
    }

    console.log(`[WhatsApp Service] Disconnecting session: ${sessionId}`);
    session.connectionStatus = 'DISCONNECTED';

    try {
        if (session.sock) {
            await session.sock.logout();
        }
    } catch (err) {
        console.log(`[WhatsApp Service] Error logging out session ${sessionId}:`, err.message);
    }

    cleanupSessionDir(sessionId);
    delete sessions[sessionId];

    res.json({ success: true, message: `Session "${sessionId}" disconnected successfully.` });
});

// Auto-start default/admin session
getOrInitSession('default');

if (isNaN(PORT)) {
    app.listen(PORT, () => {
        console.log(`====================================================`);
        console.log(`⚡ Multi-Session WhatsApp Microservice is running!`);
        console.log(`🔌 Listening on socket/pipe: ${PORT}`);
        console.log(`====================================================`);
    });
} else {
    app.listen(PORT, '0.0.0.0', () => {
        console.log(`====================================================`);
        console.log(`⚡ Multi-Session WhatsApp Microservice is running!`);
        console.log(`🔌 Listening on: http://localhost:${PORT}`);
        console.log(`====================================================`);
    });
}
