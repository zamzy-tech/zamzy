/**
 * Email Service — Provider adapter for transactional email
 * Supports SMTP (Nodemailer) and console logging fallback
 */
const nodemailer = require('nodemailer');

async function sendOrderEmail(customer, order, accessUrl) {
  const provider = process.env.EMAIL_PROVIDER || 'smtp';
  const smtpHost = process.env.SMTP_HOST;
  const smtpUser = process.env.SMTP_USER;
  const smtpPass = process.env.SMTP_PASS;

  const html = `
  <div style="font-family: 'Helvetica Neue', Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
    <div style="background: #111111; padding: 24px 32px; text-align: center;">
      <h1 style="color: #ffffff; font-size: 24px; margin: 0; letter-spacing: 2px;">ZAMZY</h1>
      <p style="color: #C8B6FF; font-size: 11px; margin: 4px 0 0; letter-spacing: 1.5px;">DIGITAL PRODUCTS</p>
    </div>
    <div style="padding: 32px;">
      <p style="color: #111; font-size: 16px; margin-bottom: 8px;">Hi ${customer.name},</p>
      <p style="color: #333; font-size: 15px; line-height: 1.6;">Thank you for your purchase from ZAMZY.</p>
      <p style="color: #333; font-size: 15px; line-height: 1.6;">Your order has been successfully confirmed.</p>
      <div style="background: #f8f7ff; border: 1px solid #e8e4ff; border-radius: 8px; padding: 16px; margin: 20px 0;">
        <p style="margin: 0; color: #666; font-size: 13px;">Order Number</p>
        <p style="margin: 4px 0 0; color: #111; font-size: 18px; font-weight: 600;">${order.order_number}</p>
        <p style="margin: 8px 0 0; color: #666; font-size: 13px;">Total Amount Paid</p>
        <p style="margin: 2px 0 0; color: #111; font-size: 16px; font-weight: 600;">₹${(order.total / 100).toFixed(0)}</p>
      </div>
      <p style="color: #333; font-size: 15px; line-height: 1.6;">Your purchased digital products are ready for download.</p>
      <div style="text-align: center; margin: 28px 0;">
        <a href="${accessUrl}" style="display: inline-block; background: #C8B6FF; color: #111; text-decoration: none; padding: 14px 36px; border-radius: 8px; font-weight: 600; font-size: 15px; letter-spacing: 0.5px;">ACCESS YOUR PURCHASE</a>
      </div>
      <p style="color: #666; font-size: 14px; line-height: 1.6;">You can use the access page to download all products included in your order.</p>
      <hr style="border: none; border-top: 1px solid #eee; margin: 24px 0;">
      <p style="color: #666; font-size: 13px;">Need help?</p>
      <p style="color: #111; font-size: 14px; margin: 4px 0;">Email: work@zamzy.in</p>
      <p style="color: #111; font-size: 14px; margin: 4px 0;">Phone: +91 7287060553</p>
    </div>
    <div style="background: #fafafa; padding: 20px 32px; text-align: center; border-top: 1px solid #eee;">
      <p style="color: #111; font-size: 14px; font-weight: 600; margin: 0;">ZAMZY</p>
      <p style="color: #C8B6FF; font-size: 11px; margin: 2px 0 0; letter-spacing: 1px;">IDEAS THAT MOVE BUSINESS.</p>
    </div>
  </div>`;

  if (smtpHost && smtpUser && smtpPass) {
    const transporter = nodemailer.createTransport({
      host: smtpHost,
      port: parseInt(process.env.SMTP_PORT) || 587,
      secure: parseInt(process.env.SMTP_PORT) === 465,
      auth: {
        user: smtpUser,
        pass: smtpPass,
      },
    });

    return await transporter.sendMail({
      from: `"ZAMZY" <${process.env.EMAIL_FROM || 'work@zamzy.in'}>`,
      to: customer.email,
      subject: `Your ZAMZY Order Confirmation & Access (#${order.order_number})`,
      html,
    });
  }

  // Fallback: Log email details when SMTP credentials not provided
  console.log('\n[EMAIL_NOTIFICATION_LOG]');
  console.log(`To: ${customer.name} <${customer.email}>`);
  console.log(`Subject: Your ZAMZY Order Confirmation & Access (#${order.order_number})`);
  console.log(`Access Link: ${accessUrl}\n`);
  return { status: 'LOGGED', recipient: customer.email, accessUrl };
}

module.exports = { sendOrderEmail };

