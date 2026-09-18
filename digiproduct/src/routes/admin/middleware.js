/**
 * Admin Authentication Middleware
 */
const express = require('express');
const router = express.Router();

router.use((req, res, next) => {
  if (!req.session || !req.session.adminId) {
    return res.status(401).json({ error: 'Authentication required.' });
  }

  const db = req.app.locals.db;
  const admin = db.prepare('SELECT id, email, name, role FROM admin_users WHERE id = ? AND active = 1').get(req.session.adminId);

  if (!admin) {
    req.session.destroy();
    return res.status(401).json({ error: 'Invalid session.' });
  }

  req.admin = admin;
  next();
});

module.exports = router;
