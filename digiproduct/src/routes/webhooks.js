/**
 * Webhook Routes — Payment webhook handler with signature verification & idempotency
 */
const express = require('express');
const router = express.Router();
const crypto = require('crypto');

// POST /api/webhooks/razorpay
router.post('/razorpay', (req, res) => {
  try {
    const db = req.app.locals.db;
    const webhookSecret = process.env.PAYMENT_WEBHOOK_SECRET;

    // ─── Signature Verification ───────────────────────
    if (webhookSecret) {
      const signature = req.headers['x-razorpay-signature'];
      if (!signature) {
        console.warn('[WEBHOOK] Missing signature header');
        return res.status(401).json({ error: 'Missing signature.' });
      }

      const body = typeof req.body === 'string' ? req.body : req.body.toString();
      const expectedSignature = crypto.createHmac('sha256', webhookSecret).update(body).digest('hex');

      if (expectedSignature !== signature) {
        console.warn('[WEBHOOK] Invalid signature');
        return res.status(401).json({ error: 'Invalid signature.' });
      }
    } else if (process.env.NODE_ENV === 'production') {
      console.error('[WEBHOOK] CRITICAL: Webhook secret not configured in production!');
      return res.status(500).json({ error: 'Webhook configuration error.' });
    }

    // Parse body
    const payload = typeof req.body === 'string' || Buffer.isBuffer(req.body)
      ? JSON.parse(req.body)
      : req.body;

    const eventId = payload.event || 'unknown';
    const eventType = payload.event || 'unknown';

    // ─── Idempotency Check ────────────────────────────
    const existing = db.prepare(
      'SELECT id, processed FROM webhook_events WHERE provider = ? AND event_id = ?'
    ).get('razorpay', payload.payload?.payment?.entity?.id || eventId);

    if (existing && existing.processed) {
      console.log('[WEBHOOK] Duplicate event, already processed:', eventId);
      return res.json({ status: 'already_processed' });
    }

    // Store webhook event
    if (!existing) {
      db.prepare(`
        INSERT INTO webhook_events (provider, event_id, event_type, payload_reference)
        VALUES (?, ?, ?, ?)
      `).run('razorpay', payload.payload?.payment?.entity?.id || eventId, eventType, JSON.stringify({ event: eventType }));
    }

    // ─── Process Payment Events ───────────────────────
    if (eventType === 'payment.captured' || eventType === 'order.paid') {
      const paymentEntity = payload.payload?.payment?.entity;
      if (!paymentEntity) {
        console.warn('[WEBHOOK] No payment entity in payload');
        return res.json({ status: 'ignored' });
      }

      const orderId = paymentEntity.notes?.order_id || paymentEntity.notes?.order_number;
      const orderNumber = paymentEntity.notes?.order_number;

      // Find our order
      let order;
      if (orderNumber) {
        order = db.prepare('SELECT * FROM orders WHERE order_number = ?').get(orderNumber);
      }
      if (!order && paymentEntity.order_id) {
        order = db.prepare('SELECT * FROM orders WHERE payment_provider_order_id = ?').get(paymentEntity.order_id);
      }

      if (!order) {
        console.warn('[WEBHOOK] Order not found for payment:', paymentEntity.id);
        return res.json({ status: 'order_not_found' });
      }

      // ─── Amount Verification ──────────────────────────
      if (paymentEntity.amount !== order.total) {
        console.error('[WEBHOOK] Amount mismatch!', {
          expected: order.total,
          received: paymentEntity.amount,
          order: order.order_number,
        });
        return res.status(400).json({ error: 'Amount mismatch.' });
      }

      // ─── Currency Verification ────────────────────────
      if (paymentEntity.currency && paymentEntity.currency !== order.currency) {
        console.error('[WEBHOOK] Currency mismatch!', {
          expected: order.currency,
          received: paymentEntity.currency,
        });
        return res.status(400).json({ error: 'Currency mismatch.' });
      }

      // ─── Process Fulfillment ──────────────────────────
      const { processPayment } = require('../services/fulfillment');
      const result = processPayment(db, order, paymentEntity.id, 'razorpay');

      // Mark webhook as processed
      db.prepare(`
        UPDATE webhook_events SET processed = 1
        WHERE provider = 'razorpay' AND event_id = ?
      `).run(paymentEntity.id);

      console.log('[WEBHOOK] Payment processed:', order.order_number, result);

    } else if (eventType === 'payment.failed') {
      const paymentEntity = payload.payload?.payment?.entity;
      if (paymentEntity?.notes?.order_number) {
        db.prepare(`
          UPDATE orders SET payment_status = 'FAILED', updated_at = datetime('now')
          WHERE order_number = ?
        `).run(paymentEntity.notes.order_number);
      }

    } else if (eventType === 'refund.created' || eventType === 'refund.processed') {
      const refundEntity = payload.payload?.refund?.entity;
      if (refundEntity?.payment_id) {
        const order = db.prepare(
          'SELECT * FROM orders WHERE payment_reference = ?'
        ).get(refundEntity.payment_id);

        if (order) {
          db.prepare(`
            UPDATE orders SET payment_status = 'REFUNDED', order_status = 'REFUNDED', updated_at = datetime('now')
            WHERE id = ?
          `).run(order.id);

          // Revoke entitlements
          db.prepare(`
            UPDATE entitlements SET status = 'REVOKED'
            WHERE order_id = ?
          `).run(order.id);

          // Revoke access token
          db.prepare(`
            UPDATE access_tokens SET status = 'REVOKED'
            WHERE order_id = ?
          `).run(order.id);
        }
      }
    }

    res.json({ status: 'ok' });

  } catch (err) {
    console.error('[WEBHOOK_ERROR]', err.message);
    res.status(500).json({ error: 'Webhook processing failed.' });
  }
});

module.exports = router;
