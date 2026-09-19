/**
 * ZAMZY Digital Products — Database Schema & Initialization
 * Uses better-sqlite3 for synchronous, reliable SQLite operations.
 */

const Database = require('better-sqlite3');
const path = require('path');
const fs = require('fs');
const bcrypt = require('bcryptjs');

const DATA_DIR = path.join(__dirname, '..', 'data');

function ensureDataDir() {
  if (!fs.existsSync(DATA_DIR)) {
    fs.mkdirSync(DATA_DIR, { recursive: true });
  }
}

function getDb() {
  ensureDataDir();
  const dbPath = process.env.DATABASE_PATH
    ? path.resolve(process.env.DATABASE_PATH)
    : path.join(DATA_DIR, 'zamzy.db');

  const db = new Database(dbPath);
  db.pragma('journal_mode = WAL');
  db.pragma('foreign_keys = ON');
  db.pragma('busy_timeout = 5000');
  return db;
}

function initializeDatabase(db) {
  db.exec(`
    -- =====================
    -- CUSTOMERS
    -- =====================
    CREATE TABLE IF NOT EXISTS customers (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      email TEXT NOT NULL,
      phone TEXT NOT NULL,
      company TEXT,
      country TEXT,
      email_delivery_consent INTEGER DEFAULT 1,
      whatsapp_communication_consent INTEGER DEFAULT 1,
      marketing_consent INTEGER DEFAULT 0,
      created_at TEXT DEFAULT (datetime('now')),
      updated_at TEXT DEFAULT (datetime('now'))
    );
    CREATE UNIQUE INDEX IF NOT EXISTS idx_customers_email ON customers(email);
    CREATE INDEX IF NOT EXISTS idx_customers_phone ON customers(phone);

    -- =====================
    -- PRODUCTS
    -- =====================
    CREATE TABLE IF NOT EXISTS products (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      slug TEXT NOT NULL UNIQUE,
      subtitle TEXT,
      badge TEXT,
      description TEXT,
      price INTEGER NOT NULL,
      old_price INTEGER DEFAULT 0,
      type TEXT NOT NULL DEFAULT 'digital',
      delivery_type TEXT NOT NULL DEFAULT 'DOWNLOAD',
      active INTEGER DEFAULT 1,
      resource_reference TEXT,
      deliverables TEXT,
      specifications TEXT,
      faqs TEXT,
      usage_terms TEXT,
      max_downloads INTEGER DEFAULT 10,
      access_expiry_days INTEGER DEFAULT 90,
      version_policy TEXT DEFAULT 'latest',
      sort_order INTEGER DEFAULT 0,
      is_addon INTEGER DEFAULT 0,
      created_at TEXT DEFAULT (datetime('now')),
      updated_at TEXT DEFAULT (datetime('now'))
    );
    
    -- Migration helper for existing databases
    ALTER TABLE products ADD COLUMN subtitle TEXT;
    ALTER TABLE products ADD COLUMN badge TEXT;
    ALTER TABLE products ADD COLUMN old_price INTEGER DEFAULT 0;
    ALTER TABLE products ADD COLUMN deliverables TEXT;
    ALTER TABLE products ADD COLUMN specifications TEXT;
    ALTER TABLE products ADD COLUMN faqs TEXT;


    -- =====================
    -- PRODUCT VERSIONS
    -- =====================
    CREATE TABLE IF NOT EXISTS product_versions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      product_id INTEGER NOT NULL,
      version_name TEXT NOT NULL,
      version_number INTEGER NOT NULL DEFAULT 1,
      resource_reference TEXT,
      file_metadata TEXT,
      checksum TEXT,
      active INTEGER DEFAULT 1,
      created_at TEXT DEFAULT (datetime('now')),
      FOREIGN KEY (product_id) REFERENCES products(id)
    );

    -- =====================
    -- BUNDLES
    -- =====================
    CREATE TABLE IF NOT EXISTS bundle_items (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      bundle_product_id INTEGER NOT NULL,
      included_product_id INTEGER NOT NULL,
      FOREIGN KEY (bundle_product_id) REFERENCES products(id),
      FOREIGN KEY (included_product_id) REFERENCES products(id)
    );

    -- =====================
    -- ORDERS
    -- =====================
    CREATE TABLE IF NOT EXISTS orders (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      order_number TEXT NOT NULL UNIQUE,
      customer_id INTEGER NOT NULL,
      subtotal INTEGER NOT NULL DEFAULT 0,
      addon_total INTEGER NOT NULL DEFAULT 0,
      discount_total INTEGER NOT NULL DEFAULT 0,
      total INTEGER NOT NULL DEFAULT 0,
      currency TEXT NOT NULL DEFAULT 'INR',
      payment_status TEXT NOT NULL DEFAULT 'CREATED',
      order_status TEXT NOT NULL DEFAULT 'CREATED',
      payment_reference TEXT,
      payment_provider TEXT,
      payment_provider_order_id TEXT,
      terms_version TEXT,
      privacy_version TEXT,
      coupon_id INTEGER,
      utm_source TEXT,
      utm_medium TEXT,
      utm_campaign TEXT,
      utm_content TEXT,
      utm_term TEXT,
      fbclid TEXT,
      ip_address TEXT,
      user_agent TEXT,
      created_at TEXT DEFAULT (datetime('now')),
      updated_at TEXT DEFAULT (datetime('now')),
      FOREIGN KEY (customer_id) REFERENCES customers(id),
      FOREIGN KEY (coupon_id) REFERENCES coupons(id)
    );
    CREATE INDEX IF NOT EXISTS idx_orders_customer ON orders(customer_id);
    CREATE INDEX IF NOT EXISTS idx_orders_payment_status ON orders(payment_status);
    CREATE INDEX IF NOT EXISTS idx_orders_payment_ref ON orders(payment_reference);

    -- =====================
    -- ORDER ITEMS
    -- =====================
    CREATE TABLE IF NOT EXISTS order_items (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      order_id INTEGER NOT NULL,
      product_id INTEGER NOT NULL,
      product_version_id INTEGER,
      price INTEGER NOT NULL,
      quantity INTEGER NOT NULL DEFAULT 1,
      is_addon INTEGER DEFAULT 0,
      FOREIGN KEY (order_id) REFERENCES orders(id),
      FOREIGN KEY (product_id) REFERENCES products(id)
    );
    CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id);

    -- =====================
    -- ACCESS / ENTITLEMENTS
    -- =====================
    CREATE TABLE IF NOT EXISTS entitlements (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      order_id INTEGER NOT NULL,
      product_id INTEGER NOT NULL,
      product_version_id INTEGER,
      secure_token_hash TEXT NOT NULL,
      expires_at TEXT,
      status TEXT NOT NULL DEFAULT 'ACTIVE',
      download_count INTEGER DEFAULT 0,
      max_downloads INTEGER DEFAULT 10,
      created_at TEXT DEFAULT (datetime('now')),
      FOREIGN KEY (order_id) REFERENCES orders(id),
      FOREIGN KEY (product_id) REFERENCES products(id)
    );
    CREATE INDEX IF NOT EXISTS idx_entitlements_order ON entitlements(order_id);
    CREATE INDEX IF NOT EXISTS idx_entitlements_token ON entitlements(secure_token_hash);

    -- =====================
    -- ACCESS TOKENS (per order)
    -- =====================
    CREATE TABLE IF NOT EXISTS access_tokens (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      order_id INTEGER NOT NULL UNIQUE,
      token_hash TEXT NOT NULL UNIQUE,
      expires_at TEXT,
      status TEXT NOT NULL DEFAULT 'ACTIVE',
      created_at TEXT DEFAULT (datetime('now')),
      FOREIGN KEY (order_id) REFERENCES orders(id)
    );
    CREATE INDEX IF NOT EXISTS idx_access_tokens_hash ON access_tokens(token_hash);

    -- =====================
    -- DOWNLOAD LOGS
    -- =====================
    CREATE TABLE IF NOT EXISTS download_logs (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      order_id INTEGER NOT NULL,
      product_id INTEGER NOT NULL,
      entitlement_id INTEGER NOT NULL,
      downloaded_at TEXT DEFAULT (datetime('now')),
      ip TEXT,
      user_agent TEXT,
      FOREIGN KEY (order_id) REFERENCES orders(id),
      FOREIGN KEY (product_id) REFERENCES products(id),
      FOREIGN KEY (entitlement_id) REFERENCES entitlements(id)
    );

    -- =====================
    -- WEBHOOK EVENTS
    -- =====================
    CREATE TABLE IF NOT EXISTS webhook_events (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      provider TEXT NOT NULL,
      event_id TEXT NOT NULL,
      event_type TEXT NOT NULL,
      processed INTEGER DEFAULT 0,
      payload_reference TEXT,
      created_at TEXT DEFAULT (datetime('now'))
    );
    CREATE UNIQUE INDEX IF NOT EXISTS idx_webhook_events_unique ON webhook_events(provider, event_id);

    -- =====================
    -- COUPONS
    -- =====================
    CREATE TABLE IF NOT EXISTS coupons (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      coupon_code TEXT NOT NULL UNIQUE,
      discount_type TEXT NOT NULL DEFAULT 'flat',
      discount_value INTEGER NOT NULL DEFAULT 0,
      expiry TEXT,
      usage_limit INTEGER DEFAULT 0,
      usage_count INTEGER DEFAULT 0,
      minimum_order INTEGER DEFAULT 0,
      applicable_products TEXT,
      active INTEGER DEFAULT 1,
      created_at TEXT DEFAULT (datetime('now'))
    );

    -- =====================
    -- FULFILLMENT LOGS
    -- =====================
    CREATE TABLE IF NOT EXISTS fulfillment_logs (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      order_id INTEGER NOT NULL,
      channel TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT 'PENDING',
      attempts INTEGER DEFAULT 0,
      last_attempt TEXT,
      error_message TEXT,
      created_at TEXT DEFAULT (datetime('now')),
      FOREIGN KEY (order_id) REFERENCES orders(id)
    );

    -- =====================
    -- ADMIN USERS
    -- =====================
    CREATE TABLE IF NOT EXISTS admin_users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      email TEXT NOT NULL UNIQUE,
      password_hash TEXT NOT NULL,
      name TEXT,
      role TEXT DEFAULT 'admin',
      active INTEGER DEFAULT 1,
      last_login TEXT,
      created_at TEXT DEFAULT (datetime('now'))
    );

    -- =====================
    -- AUDIT LOGS
    -- =====================
    CREATE TABLE IF NOT EXISTS audit_logs (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      admin_id INTEGER,
      action TEXT NOT NULL,
      entity_type TEXT,
      entity_id INTEGER,
      old_value TEXT,
      new_value TEXT,
      ip TEXT,
      user_agent TEXT,
      created_at TEXT DEFAULT (datetime('now'))
    );

    -- =====================
    -- SITE SETTINGS
    -- =====================
    CREATE TABLE IF NOT EXISTS site_settings (
      key TEXT PRIMARY KEY,
      value TEXT NOT NULL,
      updated_at TEXT DEFAULT (datetime('now'))
    );

    -- =====================
    -- ANALYTICS EVENTS
    -- =====================
    CREATE TABLE IF NOT EXISTS analytics_events (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      event_type TEXT NOT NULL,
      session_id TEXT,
      page TEXT,
      product_id INTEGER,
      order_id INTEGER,
      utm_source TEXT,
      utm_medium TEXT,
      utm_campaign TEXT,
      utm_content TEXT,
      utm_term TEXT,
      fbclid TEXT,
      ip TEXT,
      user_agent TEXT,
      created_at TEXT DEFAULT (datetime('now'))
    );

    -- =====================
    -- SUPPORT TICKETS
    -- =====================
    CREATE TABLE IF NOT EXISTS support_tickets (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      email TEXT NOT NULL,
      order_number TEXT,
      issue_type TEXT NOT NULL,
      message TEXT NOT NULL,
      status TEXT DEFAULT 'OPEN',
      ip TEXT,
      created_at TEXT DEFAULT (datetime('now'))
    );
  `);
}

