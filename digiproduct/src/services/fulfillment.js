/**
 * Fulfillment Service — Handles post-payment order fulfillment
 * Creates entitlements, access tokens, queues email/WhatsApp
 */
const crypto = require('crypto');

/**
 * Process a verified payment and create all entitlements
 */
function processPayment(db, order, paymentReference, provider) {
  // Idempotency: check if already paid
  const current = db.prepare('SELECT payment_status FROM orders WHERE id = ?').get(order.id);
  if (current && current.payment_status === 'PAID') {
    return { alreadyProcessed: true };
  }

  return db.transaction(() => {
    // 1. Update order status
    db.prepare(`
      UPDATE orders SET
        payment_status = 'PAID',
        order_status = 'FULFILLMENT_PENDING',
        payment_reference = ?,
        payment_provider = ?,
        updated_at = datetime('now')
      WHERE id = ?
    `).run(paymentReference, provider, order.id);

    // 2. Get order items
    const items = db.prepare('SELECT * FROM order_items WHERE order_id = ?').all(order.id);

    // 3. Resolve actual products to entitle (expand bundles)
    const productsToEntitle = [];
    for (const item of items) {
      const product = db.prepare('SELECT * FROM products WHERE id = ?').get(item.product_id);
      if (!product) continue;

      if (product.type === 'bundle') {
        // Expand bundle into its constituent products
        const bundleItems = db.prepare(`
          SELECT p.* FROM bundle_items bi
          JOIN products p ON p.id = bi.included_product_id
          WHERE bi.bundle_product_id = ?
        `).all(product.id);
        bundleItems.forEach(bp => productsToEntitle.push(bp));
      } else {
        productsToEntitle.push(product);
      }
    }

    // 4. Get latest product versions
    const getVersion = db.prepare(`
      SELECT id FROM product_versions
      WHERE product_id = ? AND active = 1
      ORDER BY version_number DESC LIMIT 1
    `);

    // 5. Create entitlements for each product
    const insertEntitlement = db.prepare(`
      INSERT INTO entitlements (order_id, product_id, product_version_id, secure_token_hash, expires_at, max_downloads)
      VALUES (?, ?, ?, ?, ?, ?)
    `);

    for (const product of productsToEntitle) {
      const version = getVersion.get(product.id);
      const expiresAt = product.access_expiry_days
        ? new Date(Date.now() + product.access_expiry_days * 24 * 60 * 60 * 1000).toISOString()
        : null;

      // Individual entitlement token (hash stored, not the token itself)
      const entitlementToken = crypto.randomBytes(16).toString('hex');
      const entitlementHash = crypto.createHash('sha256').update(entitlementToken).digest('hex');

      insertEntitlement.run(
        order.id,
        product.id,
        version?.id || null,
        entitlementHash,
        expiresAt,
        product.max_downloads || 10
      );
    }

    // 6. Generate secure access token for the order
    const accessToken = crypto.randomBytes(32).toString('hex');
    const accessTokenHash = crypto.createHash('sha256').update(accessToken).digest('hex');
    const accessExpiry = new Date(Date.now() + 90 * 24 * 60 * 60 * 1000).toISOString(); // 90 days

    db.prepare(`
      INSERT OR REPLACE INTO access_tokens (order_id, token_hash, expires_at, status)
      VALUES (?, ?, ?, 'ACTIVE')
    `).run(order.id, accessTokenHash, accessExpiry);

    // 7. Queue fulfillment (email + WhatsApp)
    db.prepare(`
      INSERT INTO fulfillment_logs (order_id, channel, status)
      VALUES (?, 'EMAIL', 'PENDING')
    `).run(order.id);

    db.prepare(`
      INSERT INTO fulfillment_logs (order_id, channel, status)
      VALUES (?, 'WHATSAPP', 'PENDING')
    `).run(order.id);

    // 8. Update order status
    db.prepare(`
      UPDATE orders SET order_status = 'FULFILLED', updated_at = datetime('now')
      WHERE id = ?
    `).run(order.id);

    // 9. Track analytics
    db.prepare(`
      INSERT INTO analytics_events (event_type, order_id, ip, user_agent)
      VALUES ('Purchase', ?, ?, ?)
    `).run(order.id, null, null);

    // 10. Attempt email and WhatsApp delivery
    const customer = db.prepare('SELECT * FROM customers WHERE id = ?').get(order.customer_id);
    const accessUrl = `${process.env.APP_URL || 'http://localhost:3000'}/digiproduct/access/${accessTokenHash}`;

    // Email
    try {
      const { sendOrderEmail } = require('./email');
      sendOrderEmail(customer, order, accessUrl).then(() => {
        db.prepare(`
          UPDATE fulfillment_logs SET status = 'SENT', last_attempt = datetime('now')
          WHERE order_id = ? AND channel = 'EMAIL'
        `).run(order.id);
      }).catch(err => {
        console.error('[EMAIL_FAIL]', err.message);
        db.prepare(`
          UPDATE fulfillment_logs SET status = 'FAILED', attempts = attempts + 1,
          last_attempt = datetime('now'), error_message = ?
          WHERE order_id = ? AND channel = 'EMAIL'
        `).run(err.message, order.id);
      });
    } catch (err) {
      console.error('[EMAIL_SERVICE]', err.message);
    }

    // WhatsApp
    try {
      const { sendOrderWhatsApp } = require('./whatsapp');
      sendOrderWhatsApp(customer, order, accessUrl).then(() => {
        db.prepare(`
          UPDATE fulfillment_logs SET status = 'SENT', last_attempt = datetime('now')
          WHERE order_id = ? AND channel = 'WHATSAPP'
        `).run(order.id);
      }).catch(err => {
        console.error('[WHATSAPP_FAIL]', err.message);
        db.prepare(`
          UPDATE fulfillment_logs SET status = 'FAILED', attempts = attempts + 1,
          last_attempt = datetime('now'), error_message = ?
          WHERE order_id = ? AND channel = 'WHATSAPP'
        `).run(err.message, order.id);
      });
    } catch (err) {
      console.error('[WHATSAPP_SERVICE]', err.message);
    }

    return {
      alreadyProcessed: false,
      accessToken: accessTokenHash, // stored hash used as URL token
      productsEntitled: productsToEntitle.map(p => p.name),
    };
  })();
}

module.exports = { processPayment };
