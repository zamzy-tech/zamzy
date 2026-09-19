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
    const { name, slug, subtitle, badge, description, price, old_price, active, type, delivery_type, resource_reference, deliverables, specifications, faqs } = req.body;

    const existing = db.prepare('SELECT * FROM products WHERE id = ?').get(req.params.id);
    if (!existing) return res.status(404).json({ error: 'Product not found.' });

    // Audit log for price changes
    if (price !== undefined && price !== existing.price) {
      db.prepare(`
        INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, old_value, new_value, ip)
        VALUES (?, 'PRICE_CHANGE', 'product', ?, ?, ?, ?)
      `).run(req.admin ? req.admin.id : 1, existing.id, `₹${(existing.price / 100).toFixed(0)}`, `₹${(price / 100).toFixed(0)}`, req.ip);
    }

    db.prepare(`
      UPDATE products SET
        name = COALESCE(?, name),
        slug = COALESCE(?, slug),
        subtitle = COALESCE(?, subtitle),
        badge = COALESCE(?, badge),
        description = COALESCE(?, description),
        price = COALESCE(?, price),
        old_price = COALESCE(?, old_price),
        type = COALESCE(?, type),
        active = COALESCE(?, active),
        delivery_type = COALESCE(?, delivery_type),
        resource_reference = COALESCE(?, resource_reference),
        deliverables = COALESCE(?, deliverables),
        specifications = COALESCE(?, specifications),
        faqs = COALESCE(?, faqs),
        updated_at = datetime('now')
      WHERE id = ?
    `).run(name, slug, subtitle, badge, description, price, old_price, type, active, delivery_type, resource_reference, deliverables, specifications, faqs, req.params.id);

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
    const { name, slug, subtitle, badge, description, price, old_price, type, delivery_type, resource_reference, deliverables, specifications, faqs, is_addon } = req.body;

    if (!name || !slug || !price) {
      return res.status(400).json({ error: 'Name, slug, and price are required.' });
    }

    const result = db.prepare(`
      INSERT INTO products (name, slug, subtitle, badge, description, price, old_price, type, delivery_type, resource_reference, deliverables, specifications, faqs, is_addon)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(name, slug, subtitle || '', badge || '', description || '', price, old_price || 0, type || 'digital', delivery_type || 'DOWNLOAD', resource_reference || '', deliverables || '', specifications || '', faqs || '', is_addon || 0);

    db.prepare(`
      INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, new_value, ip)
      VALUES (?, 'PRODUCT_CREATED', 'product', ?, ?, ?)
    `).run(req.admin ? req.admin.id : 1, result.lastInsertRowid, name, req.ip);

    res.json({ success: true, id: result.lastInsertRowid });
  } catch (err) {
    console.error('[ADMIN_PRODUCTS]', err.message);
    res.status(500).json({ error: 'Failed to create product.' });
  }
});

module.exports = router;