function seedProducts(db) {
  const existing = db.prepare('SELECT COUNT(*) as count FROM products').get();
  if (existing.count > 0) return;

  const insertProduct = db.prepare(`
    INSERT INTO products (name, slug, description, price, type, delivery_type, resource_reference, usage_terms, sort_order, is_addon)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `);

  const insertBundle = db.prepare(`
    INSERT INTO bundle_items (bundle_product_id, included_product_id)
    VALUES (?, ?)
  `);

  const usaTerms = 'This digital resource is licensed for the purchaser\'s internal business prospecting use only. Redistribution, resale, or public sharing is prohibited. Data accuracy is not guaranteed. The purchaser is responsible for compliance with applicable laws.';
  const indiaTerms = usaTerms;
  const metaTerms = 'This PDF guide is licensed for personal/business educational use only. Redistribution or resale is prohibited.';

  db.transaction(() => {
    // USA Business Prospects
    insertProduct.run(
      '5L+ USA Business Prospects',
      'usa-business-prospects',
      'A downloadable USA-focused business prospecting resource containing business information, industry data, and company details.',
      24900, // Amount in paise (₹249)
      'digital',
      'DOWNLOAD',
      '', // CONFIGURATION REQUIRED: Set resource reference
      usaTerms,
      1,
      0
    );

    // India Business Leads
    insertProduct.run(
      'India Business Leads',
      'india-business-leads',
      'A downloadable India-focused business prospecting resource with business and industry information.',
      24900,
      'digital',
      'DOWNLOAD',
      '',
      indiaTerms,
      2,
      0
    );

    // Bundle
    insertProduct.run(
      'ZAMZY Business Prospecting Bundle',
      'business-bundle',
      'Get both USA Business Prospects and India Business Leads together at a special bundle price.',
      34900,
      'bundle',
      'DOWNLOAD',
      '',
      usaTerms,
      3,
      0
    );

    // Meta Ads Mastery (add-on)
    insertProduct.run(
      'Meta Ads Mastery',
      'meta-ads-mastery',
      'A practical PDF covering Meta Ads fundamentals, campaign setup, audiences, creatives, budgeting and optimization.',
      4900,
      'digital',
      'DOWNLOAD',
      '',
      metaTerms,
      10,
      1
    );

    // Bundle items: Bundle (id=3) includes USA (id=1) and India (id=2)
    insertBundle.run(3, 1);
    insertBundle.run(3, 2);
  })();
}

