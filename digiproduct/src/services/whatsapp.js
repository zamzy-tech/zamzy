/**
 * WhatsApp Service — Provider adapter for WhatsApp notifications
 * Supports Meta WhatsApp Business API and fallback click-to-chat logging
 */

function formatPhoneNumber(phone) {
  if (!phone) return '';
  // Remove all non-numeric characters
  let cleaned = phone.replace(/\D/g, '');
  // If 10 digits (e.g. Indian mobile number without country code), prepend 91
  if (cleaned.length === 10) {
    cleaned = '91' + cleaned;
  }
  return cleaned;
}

async function sendOrderWhatsApp(customer, order, accessUrl) {
  const apiKey = process.env.WHATSAPP_API_KEY;
  const phoneNumberId = process.env.WHATSAPP_PHONE_NUMBER_ID;
  const formattedPhone = formatPhoneNumber(customer.phone);

  const message = `🎉 Payment Successful!

Hi ${customer.name},

Thank you for your purchase from ZAMZY!

Your digital product access is ready.

📥 Access your purchase:
${accessUrl}

Order ID: ${order.order_number}
Amount: ₹${(order.total / 100).toFixed(0)}

Need support?
Email: work@zamzy.in
WhatsApp: +91 7287060553`;

  const waMeLink = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}`;

  if (!apiKey || !phoneNumberId) {
    console.log('\n[WHATSAPP_NOTIFICATION_LOG]');
    console.log(`To: ${customer.name} (${customer.phone} -> +${formattedPhone})`);
    console.log(`Direct WA Link: ${waMeLink}`);
    console.log(`Message:\n${message}\n`);
    return {
      status: 'LOGGED',
      provider: 'console',
      phone: formattedPhone,
      waMeLink,
      message,
    };
  }

  // Meta WhatsApp Business API
  const response = await fetch(`https://graph.facebook.com/v18.0/${phoneNumberId}/messages`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${apiKey}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      messaging_product: 'whatsapp',
      to: formattedPhone,
      type: 'text',
      text: { body: message },
    }),
  });

  if (!response.ok) {
    const errorData = await response.text();
    console.error(`[WHATSAPP_API_ERROR] ${response.status}: ${errorData}`);
    console.log(`[WHATSAPP_FALLBACK_LINK] ${waMeLink}`);
    throw new Error(`WhatsApp API error: ${response.status} — ${errorData}`);
  }

  return response.json();
}

async function sendWhatsAppOtp(phone, otp) {
  const apiKey = process.env.WHATSAPP_API_KEY;
  const phoneNumberId = process.env.WHATSAPP_PHONE_NUMBER_ID;
  const formattedPhone = formatPhoneNumber(phone);

  const message = `🔒 ZAMZY Verification Code

Hi, your 6-digit WhatsApp verification OTP for ZAMZY Digital Products checkout is:

*${otp}*

Do not share this code with anyone. Valid for 10 minutes.`;

  const waMeLink = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}`;

  if (!apiKey || !phoneNumberId) {
    console.log('\n[WHATSAPP_OTP_NOTIFICATION_LOG]');
    console.log(`OTP To: +${formattedPhone} | Code: ${otp}`);
    console.log(`Direct WA Link: ${waMeLink}\n`);
    return {
      status: 'LOGGED',
      provider: 'console',
      phone: formattedPhone,
      otp,
      waMeLink,
    };
  }

  // Meta WhatsApp Business API
  const response = await fetch(`https://graph.facebook.com/v18.0/${phoneNumberId}/messages`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${apiKey}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      messaging_product: 'whatsapp',
      to: formattedPhone,
      type: 'text',
      text: { body: message },
    }),
  });

  if (!response.ok) {
    const errorData = await response.text();
    console.error(`[WHATSAPP_OTP_API_ERROR] ${response.status}: ${errorData}`);
    console.log(`[WHATSAPP_OTP_FALLBACK_LINK] ${waMeLink}`);
    return { status: 'LOGGED', phone: formattedPhone, otp, waMeLink };
  }

  return response.json();
}

module.exports = { sendOrderWhatsApp, sendWhatsAppOtp, formatPhoneNumber };

