/**
 * Admin Orders Routes
 */
const express = require('express');
const router = express.Router();
const crypto = require('crypto');

// GET /api/admin/orders
router.get('/', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { search, status, page = 1, limit = 20 } = req.query;
    const offset = (page - 1) * limit;

    let query = `
      SELECT o.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone
      FROM orders o
      JOIN customers c ON c.id = o.customer_id
    `;
    const params = [];
    const conditions = [];

    if (search) {
      conditions.push('(o.order_number LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.name LIKE ?)');
      const s = `%${search}%`;
      params.push(s, s, s, s);
    }
    if (status) {
      conditions.push('o.payment_status = ?');
      params.push(status);
    }

    if (conditions.length) query += ' WHERE ' + conditions.join(' AND ');
    query += ` ORDER BY o.created_at DESC LIMIT ? OFFSET ?`;
    params.push(parseInt(limit), parseInt(offset));

    const orders = db.prepare(query).all(...params);

    // Get total count
    let countQuery = 'SELECT COUNT(*) as total FROM orders o JOIN customers c ON c.id = o.customer_id';
    if (conditions.length) countQuery += ' WHERE ' + conditions.join(' AND ');
    const total = db.prepare(countQuery).get(...params.slice(0, -2))?.total || 0;

    res.json({ orders, total, page: parseInt(page), limit: parseInt(limit) });
  } catch (err) {
    console.error('[ADMIN_ORDERS]', err.message);
    res.status(500).json({ error: 'Failed to load orders.' });
  }
});

// GET /api/admin/orders/:id
router.get('/:id', (req, res) => {
  try {
    const db = req.app.locals.db;
    const order = db.prepare(`
      SELECT o.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
      c.company as customer_company, c.country as customer_country
      FROM orders o JOIN customers c ON c.id = o.customer_id WHERE o.id = ?
    `).get(req.params.id);

    if (!order) return res.status(404).json({ error: 'Order not found.' });

    const items = db.prepare(`
      SELECT oi.*, p.name as product_name, p.slug
      FROM order_items oi JOIN products p ON p.id = oi.product_id
      WHERE oi.order_id = ?
    `).all(order.id);

    const entitlements = db.prepare(`
      SELECT e.*, p.name as product_name
      FROM entitlements e JOIN products p ON p.id = e.product_id
      WHERE e.order_id = ?
    `).all(order.id);

    const fulfillment = db.prepare('SELECT * FROM fulfillment_logs WHERE order_id = ?').all(order.id);
    const downloads = db.prepare(`
      SELECT dl.*, p.name as product_name
      FROM download_logs dl JOIN products p ON p.id = dl.product_id
      WHERE dl.order_id = ?
    `).all(order.id);

    const accessToken = db.prepare('SELECT * FROM access_tokens WHERE order_id = ?').get(order.id);

    res.json({ order, items, entitlements, fulfillment, downloads, accessToken });
  } catch (err) {
    console.error('[ADMIN_ORDERS]', err.message);
    res.status(500).json({ error: 'Failed to load order.' });
  }
});

// POST /api/admin/orders/:id/resend-email
router.post('/:id/resend-email', (req, res) => {
  try {
    const db = req.app.locals.db;
    const order = db.prepare('SELECT * FROM orders WHERE id = ? AND payment_status = ?').get(req.params.id, 'PAID');
    if (!order) return res.status(404).json({ error: 'Paid order not found.' });

    const customer = db.prepare('SELECT * FROM customers WHERE id = ?').get(order.customer_id);
    const accessToken = db.prepare("SELECT token_hash FROM access_tokens WHERE order_id = ? AND status = 'ACTIVE'").get(order.id);

    if (!accessToken) return res.status(400).json({ error: 'No active access token.' });

    const accessUrl = `${process.env.APP_URL}/digiproduct/access/${accessToken.token_hash}`;

    const { sendOrderEmail } = require('../../services/email');
    sendOrderEmail(customer, order, accessUrl)
      .then(() => {
        db.prepare(`
          INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, ip)
          VALUES (?, 'RESEND_EMAIL', 'order', ?, ?)
        `).run(req.admin.id, order.id, req.ip);
        res.json({ success: true });
      })
      .catch(err => res.status(500).json({ error: 'Email send failed: ' + err.message }));
  } catch (err) {
    res.status(500).json({ error: 'Failed to resend email.' });
  }
});

