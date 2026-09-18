/**
 * Products API Routes
 */
const express = require('express');
const router = express.Router();

// GET /api/products — List active products (non-addon)
router.get('/', (req, res) => {
  try {
    const db = req.app.locals.db;
    const products = db.prepare(`
      SELECT id, name, slug, description, price, type, delivery_type, sort_order, is_addon
      FROM products WHERE active = 1
      ORDER BY sort_order ASC
    `).all();

    // Separate main products and addons
    const main = products.filter(p => !p.is_addon);
    const addons = products.filter(p => p.is_addon);

    // Get bundle items
    const bundles = {};
    const bundleRows = db.prepare(`
      SELECT bi.bundle_product_id, bi.included_product_id, p.name, p.slug
      FROM bundle_items bi
      JOIN products p ON p.id = bi.included_product_id
    `).all();
    bundleRows.forEach(row => {
      if (!bundles[row.bundle_product_id]) bundles[row.bundle_product_id] = [];
      bundles[row.bundle_product_id].push({
        id: row.included_product_id,
        name: row.name,
        slug: row.slug,
      });
    });

    // Format prices (paise to rupees display)
    const format = (products) => products.map(p => ({
      ...p,
      priceDisplay: `₹${(p.price / 100).toFixed(0)}`,
      priceRaw: p.price,
      bundleItems: bundles[p.id] || null,
    }));

    res.json({
      products: format(main),
      addons: format(addons),
    });
  } catch (err) {
    console.error('[PRODUCTS]', err.message);
    res.status(500).json({ error: 'Failed to load products.' });
  }
});

// GET /api/products/:slug — Single product details
router.get('/:slug', (req, res) => {
  try {
    const db = req.app.locals.db;
    const product = db.prepare(`
      SELECT id, name, slug, description, price, type, delivery_type, usage_terms, is_addon
      FROM products WHERE slug = ? AND active = 1
    `).get(req.params.slug);

    if (!product) {
      return res.status(404).json({ error: 'Product not found.' });
    }

    // Get bundle items if bundle
    let bundleItems = null;
    if (product.type === 'bundle') {
      bundleItems = db.prepare(`
        SELECT p.id, p.name, p.slug, p.description, p.price
        FROM bundle_items bi
        JOIN products p ON p.id = bi.included_product_id
        WHERE bi.bundle_product_id = ?
      `).all(product.id);
    }

    // Get latest active version
    const version = db.prepare(`
      SELECT id, version_name, version_number
      FROM product_versions
      WHERE product_id = ? AND active = 1
      ORDER BY version_number DESC LIMIT 1
    `).get(product.id);

    res.json({
      ...product,
      priceDisplay: `₹${(product.price / 100).toFixed(0)}`,
      bundleItems,
      currentVersion: version || null,
      deliveryMethod: 'Digital download via secure access link',
      format: product.type === 'digital' ? 'Downloadable file' : 'Multiple downloadable files',
    });
  } catch (err) {
    console.error('[PRODUCT_DETAIL]', err.message);
    res.status(500).json({ error: 'Failed to load product.' });
  }
});

module.exports = router;
