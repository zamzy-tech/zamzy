/**
 * Support Ticket API
 */
const express = require('express');
const router = express.Router();

router.post('/submit', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { name, email, orderNumber, issueType, message } = req.body;

    if (!name || !email || !issueType || !message) {
      return res.status(400).json({ error: 'Name, email, issue type, and message are required.' });
    }

    const validTypes = ['Payment problem', "Can't access purchase", 'Download problem', 'Wrong product', 'Refund request', 'Other'];
    if (!validTypes.includes(issueType)) {
      return res.status(400).json({ error: 'Invalid issue type.' });
    }

    db.prepare(`
      INSERT INTO support_tickets (name, email, order_number, issue_type, message, ip)
      VALUES (?, ?, ?, ?, ?, ?)
    `).run(name, email, orderNumber || null, issueType, message, req.ip);

    res.json({ success: true, message: 'Your support request has been submitted. We will get back to you soon.' });
  } catch (err) {
    console.error('[SUPPORT]', err.message);
    res.status(500).json({ error: 'Failed to submit support request.' });
  }
});

module.exports = router;