function seedAdmin(db) {
  const existing = db.prepare('SELECT COUNT(*) as count FROM admin_users').get();
  if (existing.count > 0) return;

  const email = process.env.ADMIN_DEFAULT_EMAIL || 'zamzytech@gmail.com';
  const password = process.env.ADMIN_DEFAULT_PASSWORD || 'ZamzyAdmin2026!';
  const hash = bcrypt.hashSync(password, 12);

  db.prepare(`
    INSERT INTO admin_users (email, password_hash, name, role)
    VALUES (?, ?, ?, ?)
  `).run(email, hash, 'ZAMZY Admin', 'superadmin');
}

function seedSettings(db) {
  const existing = db.prepare('SELECT COUNT(*) as count FROM site_settings').get();
  if (existing.count > 0) return;

  const settings = {
    'sales_active': '1',
    'terms_version': '1.0',
    'privacy_version': '1.0',
    'support_email': 'work@zamzy.in',
    'support_phone': '+91 7287060553',
    'brand_name': 'ZAMZY',
    'brand_tagline': 'IDEAS THAT MOVE BUSINESS.',
    'brand_url': 'https://zamzy.in',
  };

  const insert = db.prepare('INSERT OR IGNORE INTO site_settings (key, value) VALUES (?, ?)');
  for (const [key, value] of Object.entries(settings)) {
    insert.run(key, value);
  }
}

module.exports = { getDb, initializeDatabase, seedProducts, seedAdmin, seedSettings };
