<?php
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Cancellation &amp; Refund Policy — ZAMZY Digital Engineering | Hitech City, Hyderabad</title>
  <meta name="description" content="Cancellation and Refund Policy for ZAMZY Digital Solutions. Clear terms for milestone refunds, project cancellations, and Razorpay/PayPal SLA timelines." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://zamzy.in/refund-policy.php" />

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

  <!-- Fonts & Core Styles -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="tooplate-vora-bold-style.css" />

  <style>
    .legal-page-hero {
      padding: 130px 5vw 40px 5vw;
      text-align: center;
      position: relative;
      z-index: 2;
    }
    .legal-hero__eyebrow {
      font-family: var(--mono);
      font-size: 0.75rem;
      letter-spacing: 0.25em;
      color: var(--cyan);
      text-transform: uppercase;
      margin-bottom: 0.8rem;
    }
    .legal-hero__title {
      font-family: var(--display);
      font-size: clamp(2rem, 4.5vw, 3.4rem);
      font-weight: 700;
      color: var(--white);
      margin-bottom: 1rem;
    }
    .legal-hero__meta {
      font-family: var(--mono);
      font-size: 0.8rem;
      color: var(--dim);
    }

    .legal-content-container {
      max-width: 900px;
      margin: 0 auto;
      padding: 20px 5vw 100px 5vw;
      position: relative;
      z-index: 2;
    }
    .legal-box {
      background: rgba(10, 10, 18, 0.85);
      border: 1px solid rgba(6, 182, 212, 0.25);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-radius: 20px;
      padding: 3rem 2.5rem;
      color: var(--dim);
      font-family: var(--mono);
      font-size: 0.88rem;
      line-height: 1.8;
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.6);
    }
    .legal-box h2 {
      font-family: var(--display);
      font-size: 1.35rem;
      color: var(--white);
      margin: 2rem 0 0.8rem 0;
      padding-bottom: 0.4rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .legal-box h2:first-child {
      margin-top: 0;
    }
    .legal-box p {
      margin-bottom: 1.2rem;
    }
    .legal-box ul {
      margin: 0.5rem 0 1.2rem 1.5rem;
      list-style-type: square;
    }
    .legal-box li {
      margin-bottom: 0.5rem;
    }
    .legal-highlight {
      color: var(--cyan);
      font-weight: 600;
    }
  </style>
</head>
<body>

  <!-- Ambient Cosmic Aura -->
  <div class="aura"></div>

  <!-- Navigation Bar -->
  <nav class="nav">
    <a href="index.html" class="brand-logo-wrap">
      <img src="images/logo.png" alt="ZAMZY" class="brand-logo-img" />
    </a>
    <ul class="nav-links">
      <li><a href="index.html#about">Studio</a></li>
      <li><a href="index.html#launchpad">Launchpad</a></li>
      <li><a href="index.html#products">Products</a></li>
      <li><a href="index.html#services">Services</a></li>
      <li><a href="index.html#testimonials">Reviews</a></li>
      <li><a href="index.html#rates">Rates</a></li>
      <li><a href="careers#apply-guild">Careers</a></li>
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
    <div class="mobile-menu-dropdown">
      <button type="button" class="mobile-menu-dropdown-toggle">
        <span>🎟️ Events</span>
        <span class="mobile-menu-arrow">▼</span>
      </button>
      <div class="mobile-menu-dropdown-content">
        <a href="fullstack-webinar" class="mobile-event-card">
          <span class="mobile-event-card-badge">LIVE WEBINAR</span>
          <span class="mobile-event-card-title">🚀 Full Stack Web Dev</span>
          <span class="mobile-event-card-desc">From Scratch to Cloud Deployment · ₹96</span>
        </a>
      </div>
    </div>
    <a href="careers#apply-guild">Careers &amp; Guild</a>
    <a href="contact.php">Contact</a>
  </div>

  <!-- Legal Hero Header -->
  <header class="legal-page-hero">
    <div class="legal-hero__eyebrow">CUSTOMER GUARANTEE &amp; REFUND DISCLOSURE</div>
    <h1 class="legal-hero__title">Cancellation &amp; Refund Policy</h1>
    <div class="legal-hero__meta">
      Entity: ZAMZY DIGITAL SOLUTIONS · Effective Date: January 1, 2026 · Payment Partners: Razorpay &amp; PayPal
    </div>
  </header>

  <!-- Main Legal Document Content -->
  <main class="legal-content-container">
    <div class="legal-box">
      
      <h2>1. Overview &amp; Commitment</h2>
      <p>
        At <span class="legal-highlight">ZAMZY DIGITAL SOLUTIONS</span> ("ZAMZY"), customer satisfaction, transparent milestone deliverables, and fair commercial practices are core to our engineering operations. This Cancellation and Refund Policy outlines your rights regarding project cancellations, advance milestone refunds, and digital service subscription adjustments.
      </p>

      <h2>2. Project Cancellation Terms</h2>
      <p>Clients may cancel custom software development projects subject to the following milestone stages:</p>
      <ul>
        <li><strong>Pre-Initiation Cancellation (Before Work Commences):</strong> If a cancellation request is submitted in writing within 48 hours of making an initial milestone payment and prior to architectural discovery or repository setup, a <span class="legal-highlight">100% full refund</span> will be issued.</li>
        <li><strong>Mid-Milestone In-Progress Cancellation:</strong> If work has commenced under an active Statement of Work (SOW), the client is entitled to a pro-rata refund for uncompleted future milestones. Work completed and approved up to the cancellation date is non-refundable.</li>
        <li><strong>Final Project Completion &amp; Git Transfer:</strong> Once a project milestone is reviewed, approved, and final source code/Git repository ownership is transferred to the client, payments for that milestone are non-refundable.</li>
      </ul>

      <h2>3. Ready-Made SaaS Products &amp; Sandbox Demos</h2>
      <p>
        For pre-built software products (such as CRM Suites, IVR Telephony Gateways, School ERPs, and POS Systems):
      </p>
      <ul>
        <li>Clients are provided free live sandbox demo credentials prior to purchase.</li>
        <li>If a ready-made platform suffers from unresolvable core technical failures that prevent deployment and cannot be rectified by our engineering team within 7 business days, a <span class="legal-highlight">100% full refund</span> is granted.</li>
      </ul>

      <h2>4. Refund Processing SLA &amp; Timelines (Razorpay &amp; PayPal)</h2>
      <p>When a refund is approved by our billing team:</p>
      <ul>
        <li><strong>Processing SLA:</strong> Refunds are initiated within <strong>24 to 48 business hours</strong> of written approval.</li>
        <li><strong>Payment Destination:</strong> Refunds are credited strictly back to the original payment source used during checkout (Original Credit/Debit Card, Netbanking account, UPI VPA, or PayPal balance).</li>
        <li><strong>Crediting Timeline:</strong>
          <ul>
            <li><strong>Razorpay (India - UPI / Netbanking / Cards):</strong> Appears in the bank account within <span class="legal-highlight">5 to 7 business days</span> depending on the issuer bank.</li>
            <li><strong>PayPal (International):</strong> Appears in the PayPal account / card within <span class="legal-highlight">3 to 5 business days</span>.</li>
          </ul>
        </li>
      </ul>

      <h2>5. How to Request a Refund or Cancellation</h2>
      <p>To request a cancellation or submit a refund inquiry, please follow these steps:</p>
      <ol>
        <li>Send an email to <a href="mailto:contact@zamzy.in" class="legal-highlight">contact@zamzy.in</a> with the subject line <strong>"Refund Request - [Invoice / Order ID]"</strong>.</li>
        <li>Include your registered Name, Mobile Number, Project Title, and reason for cancellation.</li>
        <li>Our billing desk will review the milestone state and respond within 24 business hours.</li>
      </ol>

      <h2>6. Contact Details for Refund Queries</h2>
      <p>
        <strong>Billing &amp; Merchant Support Desk:</strong> ZAMZY Digital Solutions<br />
        <strong>Address:</strong> Hitech City, Hyderabad, Telangana 500081, India.<br />
        <strong>Email:</strong> <a href="mailto:contact@zamzy.in" class="legal-highlight">contact@zamzy.in</a> / <a href="mailto:support@zamzy.in" class="legal-highlight">support@zamzy.in</a><br />
        <strong>WhatsApp Support:</strong> <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20contacting%20from%20your%20official%20website%20(zamzy.in)." class="legal-highlight">+91 72870 60553</a>
      </p>

    </div>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-brand">
        <div class="footer-logo">
          <img src="images/logo.png" alt="ZAMZY" class="brand-logo-img" />
        </div>
        <p class="footer-tagline">
          ZAMZY.IN — Engineering High-Performance SaaS Platforms, Mobile Ecosystems &amp; Automated Cloud Systems.
        </p>
        <span class="footer-copy">© 2026 ZAMZY Digital Engineering Agency. All rights reserved.</span>
      </div>

      <div>
        <div class="footer-col-title">Navigation</div>
        <div class="footer-links-col">
          <a href="index.html#hero">Home</a>
          <a href="index.html#about">Studio</a>
          <a href="index.html#launchpad">Launchpad</a>
          <a href="index.html#products">Products</a>
          <a href="index.html#services">Services</a>
          <a href="careers#apply-guild">Careers &amp; Guild</a>
          <a href="contact.php">Contact Us</a>
        </div>
      </div>

      <div>
        <div class="footer-col-title">Merchant Policies</div>
        <div class="footer-links-col">
          <a href="privacy-policy.php">Privacy Policy</a>
          <a href="terms-and-conditions.php">Terms &amp; Conditions</a>
          <a href="refund-policy.php" style="color:var(--cyan);">Refund &amp; Cancellation</a>
          <a href="shipping-policy.php">Shipping &amp; Delivery</a>
          <a href="contact.php">Contact Support</a>
        </div>
      </div>

      <div>
        <div class="footer-col-title">Studio Office</div>
        <div class="footer-links-col" style="font-size:0.82rem; color:var(--dim); line-height:1.7;">
          <p><strong>ZAMZY DIGITAL SOLUTIONS</strong></p>
          <p>📍 Hitech City, Hyderabad, Telangana, India</p>
          <p>💬 <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20contacting%20from%20your%20official%20website%20(zamzy.in)." target="_blank" style="color:var(--cyan); text-decoration:underline;">+91 72870 60553 (WhatsApp)</a></p>
          <p>✉️ <a href="mailto:contact@zamzy.in" style="color:var(--cyan); text-decoration:underline;">contact@zamzy.in</a></p>
          <div class="social-circle-links">
            <a href="https://www.instagram.com/zamzy.in" target="_blank" rel="noopener" class="social-circle-btn instagram" aria-label="Instagram" title="Follow us on Instagram">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
            <a href="https://www.facebook.com/share/1BwUK4uNSr/" target="_blank" rel="noopener" class="social-circle-btn facebook" aria-label="Facebook" title="Follow us on Facebook">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M14 13.5h2.5l1-4H14v-2c0-1.03.48-1.5 1.5-1.5H17.5V2.31C16.88 2.22 15.65 2.1 14.35 2.1c-3.15 0-5.35 1.9-5.35 5.5v2.4H6v4h3V22h5v-8.5z"/></svg>
            </a>
            <a href="https://www.linkedin.com/company/zamzy/" target="_blank" rel="noopener" class="social-circle-btn linkedin" aria-label="LinkedIn" title="Connect with us on LinkedIn">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M6.94 5a2 2 0 1 1-4-.002 2 2 0 0 1 4 .002zM7 8.48H3V21h4V8.48zm6.32 0H9.34V21h3.94v-6.57c0-3.66 4.77-3.95 4.77 0V21H22v-7.93c0-6.17-7.06-5.94-8.68-2.91v-1.68z"/></svg>
            </a>
            <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20contacting%20from%20your%20official%20website%20(zamzy.in)." target="_blank" rel="noopener" class="social-circle-btn whatsapp" aria-label="WhatsApp" title="Chat on WhatsApp (+91 72870 60553)">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M17.472 14.382c-.301-.15-1.78-.878-2.056-.979-.276-.1-.477-.15-.678.15-.2.3-.777.979-.953 1.18-.176.2-.351.226-.652.075-.301-.15-1.272-.469-2.423-1.496-.895-.799-1.5-1.786-1.676-2.087-.175-.301-.019-.464.132-.614.136-.135.301-.351.452-.527.15-.176.2-.301.3-.502.101-.201.051-.377-.025-.527-.076-.15-.678-1.633-.929-2.235-.245-.587-.494-.508-.678-.517-.176-.01-.377-.01-.577-.01s-.527.075-.803.377c-.276.301-1.054 1.03-1.054 2.512 0 1.482 1.079 2.912 1.23 3.113.15.201 2.124 3.243 5.145 4.549.719.311 1.28.497 1.718.636.722.23 1.378.197 1.898.12.579-.087 1.78-.728 2.03-1.431.251-.703.251-1.306.176-1.431-.075-.126-.276-.201-.577-.352zm-5.427 7.618c-1.77 0-3.504-.46-5.033-1.332l-.361-.206-3.743.982.999-3.649-.227-.361a10.024 10.024 0 0 1-1.536-5.385C2.144 6.458 6.577 2.025 12.045 2.025c2.645 0 5.131 1.03 7.001 2.899a9.85 9.85 0 0 1 2.909 7.001c0 5.469-4.433 9.902-9.91 9.902z"/></svg>
            </a>
          </div>
        </div>
      </div>
    </div>

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

  <!-- Back to Top Button -->
  <button id="back-to-top" class="back-to-top" aria-label="Back to Top" title="Back to Top">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
      stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
  </button>

  <!-- AI CHATBOT FLOATING ICON -->
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
            <img src="images/ai-chat-icon.jpg" alt="AI"
              style="width:100%; height:100%; object-fit:cover; border-radius:50%;" />
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
          <div class="ai-msg-bubble">👋 Hi! I'm ZAMZY's assistant. How can I help you today? Ask me about our services,
            pricing, or start a project brief.</div>
        </div>
        <div class="ai-quick-btns">
          <button class="ai-quick-btn" data-msg="What services do you offer?">Our Services</button>
          <button class="ai-quick-btn" data-msg="What are your pricing plans?">Pricing</button>
          <button class="ai-quick-btn" data-msg="I want to start a project">Start a Project</button>
        </div>
      </div>
      <form class="ai-chat-input-row" id="ai-chat-form" autocomplete="off">
        <input type="text" id="ai-chat-input" class="ai-chat-input" placeholder="Type your message..."
          autocomplete="off" />
        <button type="submit" class="ai-send-btn" aria-label="Send">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
          </svg>
        </button>
      </form>
    </div>
  </div>

  <script src="tooplate-vora-bold-script.js"></script>
</body>
</html>
