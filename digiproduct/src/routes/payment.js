/**
 * Payment API Routes — Razorpay Integration (provider-agnostic adapter)
 */
const express = require('express');
const router = express.Router();
const crypto = require('crypto');

// POST /api/payment/create — Create payment order with gateway
router.post('/create', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { orderNumber } = req.body;

    if (!orderNumber) {
      return res.status(400).json({ error: 'Order number is required.' });
    }

    const order = db.prepare('SELECT * FROM orders WHERE order_number = ?').get(orderNumber);
    if (!order) {
      return res.status(404).json({ error: 'Order not found.' });
    }

    if (order.payment_status === 'PAID') {
      return res.status(400).json({ error: 'This order has already been paid.' });
    }

    // ─── Payment Gateway Integration ──────────────────
    const dbApiKey = db.prepare("SELECT value FROM site_settings WHERE key = 'razorpay_key_id'").get();
    const dbApiSecret = db.prepare("SELECT value FROM site_settings WHERE key = 'razorpay_key_secret'").get();

    const apiKey = (dbApiKey && dbApiKey.value && dbApiKey.value.trim() !== '') ? dbApiKey.value.trim() : process.env.PAYMENT_API_KEY;
    const apiSecret = (dbApiSecret && dbApiSecret.value && dbApiSecret.value.trim() !== '') ? dbApiSecret.value.trim() : process.env.PAYMENT_SECRET;

    if (!apiKey || !apiSecret || apiKey === 'CONFIGURATION_REQUIRED') {
      // CONFIGURATION REQUIRED: Payment gateway credentials not set
      console.warn('[PAYMENT] CONFIGURATION REQUIRED: Payment gateway credentials not set.');
      
      // In development or unconfigured mode, return a mock order for testing
      const mockPaymentOrderId = 'order_dev_' + crypto.randomBytes(8).toString('hex');
      
      db.prepare(`
        UPDATE orders SET payment_status = 'PAYMENT_PENDING', payment_provider = 'razorpay',
        payment_provider_order_id = ?, updated_at = datetime('now')
        WHERE id = ?
      `).run(mockPaymentOrderId, order.id);

      return res.json({
        success: true,
        paymentOrderId: mockPaymentOrderId,
        amount: order.total,
        currency: order.currency,
        orderNumber: order.order_number,
        keyId: apiKey || 'rzp_test_mock_key',
        provider: 'razorpay',
        mode: 'development',
        prefill: getPrefill(db, order.customer_id),
      });
    }

    // ─── Razorpay Order Creation ──────────────────────
    // Using fetch to create Razorpay order
    const Razorpay = requireRazorpay();
    if (!Razorpay) {
      return res.status(503).json({ error: 'Payment provider unavailable.' });
    }

    const razorpay = new Razorpay({ key_id: apiKey, key_secret: apiSecret });

    razorpay.orders.create({
      amount: order.total,
      currency: order.currency,
      receipt: order.order_number,
      notes: {
        order_number: order.order_number,
        order_id: order.id.toString(),
      },
    }).then(paymentOrder => {
      // Update order with payment provider order ID
      db.prepare(`
        UPDATE orders SET payment_status = 'PAYMENT_PENDING', payment_provider = 'razorpay',
        payment_provider_order_id = ?, updated_at = datetime('now')
        WHERE id = ?
      `).run(paymentOrder.id, order.id);

      res.json({
        success: true,
        paymentOrderId: paymentOrder.id,
        amount: order.total,
        currency: order.currency,
        orderNumber: order.order_number,
        keyId: apiKey,
        provider: 'razorpay',
        prefill: getPrefill(db, order.customer_id),
      });
    }).catch(err => {
      console.error('[PAYMENT_CREATE]', err.message);
      res.status(500).json({ error: 'Failed to create payment. Please try again.' });
    });

  } catch (err) {
    console.error('[PAYMENT]', err.message);
    res.status(500).json({ error: 'Payment initialization failed.' });
  }
});

