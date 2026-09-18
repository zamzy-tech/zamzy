/**
 * Admin Coupons CRUD
 */
const express = require('express');
const router = express.Router();

router.get('/', (req, res) => {
  try {
    const db = req.app.locals.db;
    const coupons = db.prepare('SELECT * FROM coupons ORDER BY created_at DESC').all();
    res.json({ coupons });
  } catch (err) {
    res.status(500).json({ error: 'Failed to load coupons.' });
  }
});

router.post('/', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { coupon_code, discount_type, discount_value, expiry, usage_limit, minimum_order, applicable_products } = req.body;

    if (!coupon_code || !discount_type || discount_value === undefined) {
      return res.status(400).json({ error: 'Code, type, and value are required.' });
    }

    db.prepare(`
      INSERT INTO coupons (coupon_code, discount_type, discount_value, expiry, usage_limit, minimum_order, applicable_products)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `).run(coupon_code.toUpperCase().trim(), discount_type, discount_value, expiry || null, usage_limit || 0, minimum_order || 0, applicable_products || null);

    db.prepare(`
      INSERT INTO audit_logs (admin_id, action, entity_type, new_value, ip)
      VALUES (?, 'COUPON_CREATED', 'coupon', ?, ?)
    `).run(req.admin.id, coupon_code, req.ip);

    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: 'Failed to create coupon.' });
  }
});

router.put('/:id', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { active, expiry, usage_limit } = req.body;

    db.prepare(`
      UPDATE coupons SET active = COALESCE(?, active), expiry = COALESCE(?, expiry), usage_limit = COALESCE(?, usage_limit) WHERE id = ?
    `).run(active, expiry, usage_limit, req.params.id);

    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: 'Failed to update coupon.' });
  }
});

router.delete('/:id', (req, res) => {
  try {
    const db = req.app.locals.db;
    db.prepare('UPDATE coupons SET active = 0 WHERE id = ?').run(req.params.id);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: 'Failed to deactivate coupon.' });
  }
});

module.exports = router;
