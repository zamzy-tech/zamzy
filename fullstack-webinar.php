<?php
require_once __DIR__ . '/db.php';
$webinarPrice = intval(getSetting('webinar_price', '96'));
if ($webinarPrice <= 0) $webinarPrice = 96;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Full Stack Web Development Webinar — From Basics to Real-World Applications | ZAMZY</title>
  <meta name="description" content="Join ZAMZY's Live Online Webinar on Full Stack Web Development for just ₹96. Learn HTML, CSS, JS, PHP, MySQL, Git, Hosting, cPanel, APIs & AI Stack with practical live demo and certificate." />
  <meta name="keywords" content="Full Stack Web Development Webinar, Learn Full Stack ₹96, ZAMZY Webinar, Web Development Course Chennai, Online Coding Workshop, PHP MySQL Git cPanel, Student Web Dev Webinar" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://zamzy.in/fullstack-webinar" />

  <!-- Meta Pixel Code -->
  <script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '1063958663209939');
  fbq('track', 'PageView');
  </script>
  <noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=1063958663209939&ev=PageView&noscript=1"
  /></noscript>
  <!-- End Meta Pixel Code -->

  <!-- Open Graph / WhatsApp / Social Cards -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://zamzy.in/fullstack-webinar" />
  <meta property="og:title" content="Full Stack Web Development Live Webinar — Just ₹96 | ZAMZY" />
  <meta property="og:description" content="Learn · Practice · Deploy · Grow. From basics to real-world applications covering HTML, CSS, JS, PHP, MySQL, Git, APIs, cPanel & AI Stack. Certificate included!" />
  <meta property="og:image" content="https://zamzy.in/images/webinar-fullstack.jpg" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Full Stack Web Development Live Webinar — Just ₹96 | ZAMZY" />
  <meta name="twitter:description" content="From Basics to Real-World Applications. Live demonstration, 14 technologies, and Certificate of Participation for ₹96." />
  <meta name="twitter:image" content="https://zamzy.in/images/webinar-fullstack.jpg" />

  <!-- Fonts & Core Stylesheet -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&family=Barlow+Condensed:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="tooplate-vora-bold-style.css" />

  <style>
    :root {
      --brand-purple: #9d4edd;
      --brand-neon-purple: #c77dff;
      --brand-cyan: #00ffcc;
      --brand-gold: #ffbe0b;
      --brand-green: #25D366;
    }

    /* ═══════════════════════════════════════════════
       WEBINAR HERO SECTION
    ═══════════════════════════════════════════════ */
    .webinar-hero {
      padding: 130px 5vw 70px 5vw;
      position: relative;
      z-index: 2;
      max-width: 1360px;
      margin: 0 auto;
    }

    .webinar-hero__grid {
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 3.5rem;
      align-items: center;
    }

    @media (max-width: 992px) {
      .webinar-hero__grid {
        grid-template-columns: 1fr;
        gap: 2.5rem;
      }
    }

    .live-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
      background: rgba(255, 59, 48, 0.12);
      border: 1px solid rgba(255, 59, 48, 0.4);
      color: #ff5e57;
      padding: 0.4rem 1rem;
      border-radius: 999px;
      font-family: var(--mono);
      font-size: 0.76rem;
      font-weight: 700;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      margin-bottom: 1.2rem;
      box-shadow: 0 0 20px rgba(255, 59, 48, 0.2);
    }

    .live-pulse {
      width: 9px;
      height: 9px;
      background: #ff3b30;
      border-radius: 50%;
      box-shadow: 0 0 10px #ff3b30;
      animation: livePulseAnim 1.4s infinite ease-in-out;
    }

    @keyframes livePulseAnim {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.35; transform: scale(0.75); }
    }

    .webinar-hero__title {
      font-family: var(--display);
      font-size: clamp(2.4rem, 5.5vw, 4.4rem);
      font-weight: 800;
      line-height: 1.06;
      color: var(--white);
      text-transform: uppercase;
      letter-spacing: -0.02em;
      margin-bottom: 0.8rem;
    }

    .webinar-hero__title span.gradient-text {
      background: linear-gradient(135deg, #c77dff 0%, #7b2cbf 40%, #00ffcc 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      display: block;
    }

    .webinar-hero__subtitle {
      font-family: var(--mono);
      font-size: clamp(1rem, 1.8vw, 1.35rem);
      color: #e2e8f0;
      font-weight: 600;
      margin-bottom: 1.2rem;
    }

    .webinar-hero__pillars {
      display: flex;
      gap: 0.9rem;
      flex-wrap: wrap;
      margin-bottom: 1.6rem;
    }

    .pillar-tag {
      font-family: var(--mono);
      font-size: 0.75rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--cyan);
      background: rgba(0, 255, 204, 0.08);
      border: 1px solid rgba(0, 255, 204, 0.25);
      padding: 0.35rem 0.85rem;
      border-radius: 4px;
      font-weight: 600;
    }

    .webinar-hero__quote {
      font-family: var(--mono);
      font-size: 0.88rem;
      color: var(--dim);
      line-height: 1.7;
      margin-bottom: 2rem;
      border-left: 2px solid var(--brand-purple);
      padding-left: 1rem;
    }

    .webinar-hero__quote strong {
      color: #ffffff;
    }

    .price-strip-inline {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      background: rgba(157, 78, 221, 0.12);
      border: 1px solid rgba(199, 125, 255, 0.3);
      padding: 1rem 1.4rem;
      border-radius: 12px;
      margin-bottom: 2.2rem;
      backdrop-filter: blur(10px);
      box-shadow: 0 10px 35px rgba(123, 44, 191, 0.2);
    }

    .price-strip-inline__price {
      display: flex;
      align-items: baseline;
      gap: 0.4rem;
    }

    .price-strip-inline__label {
      font-family: var(--mono);
      font-size: 0.75rem;
      color: var(--dim);
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    .price-strip-inline__amount {
      font-family: var(--display);
      font-size: 2.5rem;
      font-weight: 800;
      color: #ffffff;
      line-height: 1;
    }

    .price-strip-inline__strikethrough {
      font-family: var(--mono);
      font-size: 0.95rem;
      color: #64748b;
      text-decoration: line-through;
    }

    .price-strip-inline__highlight {
      font-family: var(--mono);
      font-size: 0.8rem;
      color: var(--cyan);
      line-height: 1.4;
      border-left: 1px solid rgba(255, 255, 255, 0.15);
      padding-left: 1.2rem;
    }

    .webinar-hero__actions {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      align-items: center;
    }

    /* Poster Mockup Card */
    .poster-showcase {
      position: relative;
      border-radius: 18px;
      overflow: hidden;
      border: 1px solid rgba(199, 125, 255, 0.35);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.7), 0 0 40px rgba(157, 78, 221, 0.25);
      background: #0d0c1d;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }

    .poster-showcase:hover {
      transform: translateY(-4px);
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.85), 0 0 50px rgba(157, 78, 221, 0.4);
    }

    .poster-showcase__img {
      width: 100%;
      height: auto;
      display: block;
      object-fit: cover;
    }

    .poster-showcase__floating-badge {
      position: absolute;
      bottom: 16px;
      left: 16px;
      right: 16px;
      background: rgba(8, 8, 17, 0.88);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 10px;
      padding: 0.8rem 1.1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
    }

    /* ═══════════════════════════════════════════════
       KEY BENEFITS / 4 HIGHLIGHTS BAR
    ═══════════════════════════════════════════════ */
    .webinar-highlights {
      padding: 0 5vw 70px 5vw;
      max-width: 1360px;
      margin: 0 auto;
      position: relative;
      z-index: 2;
    }

    .highlights-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.25rem;
    }

    .highlight-card {
      background: rgba(255, 255, 255, 0.025);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 1.6rem 1.4rem;
      display: flex;
      align-items: flex-start;
      gap: 1.1rem;
      transition: all 0.3s ease;
    }

    .highlight-card:hover {
      background: rgba(255, 255, 255, 0.045);
      border-color: rgba(0, 255, 204, 0.3);
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .highlight-card__icon {
      font-size: 2rem;
      line-height: 1;
      flex-shrink: 0;
    }

    .highlight-card__title {
      font-family: var(--display);
      font-size: 1.15rem;
      color: #ffffff;
      font-weight: 700;
      margin-bottom: 0.35rem;
    }

    .highlight-card__desc {
      font-family: var(--mono);
      font-size: 0.76rem;
      color: var(--dim);
      line-height: 1.6;
    }

    /* ═══════════════════════════════════════════════
       THE 14 TECHNOLOGIES GRID (FROM POSTER)
    ═══════════════════════════════════════════════ */
    .tech-stack-section {
      padding: 80px 5vw;
      background: linear-gradient(180deg, rgba(8,8,17,0) 0%, rgba(15,14,35,0.6) 50%, rgba(8,8,17,0) 100%);
      position: relative;
      z-index: 2;
    }

    .tech-stack-container {
      max-width: 1360px;
      margin: 0 auto;
    }

    .section-header-centered {
      text-align: center;
      max-width: 780px;
      margin: 0 auto 3.5rem auto;
    }

    .tech-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
      gap: 1.1rem;
    }

    @media (max-width: 600px) {
      .tech-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
      }
    }

    .tech-card {
      background: rgba(18, 18, 38, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 1.4rem 1rem;
      text-align: center;
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
      overflow: hidden;
    }

    .tech-card:hover {
      transform: translateY(-5px);
      border-color: rgba(199, 125, 255, 0.4);
      background: rgba(30, 26, 60, 0.85);
      box-shadow: 0 12px 30px rgba(123, 44, 191, 0.25);
    }

    .tech-badge-icon {
      width: 48px;
      height: 48px;
      margin: 0 auto 0.9rem auto;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      font-family: var(--display);
      font-weight: 800;
      font-size: 1.3rem;
      color: #ffffff;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4);
    }

    .tech-badge-icon.html { background: #e44d26; }
    .tech-badge-icon.css { background: #264de4; }
    .tech-badge-icon.js { background: #f7df1e; color: #000; font-size: 1.15rem; }
    .tech-badge-icon.bootstrap { background: #7952b3; }
    .tech-badge-icon.php { background: #777bb4; font-size: 1rem; }
    .tech-badge-icon.mysql { background: #00758f; }
    .tech-badge-icon.apis { background: linear-gradient(135deg, #0ea5e9, #2563eb); font-size: 1.4rem; }
    .tech-badge-icon.gateway { background: linear-gradient(135deg, #10b981, #059669); font-size: 1.4rem; }
    .tech-badge-icon.git { background: #f05032; }
    .tech-badge-icon.github { background: #24292e; border: 1px solid rgba(255,255,255,0.2); }
    .tech-badge-icon.dns { background: linear-gradient(135deg, #8b5cf6, #6366f1); }
    .tech-badge-icon.cpanel { background: #ff6c2c; }
    .tech-badge-icon.vercel { background: #000000; border: 1px solid rgba(255,255,255,0.25); }
    .tech-badge-icon.aistack { background: linear-gradient(135deg, #ec4899, #8b5cf6); }

    .tech-card__name {
      font-family: var(--display);
      font-size: 1.1rem;
      color: #ffffff;
      font-weight: 700;
      margin-bottom: 0.25rem;
    }

    .tech-card__role {
      font-family: var(--mono);
      font-size: 0.72rem;
      color: var(--cyan);
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    /* ═══════════════════════════════════════════════
       REGISTRATION FORM & CHECKOUT
    ═══════════════════════════════════════════════ */
    .register-section {
      padding: 80px 5vw 100px 5vw;
      position: relative;
      z-index: 2;
      max-width: 1100px;
      margin: 0 auto;
    }

    .register-card-shell {
      background: rgba(14, 13, 28, 0.9);
      border: 1px solid rgba(199, 125, 255, 0.35);
      border-radius: 20px;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.8), 0 0 50px rgba(123, 44, 191, 0.2);
      backdrop-filter: blur(20px);
      overflow: hidden;
      display: grid;
      grid-template-columns: 1fr 1.08fr;
    }

    @media (max-width: 900px) {
      .register-card-shell {
        grid-template-columns: 1fr;
      }
    }

    .register-info-panel {
      padding: 3.5rem 2.8rem;
      background: linear-gradient(145deg, rgba(30, 20, 60, 0.7) 0%, rgba(15, 12, 35, 0.95) 100%);
      border-right: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    @media (max-width: 900px) {
      .register-info-panel {
        border-right: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 2.5rem 2rem;
      }
    }

    .register-form-panel {
      padding: 3.5rem 3rem;
    }

    @media (max-width: 900px) {
      .register-form-panel {
        padding: 2.5rem 2rem;
      }
    }

    .price-callout-box {
      background: rgba(0, 0, 0, 0.35);
      border: 1px solid rgba(0, 255, 204, 0.3);
      border-radius: 12px;
      padding: 1.4rem;
      margin: 1.8rem 0;
    }

    .price-callout-box__header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.6rem;
    }

    .price-callout-box__amount {
      font-family: var(--display);
      font-size: 2.6rem;
      font-weight: 800;
      color: var(--cyan);
      line-height: 1;
    }

    .upi-badge-row {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      flex-wrap: wrap;
      margin-top: 1rem;
    }

    .upi-badge {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 6px;
      padding: 0.3rem 0.65rem;
      font-family: var(--mono);
      font-size: 0.72rem;
      color: #cbd5e1;
    }

    /* Form Fields */
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.2rem;
      margin-bottom: 1.2rem;
    }

    @media (max-width: 640px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }

    .form-field {
      margin-bottom: 1.2rem;
    }

    .field-label {
      display: block;
      font-family: var(--mono);
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #cbd5e1;
      margin-bottom: 0.45rem;
    }

    .field-label span.req {
      color: #ff5e57;
    }

    .field-input, .field-select {
      width: 100%;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #ffffff;
      border-radius: 8px;
      padding: 0.85rem 1rem;
      font-family: var(--mono);
      font-size: 0.88rem;
      box-sizing: border-box;
      transition: all 0.25s ease;
      outline: none;
    }

    .field-input:focus, .field-select:focus {
      border-color: var(--brand-neon-purple);
      background: rgba(255, 255, 255, 0.07);
      box-shadow: 0 0 16px rgba(199, 125, 255, 0.25);
    }

    .field-select {
      cursor: pointer;
    }

    .field-select option {
      background: #0d0c1d;
      color: #ffffff;
    }

    .btn-register-submit {
      width: 100%;
      background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
      color: #ffffff;
      border: none;
      border-radius: 10px;
      padding: 1.1rem 1.6rem;
      font-family: var(--display);
      font-size: 1.25rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      cursor: pointer;
      box-shadow: 0 10px 30px rgba(168, 85, 247, 0.45);
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.7rem;
      margin-top: 1.2rem;
    }

    .btn-register-submit:hover {
      transform: translateY(-3px) scale(1.01);
      box-shadow: 0 15px 40px rgba(168, 85, 247, 0.6);
      background: linear-gradient(135deg, #c084fc 0%, #818cf8 100%);
    }

    .btn-register-submit:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    /* Success Screen Modal / Inline Alert */
    .registration-success-box {
      display: none;
      background: rgba(37, 211, 102, 0.08);
      border: 1px solid rgba(37, 211, 102, 0.4);
      border-radius: 16px;
      padding: 2.2rem;
      text-align: center;
      box-shadow: 0 15px 40px rgba(37, 211, 102, 0.2);
      animation: fadeInSuccess 0.5s ease forwards;
    }

    @keyframes fadeInSuccess {
      from { opacity: 0; transform: translateY(12px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .reg-code-badge {
      font-family: var(--display);
      font-size: 1.6rem;
      color: var(--brand-green);
      letter-spacing: 0.1em;
      background: rgba(0, 0, 0, 0.4);
      padding: 0.5rem 1.2rem;
      border-radius: 8px;
      display: inline-block;
      margin: 1rem 0;
      border: 1px dashed rgba(37, 211, 102, 0.5);
    }

    .btn-whatsapp-confirm {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.8rem;
      background: #25D366;
      color: #ffffff;
      text-decoration: none;
      padding: 0.95rem 1.8rem;
      border-radius: 10px;
      font-family: var(--display);
      font-size: 1.15rem;
      font-weight: 700;
      margin-top: 1rem;
      box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
      transition: all 0.25s ease;
    }

    .btn-whatsapp-confirm:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 35px rgba(37, 211, 102, 0.6);
      background: #22bf5b;
    }

    /* Payment Checkout Card */
    .payment-checkout-card {
      background: rgba(14, 17, 30, 0.85);
      border: 1px solid rgba(0, 255, 204, 0.35);
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.7), 0 0 30px rgba(0, 255, 204, 0.15);
      border-radius: 18px;
      padding: 2rem;
      animation: fadeInSuccess 0.4s ease forwards;
    }

    .checkout-badge {
      display: inline-block;
      font-family: var(--mono);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      color: var(--cyan);
      background: rgba(0, 255, 204, 0.1);
      border: 1px solid rgba(0, 255, 204, 0.3);
      padding: 4px 10px;
      border-radius: 999px;
      text-transform: uppercase;
    }

    /* ═══════════════════════════════════════════════
       PAYMENT CONFIRMED FULLSCREEN POPUP MODAL
    ═══════════════════════════════════════════════ */
    .webinar-success-modal {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 999999;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    .webinar-success-modal.active {
      display: flex;
    }

    .webinar-success-modal__backdrop {
      position: absolute;
      inset: 0;
      background: rgba(5, 5, 12, 0.88);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
    }

    .webinar-success-modal__card {
      position: relative;
      background: linear-gradient(135deg, #0d0e1c 0%, #131428 100%);
      border: 1px solid rgba(0, 255, 204, 0.4);
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.9), 0 0 50px rgba(0, 255, 204, 0.25);
      border-radius: 20px;
      padding: 2.5rem 2.2rem;
      max-width: 540px;
      width: 100%;
      text-align: center;
      animation: modalPopIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
      z-index: 10;
    }

    @keyframes modalPopIn {
      from { opacity: 0; transform: scale(0.9) translateY(20px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .webinar-success-modal__close {
      position: absolute;
      top: 16px;
      right: 18px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #94a3b8;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      line-height: 1;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .webinar-success-modal__close:hover {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
    }

    .modal-celebrate-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(0, 255, 204, 0.12);
      border: 1px solid rgba(0, 255, 204, 0.4);
      color: #00ffcc;
      font-family: var(--mono);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      padding: 0.35rem 0.9rem;
      border-radius: 999px;
      margin-bottom: 1.2rem;
    }

    .modal-celebrate-badge .pulse-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #00ffcc;
      box-shadow: 0 0 10px #00ffcc;
      animation: pulseGlow 1.5s infinite;
    }

    @keyframes pulseGlow {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.4); opacity: 0.5; }
    }

    .modal-icon-wrap {
      font-size: 3.5rem;
      margin-bottom: 0.8rem;
      animation: bounceCelebration 1s ease infinite alternate;
    }

    @keyframes bounceCelebration {
      from { transform: translateY(0); }
      to { transform: translateY(-8px); }
    }

    .modal-title {
      font-family: var(--display);
      font-size: 1.85rem;
      font-weight: 800;
      color: #ffffff;
      line-height: 1.2;
      margin-bottom: 0.6rem;
    }

    .modal-reg-pill {
      font-family: var(--mono);
      font-size: 0.82rem;
      color: #94a3b8;
      background: rgba(0, 0, 0, 0.45);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 8px;
      padding: 0.45rem 1rem;
      display: inline-block;
      margin-bottom: 1.4rem;
    }

    .modal-reg-pill strong {
      color: #00ffcc;
      letter-spacing: 0.08em;
    }

    .modal-delivery-card {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.09);
      border-radius: 12px;
      padding: 1.2rem 1.4rem;
      text-align: left;
      margin-bottom: 1.5rem;
    }

    .delivery-card-header {
      display: flex;
      align-items: flex-start;
      gap: 0.85rem;
      margin-bottom: 0.85rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding-bottom: 0.85rem;
    }

    .delivery-icon {
      font-size: 1.6rem;
      line-height: 1;
    }

    .delivery-title {
      font-family: var(--display);
      font-size: 1.05rem;
      font-weight: 700;
      color: #ffffff;
    }

    .delivery-sub {
      font-size: 0.78rem;
      color: #cbd5e1;
      margin-top: 0.2rem;
      line-height: 1.5;
    }

    .delivery-items-list {
      display: flex;
      flex-direction: column;
      gap: 0.55rem;
    }

    .delivery-item {
      display: flex;
      align-items: flex-start;
      gap: 0.6rem;
      font-size: 0.75rem;
      color: #94a3b8;
      line-height: 1.45;
    }

    .delivery-item strong {
      color: #e2e8f0;
    }

    .item-check {
      color: #00ffcc;
      font-weight: 800;
    }

    .modal-wa-cta-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.8rem;
      background: #25D366;
      color: #06230f;
      text-decoration: none;
      padding: 1rem 1.8rem;
      border-radius: 12px;
      font-family: var(--display);
      font-size: 1.15rem;
      font-weight: 800;
      letter-spacing: 0.04em;
      box-shadow: 0 8px 30px rgba(37, 211, 102, 0.45);
      transition: all 0.25s ease;
      width: 100%;
      box-sizing: border-box;
    }

    .modal-wa-cta-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 40px rgba(37, 211, 102, 0.65);
      background: #22bf5b;
      color: #000;
    }

    .modal-done-btn {
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #94a3b8;
      padding: 0.65rem 1.4rem;
      border-radius: 8px;
      font-family: var(--mono);
      font-size: 0.78rem;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .modal-done-btn:hover {
      background: rgba(255, 255, 255, 0.06);
      color: #ffffff;
      border-color: rgba(255, 255, 255, 0.3);
    }

    /* ═══════════════════════════════════════════════
       HELP & CONTACT STRIP
    ═══════════════════════════════════════════════ */
    .contact-strip {
      padding: 40px 5vw 80px 5vw;
      max-width: 1100px;
      margin: 0 auto;
      text-align: center;
      position: relative;
      z-index: 2;
    }

    .contact-strip__box {
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 14px;
      padding: 1.8rem 2rem;
      display: flex;
      justify-content: space-around;
      align-items: center;
      flex-wrap: wrap;
      gap: 1.5rem;
    }

    .contact-strip__item {
      display: flex;
      align-items: center;
      gap: 0.9rem;
      text-align: left;
    }

    .contact-strip__icon {
      width: 44px;
      height: 44px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
    }
  </style>
</head>
<body class="visuals-on">

  <!-- Ambient Nebula Aura -->
  <div class="aura"></div>

  <!-- ═══════════════════════════════════════════════
       NAVIGATION BAR
  ═══════════════════════════════════════════════ -->
  <nav class="nav">
    <a href="index.html#hero" class="brand-logo-wrap">
      <img src="images/logo.png" alt="ZAMZY" class="brand-logo-img" />
    </a>
    <ul class="nav-links">
      <li><a href="index.html#about">Studio</a></li>
      <li><a href="index.html#launchpad">Launchpad</a></li>
      <li><a href="index.html#products">Products</a></li>
      <li><a href="index.html#services">Services</a></li>
      <li class="nav-dropdown">
        <a href="fullstack-webinar" class="active" style="color:var(--brand-neon-purple); font-weight:700;">
          Events <span style="font-size:8px; opacity:0.8;">▼</span>
        </a>
        <ul class="nav-dropdown-menu">
          <li>
            <a href="fullstack-webinar" class="nav-dropdown-item">
              <span class="nav-dropdown-item-title">🚀 Full Stack Live Webinar <span style="font-size:9px; background:#a855f7; color:#fff; padding:2px 6px; border-radius:10px; font-weight:800;">LIVE</span></span>
              <span class="nav-dropdown-item-desc">From Scratch to Cloud Deployment · ₹<?= $webinarPrice ?></span>
            </a>
          </li>
        </ul>
      </li>
      <li><a href="careers">Careers</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>
    <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <!-- Mobile Drawer Menu -->
  <div class="mobile-menu">
    <a href="index.html#hero">Hero</a>
    <a href="index.html#about">Studio</a>
    <a href="index.html#launchpad">Launchpad</a>
    <a href="index.html#products">Products</a>
    <a href="index.html#services">Services</a>
    <a href="fullstack-webinar" style="color:var(--brand-neon-purple); font-weight:700;">🎟️ Events — Full Stack Webinar (₹<?= $webinarPrice ?>)</a>
    <a href="careers">Careers &amp; Guild</a>
    <a href="contact.php">Contact Us</a>
  </div>

  <!-- ═══════════════════════════════════════════════
       HERO SECTION
  ═══════════════════════════════════════════════ -->
  <section class="webinar-hero">
    <div class="webinar-hero__grid">
      
      <!-- Left Column: Copy & Value Proposition -->
      <div>
        <div class="live-pill">
          <span class="live-pulse"></span>
          <span>Live Online Webinar · Limited Seats</span>
        </div>

        <h1 class="webinar-hero__title">
          Full Stack
          <span class="gradient-text">Web Development</span>
        </h1>

        <div class="webinar-hero__subtitle">
          From Basics to Real-World Applications
        </div>

        <div class="webinar-hero__pillars">
          <span class="pillar-tag">Learn</span>
          <span class="pillar-tag">Practice</span>
          <span class="pillar-tag">Deploy</span>
          <span class="pillar-tag">Grow</span>
        </div>

        <p class="webinar-hero__quote">
          <strong>"Real Skills. Real Opportunities. · Better Developers Brighter Future."</strong><br />
          Experience a complete end-to-end practical walkthrough of professional web engineering. We guide you step-by-step through front-end architectures, robust back-ends, database schema design, and production cloud deployment on live domains.
        </p>

        <!-- Unbeatable ₹96 Pricing Strip -->
        <div class="price-strip-inline">
          <div class="price-strip-inline__price">
            <div>
              <div class="price-strip-inline__label">Just</div>
              <div class="price-strip-inline__amount">₹<?= $webinarPrice ?></div>
            </div>
            <span class="price-strip-inline__strikethrough">₹1,999</span>
          </div>
          <div class="price-strip-inline__highlight">
            <strong>High Value. Real Skills.</strong><br />
            Unbeatable Price. Complete with practical demonstration &amp; verified certificate.
          </div>
        </div>

        <div class="webinar-hero__actions">
          <a href="#register" class="btn btn-primary" style="background:linear-gradient(135deg,#a855f7 0%,#6366f1 100%); border-color:transparent; padding:0.95rem 2rem; font-size:1.05rem;">
            Register Now (₹<?= $webinarPrice ?>) →
          </a>
          <a href="#tech-stack" class="btn btn-outline" style="border-color:rgba(255,255,255,0.25);">
            Explore Curriculum
          </a>
          <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20have%20a%20question%20regarding%20the%20Full%20Stack%20Web%20Development%20Webinar%20(Rs.%2096)." target="_blank" rel="noopener" class="btn btn-outline" style="border-color:rgba(37,211,102,0.4); color:#25D366;">
            💬 WhatsApp Us
          </a>
        </div>
      </div>

      <!-- Right Column: Official Poster Showcase -->
      <div>
        <div class="poster-showcase">
          <img src="images/webinar-fullstack.jpg" alt="ZAMZY Full Stack Web Development Live Online Webinar" class="poster-showcase__img" />
          <div class="poster-showcase__floating-badge">
            <div>
              <div style="font-family:var(--display); font-size:1.1rem; color:#ffffff; font-weight:700;">Live Interactive Session</div>
              <div style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan);">Certificate of Participation Included</div>
            </div>
            <a href="#register" class="btn btn-sm" style="background:var(--cyan); color:#080811; font-weight:800;">
              ₹<?= $webinarPrice ?> · Join
            </a>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════
       4 KEY BENEFIT HIGHLIGHTS (FROM POSTER)
  ═══════════════════════════════════════════════ -->
  <section class="webinar-highlights">
    <div class="highlights-grid">
      
      <div class="highlight-card">
        <div class="highlight-card__icon">🖥️</div>
        <div>
          <h3 class="highlight-card__title">Live Online Session</h3>
          <p class="highlight-card__desc">Interactive real-time masterclass. Ask questions live and clear technical concepts on the spot.</p>
        </div>
      </div>

      <div class="highlight-card">
        <div class="highlight-card__icon">📄</div>
        <div>
          <h3 class="highlight-card__title">Practical Demonstration</h3>
          <p class="highlight-card__desc">Zero fluff or passive slides. Pure hands-on code built live from blank file to live deployment.</p>
        </div>
      </div>

      <div class="highlight-card">
        <div class="highlight-card__icon">👥</div>
        <div>
          <h3 class="highlight-card__title">Open for Everyone</h3>
          <p class="highlight-card__desc">Beginner friendly! Tailored for college students, aspiring developers, and career switchers.</p>
        </div>
      </div>

      <div class="highlight-card">
        <div class="highlight-card__icon">🎓</div>
        <div>
          <h3 class="highlight-card__title">Certificate Included</h3>
          <p class="highlight-card__desc">Receive an official verifiable Certificate of Participation from ZAMZY to boost your resume &amp; LinkedIn.</p>
        </div>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════
       THE 14 TECHNOLOGIES YOU WILL MASTER
  ═══════════════════════════════════════════════ -->
  <section class="tech-stack-section" id="tech-stack">
    <div class="tech-stack-container">
      
      <div class="section-header-centered">
        <span class="badge">Comprehensive Roadmap</span>
        <h2 class="section-title" style="margin-top:0.6rem;">14 Core Technologies &amp; Tools Covered</h2>
        <p style="font-family:var(--mono); font-size:0.9rem; color:var(--dim); margin-top:0.8rem; line-height:1.7;">
          Everything needed to build production-grade web platforms from structure to global cloud deployment.
        </p>
      </div>

      <div class="tech-grid">
        
        <!-- 1. HTML -->
        <div class="tech-card">
          <div class="tech-badge-icon html">5</div>
          <div class="tech-card__name">HTML</div>
          <div class="tech-card__role">Structure</div>
        </div>

        <!-- 2. CSS -->
        <div class="tech-card">
          <div class="tech-badge-icon css">3</div>
          <div class="tech-card__name">CSS</div>
          <div class="tech-card__role">Design</div>
        </div>

        <!-- 3. JavaScript -->
        <div class="tech-card">
          <div class="tech-badge-icon js">JS</div>
          <div class="tech-card__name">JavaScript</div>
          <div class="tech-card__role">Interact</div>
        </div>

        <!-- 4. Bootstrap -->
        <div class="tech-card">
          <div class="tech-badge-icon bootstrap">B</div>
          <div class="tech-card__name">Bootstrap</div>
          <div class="tech-card__role">Responsive UI</div>
        </div>

        <!-- 5. PHP -->
        <div class="tech-card">
          <div class="tech-badge-icon php">php</div>
          <div class="tech-card__name">PHP</div>
          <div class="tech-card__role">Backend</div>
        </div>

        <!-- 6. MySQL -->
        <div class="tech-card">
          <div class="tech-badge-icon mysql">🐬</div>
          <div class="tech-card__name">MySQL</div>
          <div class="tech-card__role">Database</div>
        </div>

        <!-- 7. APIs -->
        <div class="tech-card">
          <div class="tech-badge-icon apis">⚙️</div>
          <div class="tech-card__name">APIs</div>
          <div class="tech-card__role">Integrate</div>
        </div>

        <!-- 8. API Gateway -->
        <div class="tech-card">
          <div class="tech-badge-icon gateway">🔗</div>
          <div class="tech-card__name">API Gateway</div>
          <div class="tech-card__role">Connect</div>
        </div>

        <!-- 9. Git -->
        <div class="tech-card">
          <div class="tech-badge-icon git">🌿</div>
          <div class="tech-card__name">Git</div>
          <div class="tech-card__role">Version Control</div>
        </div>

        <!-- 10. GitHub -->
        <div class="tech-card">
          <div class="tech-badge-icon github">🐙</div>
          <div class="tech-card__name">GitHub</div>
          <div class="tech-card__role">Collaborate</div>
        </div>

        <!-- 11. Domain & DNS -->
        <div class="tech-card">
          <div class="tech-badge-icon dns">🌐</div>
          <div class="tech-card__name">Domain &amp; DNS</div>
          <div class="tech-card__role">Go Live</div>
        </div>

        <!-- 12. Hosting & cPanel -->
        <div class="tech-card">
          <div class="tech-badge-icon cpanel">🗄️</div>
          <div class="tech-card__name">Hosting &amp; cPanel</div>
          <div class="tech-card__role">Manage</div>
        </div>

        <!-- 13. Vercel -->
        <div class="tech-card">
          <div class="tech-badge-icon vercel">▲</div>
          <div class="tech-card__name">Vercel</div>
          <div class="tech-card__role">Deploy</div>
        </div>

        <!-- 14. AI Stack -->
        <div class="tech-card">
          <div class="tech-badge-icon aistack">🤖</div>
          <div class="tech-card__name">AI Stack</div>
          <div class="tech-card__role">Build Faster</div>
        </div>

      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════
       REGISTRATION FORM & CHECKOUT
  ═══════════════════════════════════════════════ -->
  <section class="register-section" id="register">
    
    <div class="register-card-shell">
      
      <!-- Left Panel: Summary & UPI Info -->
      <div class="register-info-panel">
        <div>
          <span class="badge">Reserve Your Seat</span>
          <h2 style="font-family:var(--display); font-size:2rem; font-weight:800; color:#ffffff; margin:0.8rem 0 0.5rem 0;">
            Webinar Enrollment
          </h2>
          <p style="font-family:var(--mono); font-size:0.84rem; color:var(--dim); line-height:1.7;">
            Register in under 60 seconds. You will receive immediate WhatsApp &amp; Email confirmation with access links and study resources.
          </p>

          <div class="price-callout-box">
            <div class="price-callout-box__header">
              <span style="font-family:var(--mono); font-size:0.75rem; color:var(--dim); text-transform:uppercase; letter-spacing:0.1em;">
                Registration Fee
              </span>
              <span class="badge" style="background:rgba(0,255,204,0.15); color:var(--cyan); border-color:var(--cyan);">
                95% Off Today
              </span>
            </div>
            <div class="price-callout-box__amount">₹<?= $webinarPrice ?> <span style="font-size:0.95rem; color:#64748b; font-weight:400; text-decoration:line-through;">₹1,999</span></div>
            <div style="font-family:var(--mono); font-size:0.75rem; color:#94a3b8; margin-top:0.4rem;">
              ✓ Includes Live Class + Certificate + GitHub Code + Replay Access
            </div>
          </div>

          <div style="font-family:var(--mono); font-size:0.78rem; color:var(--dim); line-height:1.7;">
            <strong style="color:#ffffff;">UPI Payment Instructions:</strong><br />
            1. Scan QR code from poster or pay ₹96 to our official WhatsApp / UPI.<br />
            2. Enter your UTR / Ref number below, or complete checkout directly via WhatsApp.
          </div>

          <div class="upi-badge-row">
            <span class="upi-badge">Google Pay</span>
            <span class="upi-badge">PhonePe</span>
            <span class="upi-badge">Paytm</span>
            <span class="upi-badge">BHIM UPI</span>
          </div>
        </div>

        <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid rgba(255,255,255,0.08); font-family:var(--mono); font-size:0.75rem; color:var(--faint);">
          Need assistance? Call or WhatsApp our team at <strong style="color:var(--cyan);">+91 72870 60553</strong>.
        </div>
      </div>

      <!-- Right Panel: Registration Form -->
      <div class="register-form-panel">
        
        <!-- Interactive Form -->
        <form id="webinar-reg-form">
          <h3 style="font-family:var(--display); font-size:1.4rem; color:#ffffff; margin-bottom:1.5rem;">
            Participant Details
          </h3>

          <!-- Full Name -->
          <div class="form-field">
            <label class="field-label" for="reg-name">Full Name <span class="req">*</span></label>
            <input type="text" id="reg-name" class="field-input" placeholder="e.g. Rahul Sharma" required />
          </div>

          <!-- WhatsApp Number & Email -->
          <div class="form-row">
            <div class="form-field" style="margin-bottom:0;">
              <label class="field-label" for="reg-phone">WhatsApp Number <span class="req">*</span></label>
              <input type="tel" id="reg-phone" class="field-input" placeholder="+91 98765 43210" required />
            </div>

            <div class="form-field" style="margin-bottom:0;">
              <label class="field-label" for="reg-email">Email Address <span class="req">*</span></label>
              <input type="email" id="reg-email" class="field-input" placeholder="rahul@gmail.com" required />
            </div>
          </div>

          <!-- College / Company -->
          <div class="form-field">
            <label class="field-label" for="reg-college">College / University or Organization</label>
            <input type="text" id="reg-college" class="field-input" placeholder="e.g. SRM University / Self-Employed" />
          </div>

          <!-- Experience Level & Preferred Language -->
          <div class="form-row">
            <div class="form-field" style="margin-bottom:0;">
              <label class="field-label" for="reg-exp">Current Experience</label>
              <select id="reg-exp" class="field-select">
                <option value="College Student (1st - 4th Year)">College Student</option>
                <option value="Fresh Graduate / Job Seeker">Fresh Graduate / Job Seeker</option>
                <option value="Working Professional">Working Professional</option>
                <option value="Complete Beginner to Coding">Complete Beginner</option>
              </select>
            </div>

            <div class="form-field" style="margin-bottom:0;">
              <label class="field-label" for="reg-lang">Preferred Language</label>
              <select id="reg-lang" class="field-select">
                <option value="English">English</option>
                <option value="Tamil">Tamil + English</option>
                <option value="Telugu">Telugu + English</option>
                <option value="Hindi">Hindi + English</option>
              </select>
            </div>
          </div>

          <!-- Have a Coupon or VIP Pass? -->
          <div class="form-field" style="margin-top: 1.2rem; background: rgba(255, 255, 255, 0.02); border: 1px dashed rgba(6, 182, 212, 0.3); border-radius: 10px; padding: 12px 14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
              <label class="field-label" for="reg-coupon" style="margin-bottom:0; color:#38bdf8; font-size:0.75rem;">
                🎟️ Have a Coupon or VIP Code?
              </label>
              <span id="coupon-status-badge" style="display:none; font-family:var(--mono); font-size:0.68rem; padding:2px 8px; border-radius:4px; font-weight:700;"></span>
            </div>
            <div style="display:flex; gap:8px;">
              <input type="text" id="reg-coupon" class="field-input" placeholder="e.g. ZAMZY100" style="text-transform:uppercase; font-family:var(--mono); letter-spacing:0.08em; font-weight:700; margin-bottom:0; flex:1;" />
              <button type="button" id="btn-apply-coupon" class="btn-coupon-apply" style="background:rgba(6,182,212,0.15); border:1px solid var(--cyan); color:var(--cyan); font-family:var(--mono); font-size:0.78rem; font-weight:700; padding:0 16px; border-radius:8px; cursor:pointer; transition:all 0.2s ease;">
                Apply
              </button>
            </div>
            <div id="coupon-feedback" style="font-family:var(--mono); font-size:0.72rem; margin-top:6px; display:none; line-height:1.4;"></div>
          </div>

          <button type="submit" id="reg-submit-btn" class="btn-register-submit">
            <span id="reg-submit-btn-text">Confirm Registration — ₹<?= $webinarPrice ?></span>
            <span>→</span>
          </button>

          <p style="font-family:var(--mono); font-size:0.72rem; color:var(--dim); text-align:center; margin-top:1rem; line-height:1.5;">
            🔒 100% Secure Submission · Instant Access Link will be dispatched to your WhatsApp.
          </p>
        </form>

        <!-- Payment Checkout Box (Step 2: Pay ₹96) -->
        <div id="webinar-payment-box" style="display:none;" class="payment-checkout-card">
          <div style="text-align:center; margin-bottom:1.5rem;">
            <div class="checkout-badge">STEP 2 OF 2 · COMPLETE ₹<?= $webinarPrice ?> PAYMENT</div>
            <h3 style="font-family:var(--display); font-size:1.6rem; color:#ffffff; font-weight:800; margin-top:0.6rem;">
              Scan QR or Tap to Pay ₹<?= $webinarPrice ?>
            </h3>
            <p style="font-family:var(--mono); font-size:0.8rem; color:var(--cyan); margin-top:0.3rem;">
              Registration ID: <strong id="pay-reg-code">ZMW-2026-XXXX</strong>
            </p>
          </div>

          <!-- Dynamic QR Code -->
          <div style="text-align:center; margin-bottom:1.5rem;">
            <div style="display:inline-block; padding:12px; background:#ffffff; border-radius:14px; box-shadow:0 10px 30px rgba(0,255,204,0.2);">
              <img id="pay-qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=upi://pay?pa=8667702473@fam%26pn=Sameer%20Ahamadh%26am=96%26cu=INR%26tn=Webinar_Registration" alt="UPI QR Code" style="width:200px; height:200px; display:block;" />
            </div>
            <div style="font-family:var(--mono); font-size:0.75rem; color:var(--dim); margin-top:0.6rem;">
              Scan with GPay, PhonePe, Paytm, or FamPay
            </div>
          </div>

          <!-- Direct Pay Button on Mobile -->
          <a href="#" id="pay-upi-btn" class="btn-register-submit" style="text-decoration:none; margin-bottom:1.2rem; background:linear-gradient(135deg, #10b981 0%, #06b6d4 100%);">
            <span>📲 Tap to Pay ₹<?= $webinarPrice ?> via UPI App</span>
            <span>→</span>
          </a>

          <div style="text-align:center; margin-bottom:1.4rem; padding:0.8rem; background:rgba(255,255,255,0.03); border:1px dashed rgba(255,255,255,0.12); border-radius:8px;">
            <span style="font-family:var(--mono); font-size:0.75rem; color:#94a3b8;">UPI ID:</span>
            <strong style="font-family:var(--mono); font-size:0.85rem; color:#ffffff; margin-left:4px;">8667702473@fam</strong>
          </div>

          <!-- UTR Verification Form -->
          <div style="border-top:1px solid rgba(255,255,255,0.08); padding-top:1.2rem;">
            <label class="field-label" for="manual-utr-input">Already Paid? Enter 12-digit UTR / Reference No:</label>
            <div style="display:flex; gap:8px;">
              <input type="text" id="manual-utr-input" class="field-input" placeholder="e.g. 423589012345" style="margin-bottom:0;" />
              <button type="button" id="manual-verify-btn" class="btn btn-sm" style="background:var(--cyan); color:#000; font-family:var(--mono); font-weight:700; border-radius:8px; padding:0 18px; white-space:nowrap;">
                Verify
              </button>
            </div>
            <div id="verify-feedback" style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-top:6px; min-height:16px;">
              ⏳ Waiting for payment confirmation...
            </div>
          </div>
        </div>

        <!-- Success State Box -->
        <div id="reg-success-box" class="registration-success-box" style="display:none;">
          <div style="font-size:3.5rem; margin-bottom:0.6rem;">🎉</div>
          <div class="modal-celebrate-badge" style="margin-bottom:0.8rem;">
            <span class="pulse-dot"></span>
            PAYMENT CONFIRMED · SEAT RESERVED
          </div>
          <h3 style="font-family:var(--display); font-size:1.9rem; color:#ffffff; font-weight:800; margin-bottom:0.4rem;">
            Payment Successful &amp; Seat Confirmed!
          </h3>
          
          <div class="reg-code-badge" id="display-reg-code">
            ZMW-2026-XXXX
          </div>

          <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(0,255,204,0.3); border-radius:12px; padding:1.4rem; margin:1.4rem 0; text-align:left;">
            <div style="display:flex; align-items:flex-start; gap:12px;">
              <div style="font-size:1.8rem; line-height:1;">📬</div>
              <div>
                <div style="font-family:var(--display); font-size:1.05rem; font-weight:700; color:#ffffff; margin-bottom:4px;">
                  Access Materials Dispatched!
                </div>
                <p style="font-size:0.88rem; color:#cbd5e1; line-height:1.6; margin:0;">
                  You will receive your <strong>Live Meeting Room link</strong>, <strong>Session Schedule</strong>, and <strong>Study Materials / PDFs</strong> directly on your registered <strong>Email</strong> and <strong>WhatsApp message</strong>.
                </p>
              </div>
            </div>
          </div>

          <a href="<?= htmlspecialchars(getSetting('webinar_whatsapp_link', 'https://chat.whatsapp.com/sample-zamzy-fullstack')) ?>" id="wa-confirm-btn" target="_blank" rel="noopener" class="modal-wa-cta-btn" style="text-decoration:none;">
            <svg width="22" height="22" viewBox="0 0 448 512" style="fill:currentColor;"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
            <span>💬 Join Exclusive WhatsApp Community</span>
          </a>

          <a href="#" id="success-invoice-btn" target="_blank" rel="noopener" style="display:none; align-items:center; justify-content:center; gap:0.6rem; margin-top:0.8rem; padding:0.85rem 1.4rem; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.4); color:#38bdf8; text-decoration:none; border-radius:8px; font-weight:700; font-size:0.92rem; width:100%;">
            📄 Download Official PDF Tax Invoice / Receipt
          </a>
        </div>

      </div>

    </div>

  </section>

  <!-- ═══════════════════════════════════════════════
       HELP & SUPPORT STRIP (FROM POSTER)
  ═══════════════════════════════════════════════ -->
  <section class="contact-strip">
    <div class="contact-strip__box">
      
      <div class="contact-strip__item">
        <div class="contact-strip__icon">📞</div>
        <div>
          <div style="font-family:var(--display); font-size:1.15rem; color:#ffffff; font-weight:700;">+91 72870 60553</div>
          <div style="font-family:var(--mono); font-size:0.75rem; color:var(--dim);">Have Questions? Call or WhatsApp</div>
        </div>
      </div>

      <div class="contact-strip__item">
        <div class="contact-strip__icon">🌐</div>
        <div>
          <div style="font-family:var(--display); font-size:1.15rem; color:#ffffff; font-weight:700;">zamzy.in</div>
          <div style="font-family:var(--mono); font-size:0.75rem; color:var(--dim);">Visit Our Official Website</div>
        </div>
      </div>

      <div>
        <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20inquiring%20about%20the%20Full%20Stack%20Web%20Development%20Webinar." target="_blank" rel="noopener" class="btn btn-sm" style="background:#25D366; color:#ffffff; border-color:#25D366;">
          Chat with Support
        </a>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════
       FOOTER
  ═══════════════════════════════════════════════ -->
  <!-- ═══════════════════════════════════════════════
       FOOTER
  ═══════════════════════════════════════════════ -->
  <footer class="footer">
    <div class="footer-inner">
      <!-- Column 1: Brand & Bio -->
      <div class="footer-brand">
        <div class="footer-logo">
          <img src="images/logo.png" alt="ZAMZY" class="brand-logo-img" />
        </div>
        <p class="footer-tagline">
          ZAMZY.IN — Engineering High-Performance SaaS Platforms, Mobile Ecosystems &amp; Automated Cloud Systems.
        </p>
        <span class="footer-copy">© 2026 ZAMZY Digital Engineering Agency. All rights reserved.</span>
      </div>

      <!-- Column 2: Navigation Links -->
      <div>
        <div class="footer-col-title">Navigation</div>
        <div class="footer-links-col">
          <a href="index.html#hero">Home</a>
          <a href="index.html#about">Studio</a>
          <a href="index.html#launchpad">Launchpad</a>
          <a href="index.html#products">Products</a>
          <a href="careers">Careers &amp; Guild</a>
          <a href="fullstack-webinar" style="color:var(--cyan); font-weight:700;">Full Stack Webinar</a>
          <a href="contact.php">Contact Us</a>
        </div>
      </div>

      <!-- Column 3: Merchant Compliance Policies -->
      <div>
        <div class="footer-col-title">Merchant Policies</div>
        <div class="footer-links-col">
          <a href="privacy-policy.php">Privacy Policy</a>
          <a href="terms-and-conditions.php">Terms &amp; Conditions</a>
          <a href="refund-policy.php">Refund &amp; Cancellation</a>
          <a href="shipping-policy.php">Shipping &amp; Delivery</a>
          <a href="contact.php">Contact Support</a>
        </div>
      </div>

      <!-- Column 4: Office & Social Links -->
      <div>
        <div class="footer-col-title">Studio Office</div>
        <div class="footer-links-col" style="font-size:0.82rem; color:var(--dim); line-height:1.7;">
          <p><strong>ZAMZY DIGITAL SOLUTIONS</strong></p>
          <p>📍 Hitech City, Hyderabad, Telangana, India</p>
          <p>💬 <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20contacting%20from%20your%20webinar%20page." target="_blank" style="color:var(--cyan); text-decoration:underline;">+91 72870 60553 (WhatsApp)</a></p>
          <p>✉️ <a href="mailto:contact@zamzy.in" style="color:var(--cyan); text-decoration:underline;">contact@zamzy.in</a></p>
          <div class="social-circle-links">
            <a href="https://www.instagram.com/zamzy.in" target="_blank" rel="noopener" class="social-circle-btn instagram" aria-label="Instagram" title="Follow us on Instagram">
              <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
            <a href="https://www.facebook.com/share/1BwUK4uNSr/" target="_blank" rel="noopener" class="social-circle-btn facebook" aria-label="Facebook" title="Follow us on Facebook">
              <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="https://www.linkedin.com/company/zamzy/" target="_blank" rel="noopener" class="social-circle-btn linkedin" aria-label="LinkedIn" title="Connect with us on LinkedIn">
              <svg viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </a>
            <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20contacting%20from%20your%20webinar%20page." target="_blank" rel="noopener" class="social-circle-btn whatsapp" aria-label="WhatsApp" title="Chat on WhatsApp (+91 72870 60553)">
              <svg viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom-bar">
      <div class="footer-bottom-text">
        ⚡ High-Concurrency Systems · 99.9% Uptime SLA · Hitech City, Hyderabad
      </div>
      <div class="footer-social-links">
        <a href="privacy-policy.php" class="footer-social-link">Privacy Policy</a>
        <a href="terms-and-conditions.php" class="footer-social-link">Terms &amp; Conditions</a>
        <a href="refund-policy.php" class="footer-social-link">Refund Policy</a>
        <a href="shipping-policy.php" class="footer-social-link">Shipping Policy</a>
      </div>
    </div>
  </footer>


  <!-- ═══════════════════════════════════════════════
       FLOATING WHATSAPP BUTTON (BOTTOM LEFT)
  ═══════════════════════════════════════════════ -->
  <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20interested%20in%20registering%20for%20the%20Full%20Stack%20Web%20Development%20Webinar%20(Rs.%2096)." 
     target="_blank" 
     rel="noopener" 
     class="whatsapp-float-btn" 
     aria-label="Chat on WhatsApp (+91 72870 60553)" 
     title="Chat with ZAMZY on WhatsApp">
    <div class="whatsapp-float-tooltip">Chat with ZAMZY</div>
    <svg viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
  </a>

  <!-- ═══════════════════════════════════════════════
       PAYMENT SUCCESS & SEAT CONFIRMED MODAL POPUP
  ═══════════════════════════════════════════════ -->
  <div id="payment-success-modal" class="webinar-success-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title-heading">
    <div class="webinar-success-modal__backdrop" id="closeSuccessModalBackdrop"></div>
    <div class="webinar-success-modal__card">
      <button type="button" class="webinar-success-modal__close" id="closeSuccessModalBtn" aria-label="Close modal">&times;</button>
      
      <div class="modal-celebrate-badge">
        <span class="pulse-dot"></span>
        PAYMENT CONFIRMED · SEAT RESERVED
      </div>

      <div class="modal-icon-wrap">
        🎉
      </div>

      <h2 class="modal-title" id="modal-title-heading">
        Seat Confirmed! Welcome to ZAMZY Cohort 🚀
      </h2>

      <div class="modal-reg-pill">
        Registration Code: <strong id="modal-reg-code">ZMW-2026-XXXX</strong>
      </div>

      <div class="modal-delivery-card">
        <div class="delivery-card-header">
          <span class="delivery-icon">📬</span>
          <div>
            <div class="delivery-title">Access Materials Dispatched via Email &amp; WhatsApp</div>
            <div class="delivery-sub">
              You will receive all workshop details, live Google Meet room link, and study materials on your <strong>WhatsApp</strong> and registered <strong>Email</strong>.
            </div>
          </div>
        </div>

        <div class="delivery-items-list">
          <div class="delivery-item">
            <span class="item-check">✓</span>
            <span><strong>Live Meeting Room:</strong> Google Meet / Zoom link delivered to your inbox &amp; phone.</span>
          </div>
          <div class="delivery-item">
            <span class="item-check">✓</span>
            <span><strong>Course Resources:</strong> Full Stack Architecture Blueprint, PDFs &amp; GitHub starter kits sent.</span>
          </div>
          <div class="delivery-item">
            <span class="item-check">✓</span>
            <span><strong>Community Access:</strong> Exclusive discussion group for doubt clearing &amp; announcements.</span>
          </div>
        </div>
      </div>

      <!-- Action Button for WhatsApp Community Group -->
      <a href="https://chat.whatsapp.com/sample-zamzy-fullstack" target="_blank" rel="noopener" id="modal-whatsapp-link-btn" class="modal-wa-cta-btn">
        <svg width="22" height="22" viewBox="0 0 448 512" style="fill:currentColor;"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
        <span>💬 Join Exclusive WhatsApp Community</span>
        <span>→</span>
      </a>

      <!-- Action Button for PDF Tax Invoice / Receipt Download -->
      <a href="#" target="_blank" rel="noopener" id="modal-invoice-link-btn" style="display:none; align-items:center; justify-content:center; gap:0.6rem; width:100%; margin-top:0.8rem; padding:0.85rem; border:1px solid rgba(56,189,248,0.4); background:rgba(56,189,248,0.08); color:#38bdf8; text-decoration:none; border-radius:8px; font-weight:700; font-size:0.92rem;">
        📄 Download Official PDF Tax Receipt / Invoice
      </a>

      <div style="margin-top:1.2rem; text-align:center;">
        <button type="button" class="modal-done-btn" id="modalDismissBtn">
          ✓ Done / Return to Overview
        </button>
      </div>

    </div>
  </div>

  <!-- Scripts -->
  <script>
    // Mobile Drawer Toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    if (menuToggle && mobileMenu) {
      menuToggle.addEventListener('click', () => {
        const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
        menuToggle.setAttribute('aria-expanded', !isOpen);
        mobileMenu.classList.toggle('open');
      });
      mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
          menuToggle.setAttribute('aria-expanded', 'false');
          mobileMenu.classList.remove('open');
        });
      });
    }

    // Modal Control Functions
    function showPaymentSuccessModal(regCode, waLink, invoiceUrl) {
      const modal = document.getElementById('payment-success-modal');
      const regCodeEl = document.getElementById('modal-reg-code');
      const waBtn = document.getElementById('modal-whatsapp-link-btn');
      const invoiceBtn = document.getElementById('modal-invoice-link-btn');
      const succInvoiceBtn = document.getElementById('success-invoice-btn');

      if (regCodeEl && regCode) {
        regCodeEl.textContent = regCode;
      }
      if (waBtn && waLink) {
        waBtn.href = waLink;
      }
      if (invoiceUrl && invoiceUrl.startsWith('http')) {
        if (invoiceBtn) {
          invoiceBtn.href = invoiceUrl;
          invoiceBtn.style.display = 'flex';
        }
        if (succInvoiceBtn) {
          succInvoiceBtn.href = invoiceUrl;
          succInvoiceBtn.style.display = 'flex';
        }
      }
      if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    }

    function hidePaymentSuccessModal() {
      const modal = document.getElementById('payment-success-modal');
      if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    }

    document.getElementById('closeSuccessModalBtn')?.addEventListener('click', hidePaymentSuccessModal);
    document.getElementById('closeSuccessModalBackdrop')?.addEventListener('click', hidePaymentSuccessModal);
    document.getElementById('modalDismissBtn')?.addEventListener('click', hidePaymentSuccessModal);

    // Interactive Registration & FamPay / UPI Payment Handler
    const regForm = document.getElementById('webinar-reg-form');
    const submitBtn = document.getElementById('reg-submit-btn');
    const paymentBox = document.getElementById('webinar-payment-box');
    const payRegCode = document.getElementById('pay-reg-code');
    const payQrImg = document.getElementById('pay-qr-img');
    const payUpiBtn = document.getElementById('pay-upi-btn');
    const manualUtrInput = document.getElementById('manual-utr-input');
    const manualVerifyBtn = document.getElementById('manual-verify-btn');
    const verifyFeedback = document.getElementById('verify-feedback');

    const successBox = document.getElementById('reg-success-box');
    const displayCode = document.getElementById('display-reg-code');
    const waConfirmBtn = document.getElementById('wa-confirm-btn');

    let activeRegCode = '';
    let pollInterval = null;

    function handlePaymentConfirmed(regCode, waLink, invoiceUrl) {
      if (pollInterval) clearInterval(pollInterval);
      if (paymentBox) paymentBox.style.display = 'none';
      if (regForm) regForm.style.display = 'none';

      if (displayCode) {
        displayCode.innerHTML = `<span style="color:#10b981; font-weight:700;">✓ SEAT UNLOCKED · ${regCode}</span>`;
      }
      if (waConfirmBtn && waLink) {
        waConfirmBtn.href = waLink;
      }
      const succInvoiceBtn = document.getElementById('success-invoice-btn');
      if (succInvoiceBtn && invoiceUrl && invoiceUrl.startsWith('http')) {
        succInvoiceBtn.href = invoiceUrl;
        succInvoiceBtn.style.display = 'flex';
      }
      if (successBox) {
        successBox.style.display = 'block';
        successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      showPaymentSuccessModal(regCode, waLink, invoiceUrl);
    }

    // Manual UTR Verification
    if (manualVerifyBtn) {
      manualVerifyBtn.addEventListener('click', async () => {
        const utrVal = manualUtrInput ? manualUtrInput.value.trim() : '';
        if (!utrVal || utrVal.length < 6) {
          alert('Please enter a valid 12-digit UTR / UPI Reference Number from your payment receipt.');
          return;
        }

        manualVerifyBtn.disabled = true;
        manualVerifyBtn.textContent = 'Verifying...';
        if (verifyFeedback) verifyFeedback.textContent = '🔄 Checking transaction with bank server...';

        try {
          const vData = new FormData();
          vData.append('action', 'submit_manual_utr');
          vData.append('reg_code', activeRegCode);
          vData.append('utr', utrVal);

          const vRes = await fetch('api.php', { method: 'POST', body: vData });
          const vJson = await vRes.json();

          if (vJson.success) {
            handlePaymentConfirmed(activeRegCode, vJson.whatsapp_community_link);
          } else {
            alert(vJson.message || 'Verification failed. Please check UTR number.');
            manualVerifyBtn.disabled = false;
            manualVerifyBtn.textContent = 'Verify';
            if (verifyFeedback) verifyFeedback.textContent = '❌ Verification issue. Please re-enter UTR or retry.';
          }
        } catch (e) {
          alert('Verification request failed. Please check your internet connection.');
          manualVerifyBtn.disabled = false;
          manualVerifyBtn.textContent = 'Verify';
        }
      });
    }

    // Coupon Code Management
    let activeCoupon = '';
    let couponDiscount = 0;
    let payableAmount = <?= $webinarPrice ?>;
    let isFreeSeat = false;

    const couponInput = document.getElementById('reg-coupon');
    const applyCouponBtn = document.getElementById('btn-apply-coupon');
    const couponFeedback = document.getElementById('coupon-feedback');
    const couponBadge = document.getElementById('coupon-status-badge');
    const submitBtnText = document.getElementById('reg-submit-btn-text');

    if (applyCouponBtn) {
      applyCouponBtn.addEventListener('click', async () => {
        const codeVal = couponInput ? couponInput.value.trim().toUpperCase() : '';
        if (!codeVal) {
          if (couponFeedback) {
            couponFeedback.style.display = 'block';
            couponFeedback.style.color = '#ef4444';
            couponFeedback.textContent = 'Please enter a coupon code first.';
          }
          return;
        }

        applyCouponBtn.disabled = true;
        applyCouponBtn.textContent = 'Checking...';

        try {
          const cRes = await fetch(`api.php?action=validate_coupon&code=${encodeURIComponent(codeVal)}&amount=<?= $webinarPrice ?>`);
          const cJson = await cRes.json();

          if (cJson.success) {
            activeCoupon = cJson.coupon_code;
            couponDiscount = cJson.discount_amount;
            payableAmount = cJson.final_amount;
            isFreeSeat = cJson.is_free;

            if (couponFeedback) {
              couponFeedback.style.display = 'block';
              couponFeedback.style.color = '#10b981';
              couponFeedback.innerHTML = `<strong>${cJson.message}</strong>`;
            }

            if (couponBadge) {
              couponBadge.style.display = 'inline-block';
              couponBadge.style.background = isFreeSeat ? 'rgba(16,185,129,0.2)' : 'rgba(6,182,212,0.2)';
              couponBadge.style.border = isFreeSeat ? '1px solid #10b981' : '1px solid #06b6d4';
              couponBadge.style.color = isFreeSeat ? '#34d399' : '#38bdf8';
              couponBadge.textContent = isFreeSeat ? '100% FREE VIP PASS' : `₹${couponDiscount} OFF`;
            }

            if (submitBtnText) {
              if (isFreeSeat) {
                submitBtnText.textContent = '🎉 Claim 100% Free VIP Seat — ₹0';
              } else {
                submitBtnText.textContent = `Confirm Registration — ₹${payableAmount} (₹${couponDiscount} OFF)`;
              }
            }

            applyCouponBtn.textContent = '✓ Applied';
            applyCouponBtn.style.borderColor = '#10b981';
            applyCouponBtn.style.color = '#10b981';
            applyCouponBtn.disabled = false;
          } else {
            activeCoupon = '';
            couponDiscount = 0;
            payableAmount = 96;
            isFreeSeat = false;

            if (couponFeedback) {
              couponFeedback.style.display = 'block';
              couponFeedback.style.color = '#ef4444';
              couponFeedback.textContent = cJson.message || 'Invalid or expired coupon code.';
            }

            if (couponBadge) couponBadge.style.display = 'none';
            if (submitBtnText) submitBtnText.textContent = 'Confirm Registration — ₹<?= $webinarPrice ?>';

            applyCouponBtn.textContent = 'Apply';
            applyCouponBtn.style.borderColor = 'var(--cyan)';
            applyCouponBtn.style.color = 'var(--cyan)';
            applyCouponBtn.disabled = false;
          }
        } catch (e) {
          if (couponFeedback) {
            couponFeedback.style.display = 'block';
            couponFeedback.style.color = '#ef4444';
            couponFeedback.textContent = 'Could not verify coupon. Check connection.';
          }
          applyCouponBtn.textContent = 'Apply';
          applyCouponBtn.disabled = false;
        }
      });
    }

    if (regForm) {
      regForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const fullName = document.getElementById('reg-name').value.trim();
        const phone = document.getElementById('reg-phone').value.trim();
        const email = document.getElementById('reg-email').value.trim();
        const college = document.getElementById('reg-college').value.trim();
        const exp = document.getElementById('reg-exp').value;
        const lang = document.getElementById('reg-lang').value;

        if (!fullName || !phone || !email) {
          alert('Please fill in your name, phone number, and email.');
          return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>' + (isFreeSeat ? 'Unlocking Your Free Seat...' : 'Proceeding to Payment...') + '</span>';

        try {
          // 1. Submit Registration Record to Database
          const formData = new FormData();
          formData.append('action', 'submit_webinar_registration');
          formData.append('full_name', fullName);
          formData.append('phone', phone);
          formData.append('email', email);
          formData.append('college_or_company', college);
          formData.append('experience_level', exp);
          formData.append('preferred_language', lang);
          formData.append('coupon_code', activeCoupon);

          const response = await fetch('api.php', {
            method: 'POST',
            body: formData
          });

          const result = await response.json();

          if (result.success) {
            activeRegCode = result.reg_code;

            // Track Facebook Pixel Lead event
            if (typeof fbq === 'function') {
              fbq('track', 'Lead', {
                content_name: 'Full Stack Web Development Webinar',
                value: result.amount || 0.00,
                currency: 'INR'
              });
            }

            // If 100% Free VIP Pass (via coupon waiver)
            if (result.is_free) {
              handlePaymentConfirmed(result.reg_code, result.whatsapp_community_link);
              return;
            }

            // 2. Initiate FamPay / Gateway Checkout Order for remaining amount
            const fampayData = new FormData();
            fampayData.append('action', 'create_fampay_order');
            fampayData.append('reg_code', result.reg_code);
            fampayData.append('full_name', fullName);
            fampayData.append('phone', phone);
            fampayData.append('email', email);
            fampayData.append('amount', result.amount || payableAmount);

            const fpResponse = await fetch('api.php', {
              method: 'POST',
              body: fampayData
            });

            const fpResult = await fpResponse.json();
            const targetPayUrl = fpResult.checkout_url || fpResult.payment_url;

            // Hosted Payment Gateway Page Redirect (Open checkout page immediately)
            if (fpResult.success && targetPayUrl && targetPayUrl.startsWith('http') && !targetPayUrl.includes('qrserver.com')) {
              window.location.href = targetPayUrl;
              return;
            }

            // Direct Payment Page View (Step 2: Pay via QR / UPI)
            regForm.style.display = 'none';

            if (payRegCode) payRegCode.textContent = result.reg_code;
            if (payQrImg && fpResult.qr_url) payQrImg.src = fpResult.qr_url;
            if (payUpiBtn && (fpResult.upi_intent || targetPayUrl)) {
              payUpiBtn.href = fpResult.upi_intent || targetPayUrl;
            }

            if (paymentBox) {
              paymentBox.style.display = 'block';
              paymentBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Start live verification polling every 3 seconds
            pollInterval = setInterval(async () => {
              try {
                const chk = await fetch(`api.php?action=check_order_status&reg_code=${encodeURIComponent(result.reg_code)}`);
                const chkRes = await chk.json();
                if (chkRes.success && chkRes.seat_unlocked) {
                  handlePaymentConfirmed(result.reg_code, chkRes.whatsapp_community_link || result.whatsapp_url, chkRes.invoice_url);
                }
              } catch (e) {}
            }, 3000);

          } else {
            alert('Error: ' + (result.message || 'Could not record registration. Please try again.'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>' + (submitBtnText ? submitBtnText.textContent : 'Confirm Registration — ₹96') + '</span><span>→</span>';
          }
        } catch (err) {
          alert('Network error. Please try again or message us on WhatsApp.');
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<span>' + (submitBtnText ? submitBtnText.textContent : 'Confirm Registration — ₹96') + '</span><span>→</span>';
        }
      });
    }

    // Auto-detect return from FamGateway payment redirect
    document.addEventListener('DOMContentLoaded', async () => {
      const urlParams = new URLSearchParams(window.location.search);
      const retRegCode = urlParams.get('reg_code');
      const retStatus = urlParams.get('status');

      if (retRegCode && (retStatus === 'success' || retStatus === 'completed')) {
        if (regForm && successBox) {
          regForm.style.display = 'none';
          displayCode.textContent = retRegCode;
          successBox.style.display = 'block';
          successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

          try {
            const chk = await fetch(`api.php?action=check_order_status&reg_code=${encodeURIComponent(retRegCode)}`);
            const chkRes = await chk.json();
            const waCommunityLink = (chkRes && chkRes.whatsapp_community_link) ? chkRes.whatsapp_community_link : 'https://chat.whatsapp.com/sample-zamzy-fullstack';
            const invoiceUrl = (chkRes && chkRes.invoice_url) ? chkRes.invoice_url : '';
            
            if (chkRes.success && chkRes.seat_unlocked) {
              displayCode.innerHTML = `<span style="color:#10b981; font-weight:700;">✓ SEAT UNLOCKED · ${retRegCode}</span>`;
              const heading = successBox.querySelector('h3');
              if (heading) {
                heading.textContent = 'Payment Verified & Seat Confirmed!';
                heading.style.color = '#10b981';
              }
            }
            
            // Trigger the success modal popup
            showPaymentSuccessModal(retRegCode, waCommunityLink, invoiceUrl);
          } catch(e) {
            showPaymentSuccessModal(retRegCode, 'https://chat.whatsapp.com/sample-zamzy-fullstack');
          }
        }
      }
    });
  </script>

  <!-- ═══ BACK TO TOP BUTTON ═══ -->
  <button id="back-to-top" class="back-to-top" aria-label="Back to top" title="Back to top">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
  </button>

  <!-- ═══ AI CHATBOT FLOATING ICON ═══ -->
  <button id="ai-chat-btn" class="ai-chat-btn" aria-label="Chat with ZAMZY AI" title="Chat with ZAMZY AI">
    <img src="images/ai-chat-icon.jpg" alt="ZAMZY AI" />
    <span class="ai-chat-badge">AI</span>
  </button>

  <!-- AI Chat Modal -->
  <div id="ai-chat-modal" class="ai-chat-modal">
    <div class="ai-chat-panel">
      <div class="ai-chat-header">
        <div class="ai-chat-header-info">
          <div class="ai-chat-avatar" style="overflow:hidden; padding:0;">
            <img src="images/ai-chat-icon.jpg" alt="AI" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" />
          </div>
          <div>
            <div class="ai-chat-name">ZAMZY Assistant</div>
            <div class="ai-chat-status"><span class="ai-online-dot"></span> Online</div>
          </div>
        </div>
        <button id="close-ai-chat" class="ai-chat-close" aria-label="Close chat">&times;</button>
      </div>
      <div class="ai-chat-messages" id="ai-chat-messages">
        <div class="ai-msg ai-msg--bot">
          <div class="ai-msg-bubble">👋 Hi! I'm ZAMZY's assistant. Interested in the Full Stack Webinar? Ask me anything about the curriculum, schedule, or registration!</div>
        </div>
        <div class="ai-quick-btns">
          <button class="ai-quick-btn" data-msg="Tell me about the Full Stack Webinar">About Webinar</button>
          <button class="ai-quick-btn" data-msg="How do I register for the webinar?">How to Register</button>
          <button class="ai-quick-btn" data-msg="What do I learn in the webinar?">Curriculum</button>
        </div>
      </div>
      <form class="ai-chat-input-row" id="ai-chat-form" autocomplete="off">
        <input type="text" id="ai-chat-input" class="ai-chat-input" placeholder="Type your message..." autocomplete="off" />
        <button type="submit" class="ai-send-btn" aria-label="Send">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
          </svg>
        </button>
      </form>
    </div>
  </div>

  <!-- ═══ SHARED SCRIPTS ═══ -->
  <script src="tooplate-vora-bold-script.js"></script>
  <script>
    // Back to top
    (function(){
      var backBtn = document.getElementById('back-to-top');
      if (backBtn) {
        window.addEventListener('scroll', function(){
          backBtn.classList.toggle('visible', window.scrollY > 400);
        }, {passive:true});
        backBtn.addEventListener('click', function(){
          window.scrollTo({top:0, behavior:'smooth'});
        });
      }
    })();
  </script>
</body>
</html>
