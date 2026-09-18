/**
 * Coupon Validation API (public)
 */
const express = require('express');
const router = express.Router();

router.post('/validate', (req, res) => {
  try {
    const db = req.app.locals.db;
    const { couponCode, totalAmount } = req.body;

    if (!couponCode) {
      return res.status(400).json({ error: 'Coupon code is required.' });
    }

    const coupon = db.prepare(
      'SELECT * FROM coupons WHERE coupon_code = ? AND active = 1'
    ).get(couponCode.toUpperCase().trim());

    if (!coupon) {
      return res.status(404).json({ error: 'Invalid coupon code.' });
    }

    if (coupon.expiry && new Date(coupon.expiry) < new Date()) {
      return res.status(400).json({ error: 'This coupon has expired.' });
    }

    if (coupon.usage_limit > 0 && coupon.usage_count >= coupon.usage_limit) {
      return res.status(400).json({ error: 'This coupon has reached its usage limit.' });
    }

    if (coupon.minimum_order > 0 && totalAmount && totalAmount < coupon.minimum_order) {
      return res.status(400).json({ error: `Minimum order of ₹${(coupon.minimum_order / 100).toFixed(0)} required.` });
    }

    let discount = 0;
    if (coupon.discount_type === 'flat') {
      discount = coupon.discount_value;
    } else if (coupon.discount_type === 'percentage') {
      discount = totalAmount ? Math.floor(totalAmount * coupon.discount_value / 100) : 0;
    }

    res.json({
      valid: true,
      code: coupon.coupon_code,
      discountType: coupon.discount_type,
      discountValue: coupon.discount_value,
      discountAmount: discount,
      discountDisplay: coupon.discount_type === 'flat'
        ? `₹${(coupon.discount_value / 100).toFixed(0)} off`
        : `${coupon.discount_value}% off`,
    });
  } catch (err) {
    console.error('[COUPON]', err.message);
    res.status(500).json({ error: 'Coupon validation failed.' });
  }
});

module.exports = router;
