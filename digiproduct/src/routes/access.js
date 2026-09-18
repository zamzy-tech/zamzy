/**
 * Access Center API Routes — Secure product access & download authorization
 */
const express = require('express');
const router = express.Router();
const crypto = require('crypto');

// GET /api/access/:tokenHash — Get access center data
router.get('/:tokenHash', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { tokenHash } = req.params;

    // Find access token
    const accessToken = db.prepare(`
      SELECT at.*, o.order_number, o.payment_status, o.customer_id
      FROM access_tokens at
      JOIN orders o ON o.id = at.order_id
      WHERE at.token_hash = ?
    `).get(tokenHash);

    if (!accessToken) {
      return res.status(404).json({ error: 'This access link is invalid or expired.' });
    }

    if (accessToken.status !== 'ACTIVE') {
      return res.status(403).json({ error: 'This access link has been revoked.' });
    }

    if (accessToken.expires_at && new Date(accessToken.expires_at) < new Date()) {
      return res.status(403).json({ error: 'This access link has expired.' });
    }

    if (accessToken.payment_status !== 'PAID') {
      return res.status(403).json({ error: 'Payment has not been verified for this order.' });
    }

    // Get customer
    const customer = db.prepare('SELECT name, email FROM customers WHERE id = ?').get(accessToken.customer_id);

    // Get entitlements with product details
    const entitlements = db.prepare(`
      SELECT e.*, p.name as product_name, p.slug as product_slug,
             p.delivery_type, p.description
      FROM entitlements e
      JOIN products p ON p.id = e.product_id
      WHERE e.order_id = ? AND e.status = 'ACTIVE'
    `).all(accessToken.order_id);

    const products = entitlements.map(e => ({
      id: e.product_id,
      name: e.product_name,
      slug: e.product_slug,
      deliveryType: e.delivery_type,
      description: e.description,
      downloadCount: e.download_count,
      maxDownloads: e.max_downloads,
      canDownload: e.download_count < e.max_downloads,
      expiresAt: e.expires_at,
    }));

    res.json({
      customerName: customer?.name || 'Customer',
      orderNumber: accessToken.order_number,
      paymentStatus: 'PAID',
      products,
    });

  } catch (err) {
    console.error('[ACCESS]', err.message);
    res.status(500).json({ error: 'Failed to load access information.' });
  }
});

// POST /api/access/:tokenHash/download/:productId — Authorized download
router.post('/:tokenHash/download/:productId', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { tokenHash, productId } = req.params;

    // 1. Validate access token
    const accessToken = db.prepare(`
      SELECT at.*, o.payment_status
      FROM access_tokens at
      JOIN orders o ON o.id = at.order_id
      WHERE at.token_hash = ? AND at.status = 'ACTIVE'
    `).get(tokenHash);

    if (!accessToken) {
      return res.status(403).json({ error: 'This access link is invalid or expired.' });
    }

    if (accessToken.expires_at && new Date(accessToken.expires_at) < new Date()) {
      return res.status(403).json({ error: 'This access link has expired.' });
    }

    // 2. Verify payment status
    if (accessToken.payment_status !== 'PAID') {
      return res.status(403).json({ error: 'Payment not verified.' });
    }

    // 3. Validate entitlement
    const entitlement = db.prepare(`
      SELECT e.*, p.name as product_name, p.resource_reference, p.delivery_type
      FROM entitlements e
      JOIN products p ON p.id = e.product_id
      WHERE e.order_id = ? AND e.product_id = ? AND e.status = 'ACTIVE'
    `).get(accessToken.order_id, parseInt(productId));

    if (!entitlement) {
      return res.status(403).json({ error: 'You do not have access to this product.' });
    }

    // 4. Check expiration
    if (entitlement.expires_at && new Date(entitlement.expires_at) < new Date()) {
      return res.status(403).json({ error: 'Your access to this product has expired.' });
    }

    // 5. Check download limits
    if (entitlement.download_count >= entitlement.max_downloads) {
      return res.status(403).json({
        error: 'Your download limit has been reached. Contact ZAMZY Support if you need assistance.',
      });
    }

    // 6. Log download
    db.prepare(`
      INSERT INTO download_logs (order_id, product_id, entitlement_id, ip, user_agent)
      VALUES (?, ?, ?, ?, ?)
    `).run(accessToken.order_id, parseInt(productId), entitlement.id, req.ip, req.get('User-Agent'));

    // 7. Increment download count
    db.prepare('UPDATE entitlements SET download_count = download_count + 1 WHERE id = ?').run(entitlement.id);

    // 8. Generate short-lived download URL
    // CONFIGURATION REQUIRED: Set up actual storage provider
    if (!entitlement.resource_reference) {
      return res.status(503).json({
        error: 'Your purchase is confirmed, but your access link is temporarily unavailable. Please contact support.',
        support: { email: 'work@zamzy.in', phone: '+91 7287060553' },
      });
    }

    // For now, return the resource reference
    // In production, this should generate a short-lived signed URL via StorageProvider
    res.json({
      success: true,
      downloadUrl: entitlement.resource_reference,
      productName: entitlement.product_name,
      downloadCount: entitlement.download_count + 1,
      maxDownloads: entitlement.max_downloads,
    });

  } catch (err) {
    console.error('[DOWNLOAD]', err.message);
    res.status(500).json({ error: 'Download failed. Please try again.' });
  }
});

module.exports = router;
