<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Terms &amp; Conditions – ZAMZY WhatsApp Gateway</title>
  <meta name="description" content="Terms of Service and Acceptable Use Policy for ZAMZY WhatsApp Gateway &amp; 2FA API services.">
  <link rel="shortcut icon" href="../images/logo.png" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700;800&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg-main: #06060b;
      --bg-surface: #0a0c16;
      --bg-card: rgba(14, 17, 30, 0.85);
      --text-primary: #F3F4F6;
      --text-secondary: #9CA3AF;
      --text-muted: #6B7280;
      
      --cyan: #00ffcc;
      --cyan-glow: rgba(0, 255, 204, 0.15);
      --purple: #8B5CF6;
      --purple-glow: rgba(139, 92, 246, 0.15);
      
      --border-color: rgba(255, 255, 255, 0.08);
      
      --font-heading: 'Space Grotesk', sans-serif;
      --font-body: 'Inter', sans-serif;
      --font-mono: 'IBM Plex Mono', monospace;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: var(--font-body);
      background-color: var(--bg-main);
      color: var(--text-primary);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
      position: relative;
    }

    .glow-purple {
      position: absolute;
      top: -10%;
      right: -10%;
      width: 50vw;
      height: 50vw;
      background: radial-gradient(circle, var(--purple-glow) 0%, transparent 70%);
      pointer-events: none;
      z-index: 1;
    }

    header {
      width: 100%;
      max-width: 900px;
      margin: 0 auto;
      padding: 32px 20px 20px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      z-index: 10;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }

    .brand-name {
      font-family: var(--font-heading);
      font-size: 24px;
      font-weight: 800;
      color: #fff;
      letter-spacing: 1px;
    }

    .brand-name span {
      color: var(--cyan);
      text-shadow: 0 0 10px var(--cyan);
    }

    .nav-btn {
      text-decoration: none;
      font-family: var(--font-mono);
      font-size: 11px;
      font-weight: 700;
      color: var(--cyan);
      background: rgba(0, 255, 204, 0.1);
      border: 1px solid rgba(0, 255, 204, 0.3);
      padding: 8px 16px;
      border-radius: 8px;
      transition: all 0.3s ease;
      letter-spacing: 0.5px;
    }

    .nav-btn:hover {
      background: var(--cyan);
      color: #06060b;
      box-shadow: 0 0 15px rgba(0, 255, 204, 0.4);
    }

    main {
      flex: 1;
      width: 100%;
      max-width: 900px;
      margin: 0 auto;
      padding: 20px 20px 80px 20px;
      position: relative;
      z-index: 10;
    }

    .content-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.5);
      backdrop-filter: blur(16px);
    }

    h1 {
      font-family: var(--font-heading);
      font-size: clamp(26px, 4vw, 36px);
      font-weight: 800;
      color: #fff;
      margin-bottom: 8px;
      letter-spacing: -0.5px;
    }

    .last-updated {
      font-family: var(--font-mono);
      font-size: 12px;
      color: var(--cyan);
      margin-bottom: 30px;
      display: inline-block;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    h2 {
      font-family: var(--font-heading);
      font-size: 18px;
      font-weight: 700;
      color: #fff;
      margin-top: 30px;
      margin-bottom: 12px;
    }

    p {
      font-size: 14.5px;
      color: var(--text-secondary);
      line-height: 1.7;
      margin-bottom: 16px;
    }

    ul {
      margin-left: 20px;
      margin-bottom: 20px;
    }

    li {
      font-size: 14px;
      color: var(--text-secondary);
      line-height: 1.6;
      margin-bottom: 8px;
    }

    footer {
      width: 100%;
      border-top: 1px solid var(--border-color);
      padding: 24px 20px;
      text-align: center;
      font-family: var(--font-mono);
      font-size: 11.5px;
      color: var(--text-muted);
      position: relative;
      z-index: 10;
      background: rgba(6, 6, 11, 0.85);
      backdrop-filter: blur(10px);
    }

    @media (max-width: 768px) {
      .content-card {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>

  <div class="glow-purple"></div>

  <header>
    <a href="index.php" class="brand">
      <span class="brand-name">ZAMZY<span>.</span></span>
    </a>
    <a href="index.php" class="nav-btn">➔ Back to Gateway</a>
  </header>

  <main>
    <article class="content-card">
      <h1>Terms &amp; Conditions</h1>
      <div class="last-updated">Last Updated: September 2026</div>

      <p>Welcome to ZAMZY WhatsApp Gateway &amp; Automation Portal. By accessing or using our website, APIs, WhatsApp gateway, and automated notification services (collectively, the "Services"), you agree to be bound by these Terms &amp; Conditions. Please read them carefully.</p>

      <h2>1. Acceptable Use Policy</h2>
      <p>Our WhatsApp Gateway and API services are designed for legitimate transaction updates, verification codes (OTPs), automated customer support, and creator upload alerts. You agree NOT to use the service for:</p>
      <ul>
        <li>Sending unsolicited bulk commercial messages (Spam).</li>
        <li>Distributing phishing links, malware, or fraudulent content.</li>
        <li>Harassing, threatening, or impersonating any individual or business entity.</li>
        <li>Violating Meta/WhatsApp's official Terms of Service.</li>
      </ul>
      <p>Any violation of the acceptable use policy will result in immediate termination of your API slots and developer keys without refund.</p>

      <h2>2. Account Security &amp; Keys</h2>
      <p>You are solely responsible for maintaining the confidentiality of your account credentials, session states, and Bearer API keys. You agree to notify us immediately of any unauthorized use of your keys. We are not liable for any losses caused by third-party access to your active WhatsApp gateway connection slots.</p>

      <h2>3. API Usage Limits &amp; Fair Use</h2>
      <p>Your subscription tier controls the number of active WhatsApp scanner slots and message delivery volumes. Standard rate limits apply to prevent gateway buffer overload. Any script or integration that attempts to bypass rate limits or compromise server stability will be automatically throttled or temporarily suspended.</p>

      <h2>4. Service Availability &amp; Disclaimer</h2>
      <p>The WhatsApp gateway relies on active Baileys socket connections and third-party networks. While we strive to maintain 99.9% uptime, the service is provided on an "as-is" and "as-available" basis. We do not guarantee that message delivery will be instantaneous or error-free in the event of Meta platform updates or network maintenance.</p>

      <h2>5. Limitation of Liability</h2>
      <p>To the maximum extent permitted by law, ZAMZY Technologies and its developers shall not be liable for any direct, indirect, incidental, or consequential damages resulting from the use or inability to use the services, including loss of business profits, data corruption, or account banning by WhatsApp.</p>

      <h2>6. Modifications to Terms</h2>
      <p>We reserve the right to modify these terms at any time. Changes will be posted directly on this page. Your continued use of the services following term modifications constitutes acceptance of the updated terms.</p>
    </article>
  </main>

  <footer>
    <div>
      <span>&copy; 2026 ZAMZY Technologies. All rights reserved.</span>
    </div>
  </footer>

</body>
</html>
