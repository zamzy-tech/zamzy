/**
 * Checkout API Routes
 * Handles order creation with server-side price calculation
 */
const express = require('express');
const router = express.Router();
const crypto = require('crypto');

// POST /api/checkout/create-order
const handleCheckoutOrder = (req, res) => {
  try {
    const db = req.app.locals.db;

    // Check if sales are active
    const salesActive = db.prepare("SELECT value FROM site_settings WHERE key = 'sales_active'").get();
    if (salesActive && salesActive.value === '0') {
      return res.status(503).json({ error: 'Sales are currently paused. Please check back later.' });
    }

    const name = req.body.name || req.body.customerName;
    const email = req.body.email || req.body.customerEmail;
    const phone = req.body.phone || req.body.customerPhone;
    const company = req.body.company;
    const country = req.body.country;
    const productSlug = req.body.productSlug;
    const productId = req.body.productId;
    const addonSlugs = req.body.addons;
    const addonProductId = req.body.addonProductId;
    const couponCode = req.body.couponCode;
    const termsAccepted = req.body.termsAccepted !== undefined ? req.body.termsAccepted : true;
    const attribution = req.body.attribution;

    // ─── Validate required fields ─────────────────────
    if (!name || !email || !phone || (!productSlug && !productId)) {
      return res.status(400).json({ error: 'Name, email, phone, and product selection are required.' });
    }
    if (!termsAccepted) {
      return res.status(400).json({ error: 'You must accept the terms and conditions to proceed.' });
    }

    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return res.status(400).json({ error: 'Please provide a valid email address.' });
    }

    // Basic phone validation
    const phoneClean = phone.replace(/[\s\-\(\)]/g, '');
    if (phoneClean.length < 10) {
      return res.status(400).json({ error: 'Please provide a valid phone number.' });
    }

    // ─── Fetch product ────────────────────────────────
    let mainProduct = null;
    if (productSlug) {
      mainProduct = db.prepare('SELECT * FROM products WHERE slug = ? AND active = 1').get(productSlug);
    } else if (productId) {
      mainProduct = db.prepare('SELECT * FROM products WHERE id = ? AND active = 1').get(productId);
    }

    if (!mainProduct) {
      return res.status(400).json({ error: 'Selected product is not available.' });
    }

    // ─── Fetch addons ─────────────────────────────────
    const addonProducts = [];
    if (addonSlugs && Array.isArray(addonSlugs)) {
      for (const slug of addonSlugs) {
        const addon = db.prepare(
          'SELECT * FROM products WHERE slug = ? AND active = 1 AND is_addon = 1'
        ).get(slug);
        if (addon) addonProducts.push(addon);
      }
    }

    // ─── Server-side price calculation ────────────────
    let subtotal = mainProduct.price;
    let addonTotal = addonProducts.reduce((sum, a) => sum + a.price, 0);
    let discountTotal = 0;

    // ─── Coupon validation ────────────────────────────
    let coupon = null;
    if (couponCode) {
      coupon = db.prepare(
        'SELECT * FROM coupons WHERE coupon_code = ? AND active = 1'
      ).get(couponCode.toUpperCase().trim());

      if (!coupon) {
        return res.status(400).json({ error: 'Invalid coupon code.' });
      }
      if (coupon.expiry && new Date(coupon.expiry) < new Date()) {
        return res.status(400).json({ error: 'This coupon has expired.' });
      }
      if (coupon.usage_limit > 0 && coupon.usage_count >= coupon.usage_limit) {
        return res.status(400).json({ error: 'This coupon has reached its usage limit.' });
      }
      if (coupon.minimum_order > 0 && (subtotal + addonTotal) < coupon.minimum_order) {
        return res.status(400).json({ error: `Minimum order of ₹${(coupon.minimum_order / 100).toFixed(0)} required for this coupon.` });
      }

      // Calculate discount
      if (coupon.discount_type === 'flat') {
        discountTotal = coupon.discount_value;
      } else if (coupon.discount_type === 'percentage') {
        discountTotal = Math.floor((subtotal + addonTotal) * coupon.discount_value / 100);
      }
    }

    const total = Math.max(0, subtotal + addonTotal - discountTotal);

    // ─── Create/find customer ─────────────────────────
    let customer = db.prepare('SELECT * FROM customers WHERE email = ?').get(email.toLowerCase().trim());
    if (!customer) {
      const result = db.prepare(`
        INSERT INTO customers (name, email, phone, company, country)
        VALUES (?, ?, ?, ?, ?)
      `).run(name.trim(), email.toLowerCase().trim(), phoneClean, company || null, country || null);
      customer = { id: result.lastInsertRowid };
    } else {
      // Update customer info
      db.prepare(`
        UPDATE customers SET name = ?, phone = ?, company = ?, country = ?, updated_at = datetime('now')
        WHERE id = ?
      `).run(name.trim(), phoneClean, company || null, country || null, customer.id);
    }

    // ─── Generate order number ────────────────────────
    const orderNumber = 'ZM' + Date.now().toString(36).toUpperCase() + crypto.randomBytes(2).toString('hex').toUpperCase();

    // ─── Get settings versions ────────────────────────
    const termsVer = db.prepare("SELECT value FROM site_settings WHERE key = 'terms_version'").get();
    const privacyVer = db.prepare("SELECT value FROM site_settings WHERE key = 'privacy_version'").get();

    // ─── Create order ─────────────────────────────────
    const orderResult = db.prepare(`
      INSERT INTO orders (
        order_number, customer_id, subtotal, addon_total, discount_total, total, currency,
        payment_status, order_status, terms_version, privacy_version, coupon_id,
        utm_source, utm_medium, utm_campaign, utm_content, utm_term, fbclid,
        ip_address, user_agent
      ) VALUES (?, ?, ?, ?, ?, ?, 'INR', 'CREATED', 'CREATED', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(
      orderNumber,
      customer.id,
      subtotal,
      addonTotal,
      discountTotal,
      total,
      termsVer?.value || '1.0',
      privacyVer?.value || '1.0',
      coupon?.id || null,
      attribution?.utm_source || null,
      attribution?.utm_medium || null,
      attribution?.utm_campaign || null,
      attribution?.utm_content || null,
      attribution?.utm_term || null,
      attribution?.fbclid || null,
      req.ip || null,
      req.get('User-Agent') || null,
    );

    const orderId = orderResult.lastInsertRowid;

    // ─── Create order items ───────────────────────────
    const insertItem = db.prepare(`
      INSERT INTO order_items (order_id, product_id, price, quantity, is_addon)
      VALUES (?, ?, ?, 1, ?)
    `);

    insertItem.run(orderId, mainProduct.id, mainProduct.price, 0);
    for (const addon of addonProducts) {
      insertItem.run(orderId, addon.id, addon.price, 1);
    }

    // ─── Update coupon usage ──────────────────────────
    if (coupon) {
      db.prepare('UPDATE coupons SET usage_count = usage_count + 1 WHERE id = ?').run(coupon.id);
    }

    // ─── Track analytics event ────────────────────────
    try {
      db.prepare(`
        INSERT INTO analytics_events (event_type, order_id, product_id, utm_source, utm_medium, utm_campaign, ip, user_agent)
        VALUES ('InitiateCheckout', ?, ?, ?, ?, ?, ?, ?)
      `).run(
        orderId,
        mainProduct.id,
        attribution?.utm_source || null,
        attribution?.utm_medium || null,
        attribution?.utm_campaign || null,
        req.ip || null,
        req.get('User-Agent') || null
      );
    } catch (analyticsErr) {
      console.warn('[CHECKOUT_ANALYTICS_WARN]', analyticsErr.message);
    }

    res.json({
      success: true,
      orderNumber,
      orderId,
      total,
      totalDisplay: `₹${(total / 100).toFixed(0)}`,
      currency: 'INR',
    });

  } catch (err) {
    console.error('[CHECKOUT]', err.stack || err.message);
    res.status(500).json({ error: 'Failed to create order. ' + (err.message || 'Please try again.') });
  }
};

router.post('/', handleCheckoutOrder);
router.post('/create-order', handleCheckoutOrder);

module.exports = router;