// POST /api/admin/orders/:id/resend-whatsapp
router.post('/:id/resend-whatsapp', (req, res) => {
  try {
    const db = req.app.locals.db;
    const order = db.prepare('SELECT * FROM orders WHERE id = ? AND payment_status = ?').get(req.params.id, 'PAID');
    if (!order) return res.status(404).json({ error: 'Paid order not found.' });

    const customer = db.prepare('SELECT * FROM customers WHERE id = ?').get(order.customer_id);
    const accessToken = db.prepare("SELECT token_hash FROM access_tokens WHERE order_id = ? AND status = 'ACTIVE'").get(order.id);

    if (!accessToken) return res.status(400).json({ error: 'No active access token.' });

    const accessUrl = `${process.env.APP_URL}/digiproduct/access/${accessToken.token_hash}`;

    const { sendOrderWhatsApp } = require('../../services/whatsapp');
    sendOrderWhatsApp(customer, order, accessUrl)
      .then(() => {
        db.prepare(`
          INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, ip)
          VALUES (?, 'RESEND_WHATSAPP', 'order', ?, ?)
        `).run(req.admin.id, order.id, req.ip);
        res.json({ success: true });
      })
      .catch(err => res.status(500).json({ error: 'WhatsApp send failed: ' + err.message }));
  } catch (err) {
    res.status(500).json({ error: 'Failed to resend WhatsApp.' });
  }
});

// POST /api/admin/orders/:id/regenerate-access
router.post('/:id/regenerate-access', (req, res) => {
  try {
    const db = req.app.locals.db;
    const order = db.prepare('SELECT * FROM orders WHERE id = ? AND payment_status = ?').get(req.params.id, 'PAID');
    if (!order) return res.status(404).json({ error: 'Paid order not found.' });

    // Revoke old token
    db.prepare("UPDATE access_tokens SET status = 'REVOKED' WHERE order_id = ?").run(order.id);

    // Generate new token
    const newToken = crypto.randomBytes(32).toString('hex');
    const newTokenHash = crypto.createHash('sha256').update(newToken).digest('hex');
    const expiry = new Date(Date.now() + 90 * 24 * 60 * 60 * 1000).toISOString();

    db.prepare(`
      INSERT INTO access_tokens (order_id, token_hash, expires_at, status)
      VALUES (?, ?, ?, 'ACTIVE')
    `).run(order.id, newTokenHash, expiry);

    db.prepare(`
      INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, ip)
      VALUES (?, 'REGENERATE_ACCESS', 'order', ?, ?)
    `).run(req.admin.id, order.id, req.ip);

    res.json({
      success: true,
      accessUrl: `/digiproduct/access/${newTokenHash}`,
    });
  } catch (err) {
    res.status(500).json({ error: 'Failed to regenerate access.' });
  }
});

// POST /api/admin/orders/:id/refund
router.post('/:id/refund', (req, res) => {
  try {
    const db = req.app.locals.db;
    const order = db.prepare('SELECT * FROM orders WHERE id = ?').get(req.params.id);
    if (!order) return res.status(404).json({ error: 'Order not found.' });

    db.transaction(() => {
      db.prepare(`
        UPDATE orders SET payment_status = 'REFUNDED', order_status = 'REFUNDED', updated_at = datetime('now')
        WHERE id = ?
      `).run(order.id);

      db.prepare("UPDATE entitlements SET status = 'REVOKED' WHERE order_id = ?").run(order.id);
      db.prepare("UPDATE access_tokens SET status = 'REVOKED' WHERE order_id = ?").run(order.id);

      db.prepare(`
        INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, old_value, new_value, ip)
        VALUES (?, 'REFUND', 'order', ?, ?, 'REFUNDED', ?)
      `).run(req.admin.id, order.id, order.payment_status, req.ip);
    })();

    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: 'Refund processing failed.' });
  }
});

module.exports = router;
