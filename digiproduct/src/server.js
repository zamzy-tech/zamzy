/**
 * ZAMZY Digital Products — Main Express Server
 */

require('dotenv').config();
const express = require('express');
const helmet = require('helmet');
const cors = require('cors');
const cookieParser = require('cookie-parser');
const session = require('express-session');
const rateLimit = require('express-rate-limit');
const path = require('path');
const { getDb, initializeDatabase, seedProducts, seedAdmin, seedSettings } = require('./database');

const app = express();
const PORT = process.env.PORT || 3000;

// ─── Database ─────────────────────────────────────────
const db = getDb();
initializeDatabase(db);
seedProducts(db);
seedAdmin(db);
seedSettings(db);

// Share db with routes
app.locals.db = db;

// ─── Security ─────────────────────────────────────────
app.use(helmet({
  contentSecurityPolicy: false,
  crossOriginEmbedderPolicy: false,
}));
app.use(cors({ origin: process.env.APP_URL || 'http://localhost:3000', credentials: true }));

// ─── Body Parsing ─────────────────────────────────────
// Raw body for webhook signature verification
app.use('/api/webhooks', express.raw({ type: 'application/json' }));
app.use(express.json({ limit: '2mb' }));
app.use(express.urlencoded({ extended: true }));
app.use(cookieParser());

// ─── Session (Admin) ─────────────────────────────────
app.use(session({
  secret: process.env.ADMIN_SESSION_SECRET || 'change-this',
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: process.env.NODE_ENV === 'production',
    httpOnly: true,
    maxAge: 4 * 60 * 60 * 1000, // 4 hours
    sameSite: 'lax',
  },
  name: 'zamzy.sid',
}));

// ─── Rate Limiting ────────────────────────────────────
const apiLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 100,
  standardHeaders: true,
  legacyHeaders: false,
  message: { error: 'Too many requests, please try again later.' },
});

const authLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 10,
  message: { error: 'Too many login attempts, please try again later.' },
});

// ─── Static Files ─────────────────────────────────────
app.use(['/digiproduct', '/digiproducts'], express.static(path.join(__dirname, '..', 'public'), {
  maxAge: process.env.NODE_ENV === 'production' ? '1d' : 0,
}));
app.use(['/digiproduct/assets', '/digiproducts/assets', '/assets'], express.static(path.join(__dirname, '..', 'public', 'assets'), {
  maxAge: process.env.NODE_ENV === 'production' ? '7d' : 0,
}));

// ─── API Routes ───────────────────────────────────────
app.use('/api/products', apiLimiter, require('./routes/products'));
app.use('/api/checkout', apiLimiter, require('./routes/checkout'));
app.use('/api/payment', apiLimiter, require('./routes/payment'));
app.use('/api/otp', apiLimiter, require('./routes/otp'));
app.use('/api/webhooks', require('./routes/webhooks'));
app.use('/api/access', apiLimiter, require('./routes/access'));
app.use('/api/recovery', rateLimit({ windowMs: 15 * 60 * 1000, max: 5 }), require('./routes/recovery'));
app.use('/api/analytics', apiLimiter, require('./routes/analytics'));
app.use('/api/support', rateLimit({ windowMs: 60 * 60 * 1000, max: 5 }), require('./routes/support'));
app.use('/api/coupons', apiLimiter, require('./routes/coupons'));

// ─── Admin Routes ─────────────────────────────────────
app.use('/api/admin/auth', authLimiter, require('./routes/admin/auth'));
app.use('/api/admin', require('./routes/admin/middleware'), require('./routes/admin/dashboard'));
app.use('/api/admin/products', require('./routes/admin/middleware'), require('./routes/admin/products'));
app.use('/api/admin/orders', require('./routes/admin/middleware'), require('./routes/admin/orders'));
app.use('/api/admin/customers', require('./routes/admin/middleware'), require('./routes/admin/customers'));
app.use('/api/admin/coupons', require('./routes/admin/middleware'), require('./routes/admin/adminCoupons'));
app.use('/api/admin/settings', require('./routes/admin/middleware'), require('./routes/admin/settings'));
app.use('/api/admin/audit', require('./routes/admin/middleware'), require('./routes/admin/audit'));

