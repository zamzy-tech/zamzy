/**
 * Admin Audit Log Routes
 */
const express = require('express');
const router = express.Router();

router.get('/', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { page = 1, limit = 50 } = req.query;
    const offset = (page - 1) * limit;

    const logs = db.prepare(`
      SELECT al.*, au.name as admin_name, au.email as admin_email
      FROM audit_logs al
      LEFT JOIN admin_users au ON au.id = al.admin_id
      ORDER BY al.created_at DESC
      LIMIT ? OFFSET ?
    `).all(parseInt(limit), parseInt(offset));

    res.json({ logs });
  } catch (err) {
    res.status(500).json({ error: 'Failed to load audit logs.' });
  }
});

module.exports = router;
