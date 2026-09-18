/**
 * Analytics API — Track frontend events
 */
const express = require('express');
const router = express.Router();

// POST /api/analytics/event
router.post('/event', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { eventType, page, productId, sessionId, attribution } = req.body;

    const validEvents = ['PageView', 'ViewContent', 'AddToCart', 'InitiateCheckout', 'AddPaymentInfo', 'Purchase'];
    if (!eventType || !validEvents.includes(eventType)) {
      return res.status(400).json({ error: 'Invalid event type.' });
    }

    db.prepare(`
      INSERT INTO analytics_events (event_type, session_id, page, product_id, utm_source, utm_medium, utm_campaign, utm_content, utm_term, fbclid, ip, user_agent)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(
      eventType,
      sessionId || null,
      page || null,
      productId || null,
      attribution?.utm_source || null,
      attribution?.utm_medium || null,
      attribution?.utm_campaign || null,
      attribution?.utm_content || null,
      attribution?.utm_term || null,
      attribution?.fbclid || null,
      req.ip,
      req.get('User-Agent'),
    );

    res.json({ success: true });
  } catch (err) {
    console.error('[ANALYTICS]', err.message);
    res.json({ success: false });
  }
});

module.exports = router;