// ─── Frontend Page Routes ─────────────────────────────
// Root redirect
app.get('/', (req, res) => {
  res.redirect('/digiproduct');
});

// Admin Panel page
app.get(['/digiproduct/admin', '/digiproducts/admin', '/digiproduct/admin/', '/digiproducts/admin/'], (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'admin', 'index.html'));
});

// Landing page (/digiproduct and /digiproducts)
app.get(['/digiproduct', '/digiproducts', '/digiproduct/', '/digiproducts/'], (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'index.html'));
});

// Product page
app.get(['/digiproduct/products', '/digiproducts/products'], (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'products.html'));
});

// Checkout page
app.get(['/digiproduct/checkout', '/digiproducts/checkout'], (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'checkout.html'));
});

// Payment success/verification page
app.get(['/digiproduct/payment-success', '/digiproducts/payment-success'], (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'payment-success.html'));
});

// Access center
app.get(['/digiproduct/access/:token', '/digiproducts/access/:token'], (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'access.html'));
});

// Order recovery
app.get(['/digiproduct/recover', '/digiproducts/recover'], (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'recover.html'));
});

// Legal pages
app.get('/privacy', (req, res) => res.sendFile(path.join(__dirname, '..', 'public', 'privacy.html')));
app.get('/terms', (req, res) => res.sendFile(path.join(__dirname, '..', 'public', 'terms.html')));
app.get('/refund-policy', (req, res) => res.sendFile(path.join(__dirname, '..', 'public', 'refund-policy.html')));
app.get('/license', (req, res) => res.sendFile(path.join(__dirname, '..', 'public', 'license.html')));
app.get('/contact', (req, res) => res.sendFile(path.join(__dirname, '..', 'public', 'contact.html')));

// Admin SPA
app.get('/admin', (req, res) => res.sendFile(path.join(__dirname, '..', 'public', 'admin', 'index.html')));
app.get('/admin/{*splat}', (req, res) => res.sendFile(path.join(__dirname, '..', 'public', 'admin', 'index.html')));

// ─── Robots.txt / Sitemap ─────────────────────────────
app.get('/robots.txt', (req, res) => {
  res.type('text/plain');
  res.send(`User-agent: *
Allow: /digiproducts
Allow: /privacy
Allow: /terms
Allow: /refund-policy
Allow: /license
Allow: /contact
Disallow: /digiproducts/checkout
Disallow: /digiproducts/payment-success
Disallow: /digiproducts/access/
Disallow: /admin
Disallow: /api/
Sitemap: ${process.env.APP_URL || 'http://localhost:3000'}/sitemap.xml`);
});

app.get('/sitemap.xml', (req, res) => {
  const base = process.env.APP_URL || 'http://localhost:3000';
  res.type('application/xml');
  res.send(`<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc>${base}/digiproducts</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>
  <url><loc>${base}/digiproducts/products</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>
  <url><loc>${base}/privacy</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>
  <url><loc>${base}/terms</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>
  <url><loc>${base}/refund-policy</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>
  <url><loc>${base}/license</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>
  <url><loc>${base}/contact</loc><changefreq>monthly</changefreq><priority>0.5</priority></url>
</urlset>`);
});

// ─── 404 Handler ──────────────────────────────────────
app.use((req, res) => {
  res.status(404).json({ error: 'Not found' });
});

// ─── Error Handler ────────────────────────────────────
app.use((err, req, res, next) => {
  console.error('[SERVER ERROR]', err.message);
  if (process.env.NODE_ENV !== 'production') {
    console.error(err.stack);
  }
  res.status(500).json({ error: 'An internal error occurred. Please try again.' });
});

// ─── Start Server ─────────────────────────────────────
app.listen(PORT, () => {
  console.log(`\n🚀 ZAMZY Digital Products server running at http://localhost:${PORT}`);
  console.log(`📦 Storefront: http://localhost:${PORT}/digiproducts`);
  console.log(`🔧 Admin: http://localhost:${PORT}/admin`);
  console.log(`🌿 Environment: ${process.env.NODE_ENV || 'development'}\n`);
});

module.exports = app;
