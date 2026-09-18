/**
 * WhatsApp OTP Verification Routes
 */
const express = require('express');
const router = express.Router();
const crypto = require('crypto');
const { sendWhatsAppOtp, formatPhoneNumber } = require('../services/whatsapp');

// In-memory OTP store (phone -> { otp, expiresAt })
const otpStore = new Map();

// Clean up expired OTPs periodically
setInterval(() => {
  const now = Date.now();
  for (const [phone, data] of otpStore.entries()) {
    if (data.expiresAt < now) {
      otpStore.delete(phone);
    }
  }
}, 60000);

// POST /api/otp/send
router.post('/send', async (req, res) => {
  try {
    const { phone } = req.body;
    if (!phone) {
      return res.status(400).json({ error: 'WhatsApp phone number is required.' });
    }

    const cleanPhone = phone.replace(/\D/g, '');
    if (cleanPhone.length < 10) {
      return res.status(400).json({ error: 'Please enter a valid 10-digit mobile number.' });
    }

    // Generate 6-digit OTP
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    const expiresAt = Date.now() + (10 * 60 * 1000); // 10 minutes

    otpStore.set(cleanPhone, { otp, expiresAt, verified: false });

    // Send via WhatsApp service
    const result = await sendWhatsAppOtp(cleanPhone, otp);

    res.json({
      success: true,
      message: `OTP sent to +${formatPhoneNumber(cleanPhone)} via WhatsApp.`,
      debugLink: result.waMeLink || null,
    });
  } catch (err) {
    console.error('[OTP_SEND]', err.message);
    res.status(500).json({ error: 'Failed to send WhatsApp OTP. Please try again.' });
  }
});

// POST /api/otp/verify
router.post('/verify', (req, res) => {
  try {
    const { phone, otp } = req.body;
    if (!phone || !otp) {
      return res.status(400).json({ error: 'Phone number and OTP code are required.' });
    }

    const cleanPhone = phone.replace(/\D/g, '');
    const stored = otpStore.get(cleanPhone);

    if (!stored) {
      return res.status(400).json({ error: 'No OTP requested for this phone number or OTP has expired.' });
    }

    if (Date.now() > stored.expiresAt) {
      otpStore.delete(cleanPhone);
      return res.status(400).json({ error: 'OTP has expired. Please request a new code.' });
    }

    if (stored.otp.trim() !== otp.trim()) {
      return res.status(400).json({ error: 'Invalid OTP code. Please check your WhatsApp and try again.' });
    }

    // Mark verified
    stored.verified = true;
    otpStore.set(cleanPhone, stored);

    res.json({
      success: true,
      verified: true,
      message: 'WhatsApp number verified successfully!',
    });
  } catch (err) {
    console.error('[OTP_VERIFY]', err.message);
    res.status(500).json({ error: 'OTP verification failed.' });
  }
});

module.exports = router;
