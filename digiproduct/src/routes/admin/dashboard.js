/**
 * Admin Dashboard Routes — Analytics & Overview
 */
const express = require('express');
const router = express.Router();

// GET /api/admin/dashboard
router.get('/dashboard', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { from, to, product, campaign } = req.query;

    const today = new Date().toISOString().split('T')[0];
    const dateFrom = from || today;
    const dateTo = to || today;

    // Today's stats
    const stats = {
      visitors: db.prepare(`
        SELECT COUNT(DISTINCT session_id) as count FROM analytics_events
        WHERE event_type = 'PageView' AND date(created_at) BETWEEN ? AND ?
      `).get(dateFrom, dateTo)?.count || 0,

      checkoutStarts: db.prepare(`
        SELECT COUNT(*) as count FROM analytics_events
        WHERE event_type = 'InitiateCheckout' AND date(created_at) BETWEEN ? AND ?
      `).get(dateFrom, dateTo)?.count || 0,

      orders: db.prepare(`
        SELECT COUNT(*) as count FROM orders
        WHERE payment_status = 'PAID' AND date(created_at) BETWEEN ? AND ?
      `).get(dateFrom, dateTo)?.count || 0,

      revenue: db.prepare(`
        SELECT COALESCE(SUM(total), 0) as total FROM orders
        WHERE payment_status = 'PAID' AND date(created_at) BETWEEN ? AND ?
      `).get(dateFrom, dateTo)?.total || 0,

      metaAddonSales: db.prepare(`
        SELECT COUNT(*) as count FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        JOIN products p ON p.id = oi.product_id
        WHERE p.slug = 'meta-ads-mastery' AND o.payment_status = 'PAID'
        AND date(o.created_at) BETWEEN ? AND ?
      `).get(dateFrom, dateTo)?.count || 0,

      downloads: db.prepare(`
        SELECT COUNT(*) as count FROM download_logs
        WHERE date(downloaded_at) BETWEEN ? AND ?
      `).get(dateFrom, dateTo)?.count || 0,
    };

    stats.conversionRate = stats.visitors > 0
      ? ((stats.orders / stats.visitors) * 100).toFixed(1)
      : '0.0';

    stats.averageOrderValue = stats.orders > 0
      ? Math.round(stats.revenue / stats.orders)
      : 0;

    stats.revenueDisplay = `₹${(stats.revenue / 100).toFixed(0)}`;
    stats.aovDisplay = `₹${(stats.averageOrderValue / 100).toFixed(0)}`;

    // Recent orders
    const recentOrders = db.prepare(`
      SELECT o.*, c.name as customer_name, c.email as customer_email
      FROM orders o
      JOIN customers c ON c.id = o.customer_id
      ORDER BY o.created_at DESC LIMIT 10
    `).all();

    // Pending fulfillments
    const pendingFulfillments = db.prepare(`
      SELECT fl.*, o.order_number, c.name as customer_name
      FROM fulfillment_logs fl
      JOIN orders o ON o.id = fl.order_id
      JOIN customers c ON c.id = o.customer_id
      WHERE fl.status IN ('PENDING', 'FAILED')
      ORDER BY fl.created_at DESC LIMIT 20
    `).all();

    res.json({ stats, recentOrders, pendingFulfillments });
  } catch (err) {
    console.error('[ADMIN_DASHBOARD]', err.message);
    res.status(500).json({ error: 'Dashboard load failed.' });
  }
});

module.exports = router;
