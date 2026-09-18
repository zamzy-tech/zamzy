/**
 * Admin Customers Routes
 */
const express = require('express');
const router = express.Router();

router.get('/', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { search, page = 1, limit = 20 } = req.query;
    const offset = (page - 1) * limit;

    let query = 'SELECT * FROM customers';
    const params = [];

    if (search) {
      query += ' WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?';
      const s = `%${search}%`;
      params.push(s, s, s);
    }

    query += ` ORDER BY created_at DESC LIMIT ? OFFSET ?`;
    params.push(parseInt(limit), parseInt(offset));

    const customers = db.prepare(query).all(...params);
    res.json({ customers });
  } catch (err) {
    res.status(500).json({ error: 'Failed to load customers.' });
  }
});

router.get('/:id', (req, res) => {
  try {
    const db = req.app.locals.db;
    const customer = db.prepare('SELECT * FROM customers WHERE id = ?').get(req.params.id);
    if (!customer) return res.status(404).json({ error: 'Customer not found.' });

    const orders = db.prepare(`
      SELECT o.* FROM orders o WHERE o.customer_id = ? ORDER BY o.created_at DESC
    `).all(customer.id);

    res.json({ customer, orders });
  } catch (err) {
    res.status(500).json({ error: 'Failed to load customer.' });
  }
});

module.exports = router;
