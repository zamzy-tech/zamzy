/**
 * Admin Site Settings Routes
 */
const express = require('express');
const router = express.Router();

router.get('/', (req, res) => {
  try {
    const db = req.app.locals.db;
    const settings = {};
    db.prepare('SELECT * FROM site_settings').all().forEach(s => {
      settings[s.key] = s.value;
    });
    res.json({ settings });
  } catch (err) {
    res.status(500).json({ error: 'Failed to load settings.' });
  }
});

router.put('/', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { settings } = req.body;

    if (!settings || typeof settings !== 'object') {
      return res.status(400).json({ error: 'Settings object required.' });
    }

    const upsert = db.prepare(`
      INSERT INTO site_settings (key, value, updated_at) VALUES (?, ?, datetime('now'))
      ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at
    `);

    for (const [key, value] of Object.entries(settings)) {
      const old = db.prepare('SELECT value FROM site_settings WHERE key = ?').get(key);
      upsert.run(key, String(value));

      db.prepare(`
        INSERT INTO audit_logs (admin_id, action, entity_type, old_value, new_value, ip)
        VALUES (?, 'SETTING_CHANGED', 'setting', ?, ?, ?)
      `).run(req.admin.id, old?.value || null, String(value), req.ip);
    }

    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: 'Failed to save settings.' });
  }
});

module.exports = router;