// POST /api/payment/verify — Client-side verification callback (supplementary only)
router.post('/verify', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { orderNumber, razorpay_payment_id, razorpay_order_id, razorpay_signature } = req.body;

    if (!orderNumber) {
      return res.status(400).json({ error: 'Order number required.' });
    }

    const order = db.prepare('SELECT * FROM orders WHERE order_number = ?').get(orderNumber);
    if (!order) {
      return res.status(404).json({ error: 'Order not found.' });
    }

    // If already paid (webhook arrived first), return success
    if (order.payment_status === 'PAID') {
      const accessToken = db.prepare('SELECT token_hash FROM access_tokens WHERE order_id = ?').get(order.id);
      return res.json({
        success: true,
        status: 'PAID',
        accessUrl: accessToken ? `/digiproduct/access/${accessToken.token_hash}` : null,
      });
    }

    // Verify signature if credentials available
    const secret = process.env.PAYMENT_SECRET;
    if (secret && razorpay_payment_id && razorpay_order_id && razorpay_signature) {
      const body = razorpay_order_id + '|' + razorpay_payment_id;
      const expectedSignature = crypto.createHmac('sha256', secret).update(body).digest('hex');

      if (expectedSignature === razorpay_signature) {
        // Signature valid — process payment
        const { processPayment } = require('../services/fulfillment');
        processPayment(db, order, razorpay_payment_id, 'razorpay');

        const accessToken = db.prepare('SELECT token_hash FROM access_tokens WHERE order_id = ?').get(order.id);
        return res.json({
          success: true,
          status: 'PAID',
          accessUrl: accessToken ? `/digiproduct/access/${accessToken.token_hash}` : null,
        });
      }
    }

    // If in development mode without credentials, simulate success
    if (process.env.NODE_ENV !== 'production' && (!secret || secret === '')) {
      const { processPayment } = require('../services/fulfillment');
      processPayment(db, order, 'dev_payment_' + Date.now(), 'development');

      const accessToken = db.prepare('SELECT token_hash FROM access_tokens WHERE order_id = ?').get(order.id);
      return res.json({
        success: true,
        status: 'PAID',
        accessUrl: accessToken ? `/digiproduct/access/${accessToken.token_hash}` : null,
        mode: 'development',
      });
    }

    // Payment pending — webhook hasn't arrived yet
    res.json({
      success: true,
      status: 'PENDING',
      message: 'Your payment is being verified. Please wait.',
    });

  } catch (err) {
    console.error('[PAYMENT_VERIFY]', err.message);
    res.status(500).json({ error: 'Payment verification failed.' });
  }
});

// GET /api/payment/status/:orderNumber
router.get('/status/:orderNumber', (req, res) => {
  try {
    const db = req.app.locals.db;
    const order = db.prepare(
      'SELECT payment_status, order_status FROM orders WHERE order_number = ?'
    ).get(req.params.orderNumber);

    if (!order) {
      return res.status(404).json({ error: 'Order not found.' });
    }

    let accessUrl = null;
    if (order.payment_status === 'PAID') {
      const at = db.prepare(
        "SELECT token_hash FROM access_tokens WHERE order_id = (SELECT id FROM orders WHERE order_number = ?) AND status = 'ACTIVE'"
      ).get(req.params.orderNumber);
      if (at) accessUrl = `/digiproduct/access/${at.token_hash}`;
    }

    res.json({
      paymentStatus: order.payment_status,
      orderStatus: order.order_status,
      accessUrl,
    });
  } catch (err) {
    console.error('[PAYMENT_STATUS]', err.message);
    res.status(500).json({ error: 'Could not check payment status.' });
  }
});

function getPrefill(db, customerId) {
  const customer = db.prepare('SELECT name, email, phone FROM customers WHERE id = ?').get(customerId);
  return customer ? { name: customer.name, email: customer.email, contact: customer.phone } : {};
}

function requireRazorpay() {
  try {
    return require('razorpay');
  } catch {
    console.warn('[PAYMENT] Razorpay SDK not installed. Run: npm install razorpay');
    return null;
  }
}

module.exports = router;
