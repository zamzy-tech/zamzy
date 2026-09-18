/**
 * Order Recovery API — Allows customers to recover their access
 */
const express = require('express');
const router = express.Router();

// POST /api/recovery/lookup
router.post('/lookup', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { email, orderNumber } = req.body;

    if (!email || !orderNumber) {
      return res.status(400).json({ error: 'Both email and order number are required.' });
    }

    const order = db.prepare(`
      SELECT o.id, o.order_number, o.payment_status
      FROM orders o
      JOIN customers c ON c.id = o.customer_id
      WHERE o.order_number = ? AND LOWER(c.email) = LOWER(?)
      AND o.payment_status = 'PAID'
    `).get(orderNumber.trim(), email.trim());

    if (!order) {
      return res.status(404).json({ error: 'No matching paid order found. Please check your details.' });
    }

    const accessToken = db.prepare(
      "SELECT token_hash FROM access_tokens WHERE order_id = ? AND status = 'ACTIVE'"
    ).get(order.id);

    if (!accessToken) {
      return res.status(404).json({
        error: 'Access link not found. Please contact support at work@zamzy.in',
      });
    }

    res.json({
      success: true,
      accessUrl: `/digiproduct/access/${accessToken.token_hash}`,
    });

  } catch (err) {
    console.error('[RECOVERY]', err.message);
    res.status(500).json({ error: 'Recovery failed. Please contact support.' });
  }
});

module.exports = router;
