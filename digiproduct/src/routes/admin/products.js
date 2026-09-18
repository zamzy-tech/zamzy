/**
 * Admin Products CRUD Routes
 */
const express = require('express');
const router = express.Router();

// GET /api/admin/products
router.get('/', (req, res) => {
  try {
    const db = req.app.locals.db;
    const products = db.prepare('SELECT * FROM products ORDER BY sort_order ASC').all();
    res.json({ products });
  } catch (err) {
    res.status(500).json({ error: 'Failed to load products.' });
  }
});

// GET /api/admin/products/:id
router.get('/:id', (req, res) => {
  try {
    const db = req.app.locals.db;
    const product = db.prepare('SELECT * FROM products WHERE id = ?').get(req.params.id);
    if (!product) return res.status(404).json({ error: 'Product not found.' });

    const versions = db.prepare('SELECT * FROM product_versions WHERE product_id = ? ORDER BY version_number DESC').all(product.id);
    const bundleItems = db.prepare(`
      SELECT bi.*, p.name as product_name FROM bundle_items bi
      JOIN products p ON p.id = bi.included_product_id
      WHERE bi.bundle_product_id = ?
    `).all(product.id);

    res.json({ product, versions, bundleItems });
  } catch (err) {
    res.status(500).json({ error: 'Failed to load product.' });
  }
});

// PUT /api/admin/products/:id
router.put('/:id', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { name, description, price, active, delivery_type, resource_reference, usage_terms, max_downloads, access_expiry_days } = req.body;

    const existing = db.prepare('SELECT * FROM products WHERE id = ?').get(req.params.id);
    if (!existing) return res.status(404).json({ error: 'Product not found.' });

    // Audit log for price changes
    if (price !== undefined && price !== existing.price) {
      db.prepare(`
        INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, old_value, new_value, ip)
        VALUES (?, 'PRICE_CHANGE', 'product', ?, ?, ?, ?)
      `).run(req.admin.id, existing.id, `₹${(existing.price / 100).toFixed(0)}`, `₹${(price / 100).toFixed(0)}`, req.ip);
    }

    if (active !== undefined && active !== existing.active) {
      db.prepare(`
        INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, old_value, new_value, ip)
        VALUES (?, ?, 'product', ?, ?, ?, ?)
      `).run(req.admin.id, active ? 'PRODUCT_ACTIVATED' : 'PRODUCT_DEACTIVATED', existing.id, String(existing.active), String(active), req.ip);
    }

    db.prepare(`
      UPDATE products SET
        name = COALESCE(?, name),
        description = COALESCE(?, description),
        price = COALESCE(?, price),
        active = COALESCE(?, active),
        delivery_type = COALESCE(?, delivery_type),
        resource_reference = COALESCE(?, resource_reference),
        usage_terms = COALESCE(?, usage_terms),
        max_downloads = COALESCE(?, max_downloads),
        access_expiry_days = COALESCE(?, access_expiry_days),
        updated_at = datetime('now')
      WHERE id = ?
    `).run(name, description, price, active, delivery_type, resource_reference, usage_terms, max_downloads, access_expiry_days, req.params.id);

    res.json({ success: true });
  } catch (err) {
    console.error('[ADMIN_PRODUCTS]', err.message);
    res.status(500).json({ error: 'Failed to update product.' });
  }
});

// POST /api/admin/products
router.post('/', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { name, slug, description, price, type, delivery_type, is_addon } = req.body;

    if (!name || !slug || !price) {
      return res.status(400).json({ error: 'Name, slug, and price are required.' });
    }

    const result = db.prepare(`
      INSERT INTO products (name, slug, description, price, type, delivery_type, is_addon)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `).run(name, slug, description || '', price, type || 'digital', delivery_type || 'DOWNLOAD', is_addon || 0);

    db.prepare(`
      INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, new_value, ip)
      VALUES (?, 'PRODUCT_CREATED', 'product', ?, ?, ?)
    `).run(req.admin.id, result.lastInsertRowid, name, req.ip);

    res.json({ success: true, id: result.lastInsertRowid });
  } catch (err) {
    console.error('[ADMIN_PRODUCTS]', err.message);
    res.status(500).json({ error: 'Failed to create product.' });
  }
});

module.exports = router;
