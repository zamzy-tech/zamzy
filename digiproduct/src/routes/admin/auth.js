/**
 * Admin Auth Routes — Login/Logout
 */
const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');

// POST /api/admin/auth/login
router.post('/login', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({ error: 'Email and password are required.' });
    }

    const admin = db.prepare('SELECT * FROM admin_users WHERE email = ? AND active = 1').get(email.toLowerCase().trim());

    if (!admin || !bcrypt.compareSync(password, admin.password_hash)) {
      // Log failed attempt
      db.prepare(`
        INSERT INTO audit_logs (action, entity_type, ip, user_agent)
        VALUES ('LOGIN_FAILED', 'admin', ?, ?)
      `).run(req.ip, req.get('User-Agent'));

      return res.status(401).json({ error: 'Invalid credentials.' });
    }

    req.session.adminId = admin.id;

    // Update last login
    db.prepare("UPDATE admin_users SET last_login = datetime('now') WHERE id = ?").run(admin.id);

    // Audit log
    db.prepare(`
      INSERT INTO audit_logs (admin_id, action, entity_type, ip, user_agent)
      VALUES (?, 'LOGIN_SUCCESS', 'admin', ?, ?)
    `).run(admin.id, req.ip, req.get('User-Agent'));

    res.json({
      success: true,
      admin: { id: admin.id, email: admin.email, name: admin.name, role: admin.role },
    });
  } catch (err) {
    console.error('[ADMIN_AUTH]', err.message);
    res.status(500).json({ error: 'Login failed.' });
  }
});

// POST /api/admin/auth/logout
router.post('/logout', (req, res) => {
  req.session.destroy();
  res.json({ success: true });
});

// GET /api/admin/auth/check
router.get('/check', (req, res) => {
  if (!req.session || !req.session.adminId) {
    return res.json({ authenticated: false });
  }
  const db = req.app.locals.db;
  const admin = db.prepare('SELECT id, email, name, role FROM admin_users WHERE id = ? AND active = 1').get(req.session.adminId);
  res.json({ authenticated: !!admin, admin: admin || null });
});

module.exports = router;
